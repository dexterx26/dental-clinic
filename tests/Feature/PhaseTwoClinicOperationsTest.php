<?php

namespace Tests\Feature;

use App\Models\ConsentForm;
use App\Models\DentalLabCase;
use App\Models\InventoryItem;
use App\Models\Patient;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseTwoClinicOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_staff_can_view_inventory_dashboard_and_filters(): void
    {
        $admin = User::where('email', 'admin@dentalclinic.com')->first();

        $response = $this->actingAs($admin)->get('/inventory');
        $response->assertStatus(200);
        $response->assertSee('Dental Supply Inventory');
        $response->assertSee('Filtek Z250');
        $response->assertSee('Low Stock Alert');

        // Test low stock filter
        $lowStockRes = $this->actingAs($admin)->get('/inventory?filter=low_stock');
        $lowStockRes->assertStatus(200);
        $lowStockRes->assertSee('Kromopan');
    }

    public function test_can_create_new_inventory_item_and_movement_is_logged(): void
    {
        $admin = User::where('email', 'admin@dentalclinic.com')->first();
        $supplier = Supplier::first();

        $response = $this->actingAs($admin)->post('/inventory', [
            'product_name' => 'Bonding Agent Single Bond Universal',
            'item_code' => 'BND-2026',
            'category' => 'Restorative',
            'brand' => '3M ESPE',
            'supplier_id' => $supplier->id,
            'unit' => 'bottle',
            'cost_price' => 1950.00,
            'selling_price' => 2800.00,
            'stock_level' => 15,
            'min_stock_level' => 5,
            'expiration_date' => date('Y-m-d', strtotime('+1 year')),
            'batch_number' => 'LOT-BOND-889',
        ]);

        $response->assertRedirect('/inventory');
        $this->assertDatabaseHas('inventory_items', [
            'item_code' => 'BND-2026',
            'product_name' => 'Bonding Agent Single Bond Universal',
            'stock_level' => 15,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'type' => 'received',
            'quantity' => 15,
            'reference_type' => 'initial_setup',
        ]);
    }

    public function test_can_adjust_inventory_stock_level(): void
    {
        $admin = User::where('email', 'admin@dentalclinic.com')->first();
        $item = InventoryItem::first();
        $originalStock = $item->stock_level;

        $response = $this->actingAs($admin)->post("/inventory/{$item->id}/adjust", [
            'type' => 'used',
            'quantity' => 2,
            'notes' => 'Consumed in quadrant restorative procedures',
        ]);

        $response->assertRedirect('/inventory');
        $item->refresh();
        $this->assertEquals($originalStock - 2, $item->stock_level);

        $this->assertDatabaseHas('stock_movements', [
            'inventory_item_id' => $item->id,
            'type' => 'used',
            'quantity' => -2,
            'new_stock' => $originalStock - 2,
        ]);
    }

    public function test_can_create_and_receive_purchase_order_replenishing_stock(): void
    {
        $admin = User::where('email', 'admin@dentalclinic.com')->first();
        $supplier = Supplier::first();
        $item = InventoryItem::first();
        $prevStock = $item->stock_level;

        // 1. Create Purchase Order
        $poRes = $this->actingAs($admin)->post('/purchase-orders', [
            'supplier_id' => $supplier->id,
            'order_date' => date('Y-m-d'),
            'expected_delivery_date' => date('Y-m-d', strtotime('+5 days')),
            'items' => [
                [
                    'inventory_item_id' => $item->id,
                    'quantity_ordered' => 10,
                    'unit_cost' => $item->cost_price,
                ]
            ],
            'notes' => 'Expedited clinical restocking',
        ]);

        $po = PurchaseOrder::latest('id')->first();
        $this->assertNotNull($po);
        $this->assertEquals('ordered', $po->status);
        $poRes->assertRedirect("/purchase-orders/{$po->id}");

        // 2. Mark Received & Auto-Replenish Stock
        $receiveRes = $this->actingAs($admin)->post("/purchase-orders/{$po->id}/receive");
        $receiveRes->assertRedirect();

        $po->refresh();
        $this->assertEquals('received', $po->status);

        $item->refresh();
        $this->assertEquals($prevStock + 10, $item->stock_level);

        $this->assertDatabaseHas('stock_movements', [
            'inventory_item_id' => $item->id,
            'type' => 'received',
            'quantity' => 10,
            'reference_type' => 'purchase_order',
            'reference_id' => $po->id,
        ]);
    }

    public function test_can_create_and_update_dental_lab_case(): void
    {
        $dentist = User::where('role', 'dentist')->first();
        $patient = Patient::first();

        // 1. Create Lab Case
        $createRes = $this->actingAs($dentist)->post('/lab-cases', [
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'lab_name' => 'Elite Dental Ceramics Manila',
            'technician_name' => 'Mark Bautista',
            'appliance_type' => 'Crown - Full Zirconia',
            'tooth_number' => '21',
            'shade' => 'Vita A1',
            'sent_date' => date('Y-m-d'),
            'expected_delivery_date' => date('Y-m-d', strtotime('+7 days')),
            'cost' => 4200.00,
            'instructions' => 'Porcelain fused layer on labial face, exact shade matching A1.',
        ]);

        $case = DentalLabCase::where('patient_id', $patient->id)->latest('id')->first();
        $this->assertNotNull($case);
        $this->assertEquals('sent', $case->status);
        $createRes->assertRedirect("/lab-cases/{$case->id}");

        // 2. Update Status to Delivered
        $statusRes = $this->actingAs($dentist)->patch("/lab-cases/{$case->id}/status", [
            'status' => 'delivered',
            'actual_delivery_date' => date('Y-m-d'),
            'notes' => 'Received at front desk in sealed container.',
        ]);
        $statusRes->assertRedirect();

        $case->refresh();
        $this->assertEquals('delivered', $case->status);
        $this->assertNotNull($case->actual_delivery_date);
    }

    public function test_can_execute_digital_consent_form_with_signature(): void
    {
        $dentist = User::where('role', 'dentist')->first();
        $patient = Patient::first();

        $sampleSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAAAyCAYAAACd5b5WAAAACXBIWXMAAAsTAAALEwEAmpwYAAAFmElEQVR4nO2dW2hcVRjH/9+ZzJ1MktymzbRNNWld2l6o9cEHRaGCF2xpL4qCiliv2AcRvPjgtSL0wQfxwQfxwfqqVURQFEERtIK1FkWlVlva1DTN5p7MnJnMeD4mZ/bM7Mlmspk52VbO9wNm9v7W2et/f7/vW/ZkXhIEQQgR9Ff9AEK4qRDDEEJDDEOIDDEMITTE';

        $consentRes = $this->actingAs($dentist)->post('/consent-forms', [
            'patient_id' => $patient->id,
            'dentist_id' => $dentist->id,
            'consent_type' => 'tooth_extraction',
            'title' => 'Informed Consent for Tooth Extraction & Oral Surgery',
            'description_and_risks' => 'I hereby authorize surgical removal of tooth 38. The risks including bleeding and numbness were explained.',
            'patient_signature' => $sampleSignature,
            'witness_name' => 'Dental Nurse Maria',
            'notes' => 'Fully discussed with patient.',
        ]);

        $consent = ConsentForm::where('patient_id', $patient->id)->latest('id')->first();
        $this->assertNotNull($consent);
        $this->assertEquals('signed', $consent->status);
        $this->assertNotNull($consent->signed_at);
        $consentRes->assertRedirect("/consent-forms/{$consent->id}");

        $this->assertDatabaseHas('consent_forms', [
            'patient_id' => $patient->id,
            'consent_type' => 'tooth_extraction',
            'status' => 'signed',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'consent_form_signed',
            'model_type' => ConsentForm::class,
            'model_id' => $consent->id,
        ]);
    }
}
