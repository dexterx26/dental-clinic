<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\DentalChart;
use App\Models\Examination;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\Treatment;
use App\Models\TreatmentPlan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseOneWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_staff_can_login_and_access_dashboard(): void
    {
        $admin = User::where('email', 'admin@dentalclinic.com')->first();
        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('BrightSmile');
        $response->assertSee('Clinical Schedule');
    }

    public function test_dentist_can_view_patient_chart_and_update_odontogram(): void
    {
        $dentist = User::where('email', 'dentist@dentalclinic.com')->first();
        $patient = Patient::first();

        $response = $this->actingAs($dentist)->get("/patients/{$patient->id}?tab=odontogram");
        $response->assertStatus(200);
        $response->assertSee('Interactive Odontogram');

        // Test AJAX Odontogram Update
        $chartResponse = $this->actingAs($dentist)->postJson("/patients/{$patient->id}/dental-chart", [
            'tooth_number' => 14,
            'surface' => 'occlusal',
            'condition' => 'caries',
            'notes' => 'Incipient occlusal pit caries',
        ]);

        $chartResponse->assertStatus(200);
        $chartResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('dental_charts', [
            'patient_id' => $patient->id,
            'tooth_number' => 14,
            'condition' => 'caries',
        ]);

        $this->assertDatabaseHas('dental_chart_histories', [
            'patient_id' => $patient->id,
            'tooth_number' => 14,
            'new_condition' => 'caries',
        ]);
    }

    public function test_dentist_can_view_odontogram_v2_and_comparison_page(): void
    {
        $dentist = User::where('email', 'dentist@dentalclinic.com')->first();
        $patient = Patient::first();

        // 1. View Odontogram v2 tab
        $responseV2 = $this->actingAs($dentist)->get("/patients/{$patient->id}?tab=odontogram_v2");
        $responseV2->assertStatus(200);
        $responseV2->assertSee('Odontogram v2');
        $responseV2->assertSee('Realistic Anatomical Visual Dental Chart');
        $responseV2->assertSee('Panoramic Dental Arch Anatomy Reference');

        // 2. View Odontogram comparison dedicated page
        $responseCompare = $this->actingAs($dentist)->get("/patients/{$patient->id}/odontogram-compare");
        $responseCompare->assertStatus(200);
        $responseCompare->assertSee('Odontogram v1 vs Odontogram v2 Comparison');
        $responseCompare->assertSee('Geometric 5-Surface Chart');
        $responseCompare->assertSee('Realistic Anatomical Visual Chart');
    }

    public function test_can_create_examination_treatment_and_prescription(): void
    {
        $dentist = User::where('email', 'dentist@dentalclinic.com')->first();
        $patient = Patient::first();

        // 1. Examination
        $examRes = $this->actingAs($dentist)->post("/patients/{$patient->id}/examinations", [
            'dentist_id' => $dentist->id,
            'exam_date' => date('Y-m-d'),
            'chief_complaint' => 'Routine follow-up exam',
            'diagnosis' => 'Localized marginal gingivitis',
            'clinical_findings' => 'Superficial plaque deposits on lower anterior lingual surfaces',
        ]);
        $examRes->assertRedirect();
        $this->assertDatabaseHas('examinations', ['patient_id' => $patient->id, 'diagnosis' => 'Localized marginal gingivitis']);

        // 2. Treatment Plan
        $planRes = $this->actingAs($dentist)->post("/patients/{$patient->id}/treatment-plans", [
            'dentist_id' => $dentist->id,
            'title' => 'Preventive Cleaning & Fluoride Treatment',
            'items' => [
                [
                    'procedure_name' => 'Oral Prophylaxis',
                    'tooth_number' => 'Full Mouth',
                    'surface' => 'All',
                    'estimated_cost' => 1200.00,
                    'priority' => 'medium',
                    'sessions_required' => 1,
                ]
            ]
        ]);
        $planRes->assertRedirect();
        $this->assertDatabaseHas('treatment_plans', ['patient_id' => $patient->id, 'title' => 'Preventive Cleaning & Fluoride Treatment']);

        // 3. Treatment Execution
        $trtRes = $this->actingAs($dentist)->post("/patients/{$patient->id}/treatments", [
            'dentist_id' => $dentist->id,
            'procedure_name' => 'Fluoride Varnish Application',
            'procedure_notes' => 'Applied 5% sodium fluoride varnish on all quadrant surfaces.',
            'cost' => 800.00,
        ]);
        $trtRes->assertRedirect();
        $this->assertDatabaseHas('treatments', ['patient_id' => $patient->id, 'procedure_name' => 'Fluoride Varnish Application']);

        // 4. Prescription
        $rxRes = $this->actingAs($dentist)->post("/patients/{$patient->id}/prescriptions", [
            'dentist_id' => $dentist->id,
            'prescription_date' => date('Y-m-d'),
            'items' => [
                [
                    'medication_name' => 'Fluoride Rinse 0.05%',
                    'dosage' => '10 mL',
                    'frequency' => 'Once daily before bed',
                    'duration' => '30 days',
                    'quantity' => 1,
                ]
            ]
        ]);
        $rxRes->assertRedirect();
        $this->assertDatabaseHas('prescriptions', ['patient_id' => $patient->id]);
    }

    public function test_billing_and_payment_flow(): void
    {
        $cashier = User::where('email', 'cashier@dentalclinic.com')->first();
        $patient = Patient::first();

        // 1. Create Invoice
        $invRes = $this->actingAs($cashier)->post("/invoices/{$patient->id}", [
            'invoice_date' => date('Y-m-d'),
            'items' => [
                [
                    'item_name' => 'Dental Prophylaxis Service',
                    'quantity' => 1,
                    'unit_price' => 1500.00,
                ]
            ]
        ]);
        $invRes->assertRedirect();
        $invoice = Invoice::where('patient_id', $patient->id)->latest('id')->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(1500.00, $invoice->total_amount);

        // 2. Pay Invoice
        $payRes = $this->actingAs($cashier)->post("/invoices/{$invoice->id}/payments", [
            'amount' => 1500.00,
            'payment_method' => 'cash',
            'notes' => 'Paid in full cash',
        ]);
        $payRes->assertRedirect();

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(0, $invoice->balance_amount);
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->id, 'amount' => 1500.00]);
    }
}
