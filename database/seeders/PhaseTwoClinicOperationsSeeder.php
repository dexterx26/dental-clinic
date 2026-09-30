<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\ConsentForm;
use App\Models\DentalLabCase;
use App\Models\InventoryItem;
use App\Models\Patient;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PhaseTwoClinicOperationsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'administrator')->first();
        $dentist = User::where('role', 'dentist')->first();
        $nurse = User::where('role', 'dental_assistant')->first();

        // 1. Suppliers
        $sup1 = Supplier::create([
            'name' => 'Metro Dental Supply Manila',
            'contact_person' => 'Maria Santos (Senior Account Rep)',
            'phone' => '+63 917 555 1234',
            'email' => 'orders@metrodental.ph',
            'address' => 'Unit 108 Shaw Blvd, Mandaluyong City, Metro Manila',
            'notes' => 'Net 30 payment terms. Free clinic delivery for orders over ₱5,000.',
            'is_active' => true,
        ]);

        $sup2 = Supplier::create([
            'name' => 'Philippine Dental Depot (PDD)',
            'contact_person' => 'Kevin Tan (Regional Sales Director)',
            'phone' => '+63 918 888 7766',
            'email' => 'sales@pdd.com.ph',
            'address' => '842 C.M. Recto Ave, Sampaloc, Manila',
            'notes' => 'Specializes in rotary endo files, impression materials, and surgical sundries.',
            'is_active' => true,
        ]);

        $sup3 = Supplier::create([
            'name' => 'Dentsply Sirona Philippines',
            'contact_person' => 'Carlo Mercado',
            'phone' => '+63 2 8999 4321',
            'email' => 'ph.orders@dentsplysirona.com',
            'address' => 'Ayala Triangle Gardens, Makati City',
            'notes' => 'Direct distributor of dental anesthetics, ultrasonic tips, and composites.',
            'is_active' => true,
        ]);

        $sup4 = Supplier::create([
            'name' => 'GC Asia Dental Depot',
            'contact_person' => 'Dr. Elaine Lim',
            'phone' => '+63 2 8234 5678',
            'email' => 'contact@gcasia.ph',
            'address' => 'High Street South Corporate Plaza, BGC, Taguig City',
            'notes' => 'Glass ionomer cements, bonding agents, and preventative fluoride varnishes.',
            'is_active' => true,
        ]);

        // 2. Inventory Items
        $items = [
            [
                'product_name' => 'Filtek Z250 Universal Restorative Composite',
                'item_code' => 'RES-0001',
                'category' => 'Restorative',
                'brand' => '3M ESPE',
                'supplier_id' => $sup1->id,
                'unit' => 'syringe',
                'cost_price' => 1450.00,
                'selling_price' => 2500.00,
                'stock_level' => 14,
                'min_stock_level' => 5,
                'expiration_date' => Carbon::now()->addMonths(18),
                'batch_number' => 'LOT-2026-081',
            ],
            [
                'product_name' => 'Xylocaine 2% with Epinephrine 1:100k',
                'item_code' => 'ANE-0002',
                'category' => 'Anesthetic',
                'brand' => 'Dentsply Sirona',
                'supplier_id' => $sup3->id,
                'unit' => 'box',
                'cost_price' => 1850.00,
                'selling_price' => 0.00,
                'stock_level' => 8,
                'min_stock_level' => 4,
                'expiration_date' => Carbon::now()->addMonths(14),
                'batch_number' => 'LOT-2026-302',
            ],
            [
                'product_name' => 'Dental Disposable Needles 30G Short',
                'item_code' => 'SUR-0003',
                'category' => 'Surgical',
                'brand' => 'Terumo Dental',
                'supplier_id' => $sup1->id,
                'unit' => 'box',
                'cost_price' => 450.00,
                'selling_price' => 0.00,
                'stock_level' => 12,
                'min_stock_level' => 4,
                'expiration_date' => Carbon::now()->addMonths(24),
                'batch_number' => 'LOT-2026-119',
            ],
            [
                'product_name' => 'Kromopan Color-Changing Alginate',
                'item_code' => 'IMP-0004',
                'category' => 'Impression',
                'brand' => 'Lascod',
                'supplier_id' => $sup2->id,
                'unit' => 'pack',
                'cost_price' => 380.00,
                'selling_price' => 0.00,
                'stock_level' => 2, // LOW STOCK
                'min_stock_level' => 6,
                'expiration_date' => Carbon::now()->addDays(28), // EXPIRING SOON
                'batch_number' => 'LOT-2026-441',
            ],
            [
                'product_name' => 'Fuji IX GP Fast Glass Ionomer Restorative',
                'item_code' => 'RES-0005',
                'category' => 'Restorative',
                'brand' => 'GC Dental',
                'supplier_id' => $sup4->id,
                'unit' => 'box',
                'cost_price' => 2200.00,
                'selling_price' => 3000.00,
                'stock_level' => 7,
                'min_stock_level' => 3,
                'expiration_date' => Carbon::now()->addMonths(20),
                'batch_number' => 'LOT-2026-905',
            ],
            [
                'product_name' => 'Nitrile Examination Gloves Powder-Free Medium',
                'item_code' => 'PPE-0006',
                'category' => 'PPE',
                'brand' => 'SafeTouch Medical',
                'supplier_id' => $sup1->id,
                'unit' => 'box',
                'cost_price' => 320.00,
                'selling_price' => 0.00,
                'stock_level' => 20,
                'min_stock_level' => 6,
                'expiration_date' => Carbon::now()->addMonths(36),
                'batch_number' => 'LOT-2026-778',
            ],
            [
                'product_name' => 'M-Access Endodontic K-Files Assorted #15-40',
                'item_code' => 'END-0007',
                'category' => 'Endodontic',
                'brand' => 'Dentsply Maillefer',
                'supplier_id' => $sup3->id,
                'unit' => 'pack',
                'cost_price' => 650.00,
                'selling_price' => 0.00,
                'stock_level' => 1, // LOW STOCK
                'min_stock_level' => 4,
                'expiration_date' => Carbon::now()->addYears(3),
                'batch_number' => 'LOT-2026-551',
            ],
        ];

        foreach ($items as $data) {
            $created = InventoryItem::create($data);
            StockMovement::create([
                'inventory_item_id' => $created->id,
                'type' => 'received',
                'quantity' => $created->stock_level,
                'previous_stock' => 0,
                'new_stock' => $created->stock_level,
                'reference_type' => 'initial_setup',
                'notes' => 'Initial clinic inventory intake',
                'user_id' => $admin ? $admin->id : null,
            ]);
        }

        // 3. Purchase Order Example
        $poItem1 = InventoryItem::where('item_code', 'RES-0001')->first();
        $poItem2 = InventoryItem::where('item_code', 'PPE-0006')->first();

        $po = PurchaseOrder::create([
            'po_number' => 'PO-20260930-7701',
            'supplier_id' => $sup1->id,
            'created_by_id' => $admin ? $admin->id : null,
            'order_date' => Carbon::today()->subDays(3),
            'expected_delivery_date' => Carbon::today()->addDays(4),
            'status' => 'ordered',
            'total_amount' => (5 * 1450.00) + (10 * 320.00),
            'notes' => 'Priority restock for Restorative and PPE supplies.',
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'inventory_item_id' => $poItem1->id,
            'quantity_ordered' => 5,
            'quantity_received' => 0,
            'unit_cost' => 1450.00,
            'total_cost' => 7250.00,
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'inventory_item_id' => $poItem2->id,
            'quantity_ordered' => 10,
            'quantity_received' => 0,
            'unit_cost' => 320.00,
            'total_cost' => 3200.00,
        ]);

        // 4. Dental Lab Cases
        $p1 = Patient::first();
        $p2 = Patient::skip(1)->first() ?? $p1;

        if ($p1 && $dentist) {
            DentalLabCase::create([
                'case_number' => 'LAB-20260925-1044',
                'patient_id' => $p1->id,
                'dentist_id' => $dentist->id,
                'lab_name' => 'Apex Dental Studio Manila',
                'technician_name' => 'Robert Ramos (Master Ceramist)',
                'appliance_type' => 'Crown - Full Zirconia',
                'tooth_number' => '14',
                'shade' => 'Vita A2',
                'sent_date' => Carbon::today()->subDays(5),
                'expected_delivery_date' => Carbon::today()->addDays(2),
                'cost' => 3800.00,
                'status' => 'in_progress',
                'instructions' => 'High translucency monolithic zirconia crown. Feather-edge buccal margin with light occlusal contact on opposing premolar.',
                'notes' => 'Patient informed of 1-week turnaround. Tentative fitting scheduled.',
                'created_by_id' => $dentist->id,
            ]);
        }

        if ($p2 && $dentist) {
            DentalLabCase::create([
                'case_number' => 'LAB-20260918-2091',
                'patient_id' => $p2->id,
                'dentist_id' => $dentist->id,
                'lab_name' => 'CrownCraft Prosthetics Lab',
                'technician_name' => 'Jennylyn Cruz',
                'appliance_type' => 'Denture - Removable Partial Denture (RPD)',
                'tooth_number' => 'Mandibular Arch',
                'shade' => 'Vita A3',
                'sent_date' => Carbon::today()->subDays(12),
                'expected_delivery_date' => Carbon::today()->subDays(2),
                'actual_delivery_date' => Carbon::today()->subDays(2),
                'cost' => 6500.00,
                'status' => 'delivered',
                'instructions' => 'Cast metal framework with Vitallium alloy and Lucitone 199 acrylic pink gum base.',
                'notes' => 'Delivered to clinic in sterile pouch. Ready for patient try-in/insertion appointment.',
                'created_by_id' => $dentist->id,
            ]);
        }

        // 5. Digital Consent Forms with Base64 Signatures
        // Standard sample base64 transparent PNG signature
        $sampleSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAAAyCAYAAACd5b5WAAAACXBIWXMAAAsTAAALEwEAmpwYAAAFmElEQVR4nO2dW2hcVRjH/9+ZzJ1MktymzbRNNWld2l6o9cEHRaGCF2xpL4qCiliv2AcRvPjgtSL0wQfxwQfxwfqqVURQFEERtIK1FkWlVlva1DTN5p7MnJnMeD4mZ/bM7Mlmspk52VbO9wNm9v7W2et/f7/vW/ZkXhIEQQgR9Ff9AEK4qRDDEEJDDEOIDDEMITTE'
            . 'MIThkF0j89u3353u3Xdf4vYbr325f+9tS0b/mZmdnZs5ffqJ31+4/4bO5196M3/eQxCCiB4bS966qOvi9i92r79434e/Tq9d80Wz2fx1Zm5uZnZu9qWDB5987a03X37x2/eeXjU9Pf133o8ghBiVjSX93950/Zpvnrv7w7lV67/N5/N/z/1zYur/v/3y9Zeff37rP/8u27Fjx42d/Z848e4'
            . '4f4gQwhJjSfdv2HztYxuu390z88Pq6dmZaT41OTU1s/L2K0+fOXH8yH8P3rW8a8P7T59s7j9y4vjh7o9++p8/RghhQY+Npe5fcd3eFdfvuZpPnjr+1Ouv37Hsyu0fnzhy+MS7X/b/61f9/B/DEDpDDEMIDTEMITTE'
            . 'MIThkF0j89u3353u3Xdf4vYbr325f+9tS0b/mZmdnZs5ffqJ31+4/4bO5196M3/eQxCCiB4bS966qOvi9i92r79434e/Tq9d80Wz2fx1Zm5uZnZu9qWDB5987a03X37x2/eeXjU9Pf133o8ghBiVjSX93950/Zpvnrv7'
            . 'w7lV67/N5/N/z/1zYur/v/3y9Zeff37rP/8u27Fjx42d/Z848e44f4gQwhJjSfdv2HztYxuu390z88Pq6dmZaT41OTU1s/L2K0+fOXH8yH8P3rW8a8P7T59s7j9y4vjh7o9++p8/RghhQY+Npe5fcd3eFdfvuZpP'
            . 'njr+1Ouv37Hsyu0fnzhy+MS7X/b/61f9/B/DEDpDDEMIDTEMITTE'
            . 'MIThEMMQw0MMQwwPMQwhNMQwhNAQwxBCQwxDCA0xDCE0xDCE0BDDEMJDDEOIDDEMITTE'
            . 'MIThkF0j89u3353u3Xdf4vYbr325f+9tS0b/mZmdnZs5ffqJ31+4/4bO5196M3/eQxCCiB4bS966qOvi9i92r79434e/Tq9d80Wz2fx1Zm5uZnZu9qWDB5987a03X37x2/eeXjU9Pf133o8ghBiVjSX93950/Zpvnrv7'
            . 'w7lV67/N5/N/z/1zYur/v/3y9Zeff37rP/8u27Fjx42d/Z848e44f4gQwhJjSfdv2HztYxuu390z88Pq6dmZaT41OTU1s/L2K0+fOXH8yH8P3rW8a8P7T59s7j9y4vjh7o9++p8/RghhQY+Npe5fcd3eFdfvuZpP'
            . 'njr+1Ouv37Hsyu0fnzhy+MS7X/b/61f9/B/DEDpDDEMIDTEMITTE'
            . 'MIThEMMQw0MMQwwPMQwhNMQwhNAQwxBCQwxDCA0xDCE0xDCE0BDDEMJDDEOIDDEMITTE';

        if ($p1 && $dentist) {
            ConsentForm::create([
                'consent_number' => 'CNS-20260925-8812',
                'patient_id' => $p1->id,
                'dentist_id' => $dentist->id,
                'consent_type' => 'tooth_extraction',
                'title' => 'Informed Consent for Tooth Extraction & Oral Surgery',
                'description_and_risks' => "I hereby authorize Dr. {$dentist->name} to perform surgical extraction of tooth 48 (impacted lower right third molar). The risks including postoperative pain, swelling, dry socket, bleeding, and nerve numbness have been explained to me. I have had an opportunity to ask questions and discuss all concerns.",
                'patient_signature' => $sampleSignature,
                'signed_at' => Carbon::now()->subDays(5),
                'witness_name' => $nurse ? $nurse->name : 'Clinical Nurse',
                'status' => 'signed',
                'notes' => 'Patient signed willingly after verbal discussion in Filipino.',
                'created_by_id' => $dentist->id,
            ]);
        }

        if ($p2 && $dentist) {
            ConsentForm::create([
                'consent_number' => 'CNS-20260928-9903',
                'patient_id' => $p2->id,
                'dentist_id' => $dentist->id,
                'consent_type' => 'root_canal',
                'title' => 'Informed Consent for Endodontic Therapy (Root Canal Treatment)',
                'description_and_risks' => "I understand that root canal therapy is performed to retain tooth 14 which might otherwise require extraction. Although endodontic therapy has a very high degree of clinical success, it is a biological procedure and results cannot be guaranteed. Potential risks including instrument separation within root canals, postoperative flare-ups, and the necessity of a permanent dental crown have been explained.",
                'patient_signature' => $sampleSignature,
                'signed_at' => Carbon::now()->subDays(2),
                'witness_name' => $nurse ? $nurse->name : 'Clinical Nurse',
                'status' => 'signed',
                'notes' => 'Patient informed of follow-up crown placement requirement.',
                'created_by_id' => $dentist->id,
            ]);
        }
    }
}
