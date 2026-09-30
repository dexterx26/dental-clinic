<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\DentalChart;
use App\Models\DentalChartHistory;
use App\Models\DentistSchedule;
use App\Models\Examination;
use App\Models\FollowUp;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\PatientDentalHistory;
use App\Models\PatientMedicalHistory;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Service;
use App\Models\SystemSetting;
use App\Models\Treatment;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. System Settings
        $settings = [
            ['key' => 'clinic_name', 'value' => 'BrightSmile Dental & Oral Health Center', 'group' => 'clinic'],
            ['key' => 'clinic_tagline', 'value' => 'State-of-the-Art Dental Care & Aesthetic Dentistry', 'group' => 'clinic'],
            ['key' => 'clinic_address', 'value' => 'Unit 402 Medical Arts Tower, Bonifacio Global City, Taguig, Philippines', 'group' => 'clinic'],
            ['key' => 'clinic_phone', 'value' => '+63 917 123 4567 / (02) 8888-9999', 'group' => 'clinic'],
            ['key' => 'clinic_email', 'value' => 'contact@brightsmiledental.ph', 'group' => 'clinic'],
            ['key' => 'currency_symbol', 'value' => '₱', 'group' => 'billing'],
            ['key' => 'invoice_prefix', 'value' => 'INV-2026-', 'group' => 'billing'],
            ['key' => 'receipt_prefix', 'value' => 'OR-2026-', 'group' => 'billing'],
            ['key' => 'opening_time', 'value' => '08:00', 'group' => 'scheduling'],
            ['key' => 'closing_time', 'value' => '18:00', 'group' => 'scheduling'],
            ['key' => 'dpa_compliance_notice', 'value' => 'Patient records are protected under Republic Act No. 10173 (Philippine Data Privacy Act of 2012).', 'group' => 'system'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        // 2. Core Users (5 User Roles)
        $users = [
            [
                'name' => 'Dr. Allan Santos',
                'email' => 'admin@dentalclinic.com',
                'password' => Hash::make('password123'),
                'role' => 'administrator',
                'phone' => '+63 917 111 2233',
                'license_number' => 'PRC-0078901',
                'specialization' => 'Clinic Director / Oral Implantology',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Maria Angela Reyes',
                'email' => 'dentist@dentalclinic.com',
                'password' => Hash::make('password123'),
                'role' => 'dentist',
                'phone' => '+63 918 222 3344',
                'license_number' => 'PRC-0081234',
                'specialization' => 'Orthodontics & Aesthetic Dentistry',
                'is_active' => true,
            ],
            [
                'name' => 'Dr. Juan Carlos Dizon',
                'email' => 'dentist2@dentalclinic.com',
                'password' => Hash::make('password123'),
                'role' => 'dentist',
                'phone' => '+63 919 333 4455',
                'license_number' => 'PRC-0094561',
                'specialization' => 'Endodontics & Oral Surgery',
                'is_active' => true,
            ],
            [
                'name' => 'Ana Patricia Gomez',
                'email' => 'receptionist@dentalclinic.com',
                'password' => Hash::make('password123'),
                'role' => 'receptionist',
                'phone' => '+63 920 444 5566',
                'license_number' => null,
                'specialization' => 'Patient Care Coordinator',
                'is_active' => true,
            ],
            [
                'name' => 'Elena Morales',
                'email' => 'assistant@dentalclinic.com',
                'password' => Hash::make('password123'),
                'role' => 'dental_assistant',
                'phone' => '+63 921 555 6677',
                'license_number' => null,
                'specialization' => 'Senior Dental Chairside Assistant',
                'is_active' => true,
            ],
            [
                'name' => 'Carlo Ramos',
                'email' => 'cashier@dentalclinic.com',
                'password' => Hash::make('password123'),
                'role' => 'cashier',
                'phone' => '+63 922 666 7788',
                'license_number' => null,
                'specialization' => 'Finance & Billing Officer',
                'is_active' => true,
            ],
        ];

        $createdUsers = [];
        foreach ($users as $userData) {
            $createdUsers[$userData['email']] = User::updateOrCreate(['email' => $userData['email']], $userData);
        }

        $dentist1 = $createdUsers['dentist@dentalclinic.com'];
        $dentist2 = $createdUsers['dentist2@dentalclinic.com'];
        $admin = $createdUsers['admin@dentalclinic.com'];
        $cashier = $createdUsers['cashier@dentalclinic.com'];

        // 3. Dentist Weekly Schedules (Monday to Saturday)
        foreach ([$dentist1->id, $dentist2->id] as $dId) {
            for ($day = 1; $day <= 6; $day++) {
                DentistSchedule::updateOrCreate([
                    'dentist_id' => $dId,
                    'day_of_week' => $day,
                ], [
                    'start_time' => '08:30:00',
                    'end_time' => '17:30:00',
                    'break_start' => '12:00:00',
                    'break_end' => '13:00:00',
                    'is_available' => true,
                    'notes' => 'Regular clinical shift',
                ]);
            }
        }

        // 4. Dental Services Catalog
        $services = [
            ['service_code' => 'SRV-001', 'name' => 'Comprehensive Dental Consultation & Checkup', 'category' => 'Diagnostic', 'standard_price' => 600.00, 'duration_minutes' => 30],
            ['service_code' => 'SRV-002', 'name' => 'Oral Prophylaxis (Cleaning & Polishing)', 'category' => 'Preventive', 'standard_price' => 1500.00, 'duration_minutes' => 45],
            ['service_code' => 'SRV-003', 'name' => 'Composite Light-Cure Tooth Filling', 'category' => 'Restorative', 'standard_price' => 1800.00, 'duration_minutes' => 45],
            ['service_code' => 'SRV-004', 'name' => 'Simple Tooth Extraction', 'category' => 'Oral Surgery', 'standard_price' => 1200.00, 'duration_minutes' => 40],
            ['service_code' => 'SRV-005', 'name' => 'Surgical Extraction / Odontectomy (Wisdom Tooth)', 'category' => 'Oral Surgery', 'standard_price' => 8500.00, 'duration_minutes' => 90],
            ['service_code' => 'SRV-006', 'name' => 'Root Canal Treatment (Molar)', 'category' => 'Endodontics', 'standard_price' => 9500.00, 'duration_minutes' => 60],
            ['service_code' => 'SRV-007', 'name' => 'Porcelain Fused to Metal (PFM) Crown', 'category' => 'Prosthodontics', 'standard_price' => 8500.00, 'duration_minutes' => 60],
            ['service_code' => 'SRV-008', 'name' => 'Full Zirconia Aesthetic Crown', 'category' => 'Prosthodontics', 'standard_price' => 15000.00, 'duration_minutes' => 60],
            ['service_code' => 'SRV-009', 'name' => 'Digital Periapical Radiograph (X-Ray)', 'category' => 'Radiology', 'standard_price' => 450.00, 'duration_minutes' => 15],
            ['service_code' => 'SRV-010', 'name' => 'In-Office Laser Teeth Whitening', 'category' => 'Cosmetic', 'standard_price' => 12000.00, 'duration_minutes' => 75],
            ['service_code' => 'SRV-011', 'name' => 'Orthodontic Braces Installation', 'category' => 'Orthodontics', 'standard_price' => 45000.00, 'duration_minutes' => 90],
            ['service_code' => 'SRV-012', 'name' => 'Periodontal Deep Scaling & Root Planing', 'category' => 'Periodontics', 'standard_price' => 3500.00, 'duration_minutes' => 60],
        ];

        $serviceModels = [];
        foreach ($services as $srv) {
            $serviceModels[$srv['service_code']] = Service::updateOrCreate(['service_code' => $srv['service_code']], $srv);
        }

        // 5. Realistic Patients (Philippine DPA compliant)
        $patientsData = [
            [
                'patient_number' => 'PAT-2026-0001',
                'first_name' => 'Jose',
                'middle_name' => 'Protacio',
                'last_name' => 'Dela Cruz',
                'dob' => '1992-06-19',
                'age' => 34,
                'gender' => 'Male',
                'civil_status' => 'Married',
                'nationality' => 'Filipino',
                'occupation' => 'Software Engineer',
                'phone' => '+63 917 555 1234',
                'email' => 'jose.delacruz@email.ph',
                'address' => 'Tower 2, Unit 18B, San Lorenzo Place, Makati City',
                'emergency_contact_name' => 'Maria Dela Cruz',
                'emergency_contact_phone' => '+63 917 555 9999',
                'emergency_contact_relationship' => 'Spouse',
                'preferred_dentist_id' => $dentist1->id,
                'referral_source' => 'Walk-in / Word of mouth',
                'status' => 'active',
                'privacy_consent_accepted' => true,
                'privacy_consent_date' => Carbon::now()->subMonths(2),
                'registration_date' => Carbon::now()->subMonths(2)->toDateString(),
                'notes' => 'Patient has mild dental anxiety; appreciates clear procedure step explanations.',
            ],
            [
                'patient_number' => 'PAT-2026-0002',
                'first_name' => 'Beatrice',
                'middle_name' => 'Carmela',
                'last_name' => 'Luna',
                'dob' => '1998-03-24',
                'age' => 28,
                'gender' => 'Female',
                'civil_status' => 'Single',
                'nationality' => 'Filipino',
                'occupation' => 'Marketing Specialist',
                'phone' => '+63 920 777 5678',
                'email' => 'beatrice.luna@email.ph',
                'address' => 'One Serendra, Bonifacio Global City, Taguig',
                'emergency_contact_name' => 'Eduardo Luna',
                'emergency_contact_phone' => '+63 920 777 0000',
                'emergency_contact_relationship' => 'Father',
                'preferred_dentist_id' => $dentist1->id,
                'referral_source' => 'Instagram / Social Media',
                'status' => 'active',
                'privacy_consent_accepted' => true,
                'privacy_consent_date' => Carbon::now()->subMonths(1),
                'registration_date' => Carbon::now()->subMonths(1)->toDateString(),
                'notes' => 'Interested in aesthetic veneers and teeth whitening.',
            ],
            [
                'patient_number' => 'PAT-2026-0003',
                'first_name' => 'Gabriel',
                'middle_name' => 'Santos',
                'last_name' => 'Santiago',
                'dob' => '1981-11-12',
                'age' => 44,
                'gender' => 'Male',
                'civil_status' => 'Married',
                'nationality' => 'Filipino',
                'occupation' => 'Architect',
                'phone' => '+63 918 888 3456',
                'email' => 'gabriel.santiago@archph.com',
                'address' => 'Valle Verde 5, Pasig City',
                'emergency_contact_name' => 'Grace Santiago',
                'emergency_contact_phone' => '+63 918 888 1111',
                'emergency_contact_relationship' => 'Spouse',
                'preferred_dentist_id' => $dentist2->id,
                'referral_source' => 'Colleague Referral',
                'status' => 'active',
                'privacy_consent_accepted' => true,
                'privacy_consent_date' => Carbon::now()->subDays(14),
                'registration_date' => Carbon::now()->subDays(14)->toDateString(),
                'notes' => 'Requires blood pressure check prior to invasive procedures.',
            ],
        ];

        $patientModels = [];
        foreach ($patientsData as $pData) {
            $patient = Patient::updateOrCreate(['patient_number' => $pData['patient_number']], $pData);
            $patientModels[$patient->patient_number] = $patient;

            // Medical History
            if ($patient->patient_number === 'PAT-2026-0001') {
                PatientMedicalHistory::updateOrCreate(
                    ['patient_id' => $patient->id],
                    [
                        'conditions' => ['Hypertension' => 'Stage 1, well-controlled', 'Bleeding Disorder' => 'None'],
                        'allergies' => 'Penicillin (developed hives in childhood)',
                        'current_medications' => 'Amlodipine 5mg once daily',
                        'past_surgeries' => 'Appendectomy (2015)',
                        'family_history' => 'Father had heart disease, Mother had diabetes',
                        'lifestyle_notes' => 'Non-smoker, occasional coffee and tea drinker',
                        'recorded_by_id' => $dentist1->id,
                    ]
                );

                PatientDentalHistory::updateOrCreate(
                    ['patient_id' => $patient->id],
                    [
                        'previous_dentist' => 'Dr. Hernandez (Makati Dental Care)',
                        'last_dental_visit' => Carbon::now()->subMonths(8)->toDateString(),
                        'past_treatments' => 'Root Canal on tooth 46 in 2022; Routine cleanings; Composite fillings on upper molars.',
                        'notes' => 'Experiences mild sensitivity to cold liquids on lower right quadrant.',
                    ]
                );
            } elseif ($patient->patient_number === 'PAT-2026-0002') {
                PatientMedicalHistory::updateOrCreate(
                    ['patient_id' => $patient->id],
                    [
                        'conditions' => ['Asthma' => 'Mild intermittent, triggered by cold air'],
                        'allergies' => 'No known drug allergies (NKDA)',
                        'current_medications' => 'Salbutamol inhaler as needed',
                        'past_surgeries' => 'None',
                        'family_history' => 'No significant hereditary conditions',
                        'lifestyle_notes' => 'Exercises regularly, drinks coffee daily',
                        'recorded_by_id' => $dentist1->id,
                    ]
                );

                PatientDentalHistory::updateOrCreate(
                    ['patient_id' => $patient->id],
                    [
                        'previous_dentist' => 'BGC Smiles Clinic',
                        'last_dental_visit' => Carbon::now()->subMonths(6)->toDateString(),
                        'past_treatments' => 'Orthodontic braces completed in 2021; routine prophylaxis.',
                        'notes' => 'Upper anterior diastema corrected.',
                    ]
                );
            } else {
                PatientMedicalHistory::updateOrCreate(
                    ['patient_id' => $patient->id],
                    [
                        'conditions' => ['Diabetes' => 'Type 2, HbA1c 6.8%', 'Hypertension' => 'Controlled'],
                        'allergies' => 'Aspirin sensitive',
                        'current_medications' => 'Metformin 500mg BID, Losartan 50mg OD',
                        'past_surgeries' => 'None',
                        'family_history' => 'Diabetes running in maternal line',
                        'lifestyle_notes' => 'Smoker (5 sticks/day), regular dental flossing',
                        'recorded_by_id' => $dentist2->id,
                    ]
                );

                PatientDentalHistory::updateOrCreate(
                    ['patient_id' => $patient->id],
                    [
                        'previous_dentist' => 'St. Luke\'s Dental Clinic',
                        'last_dental_visit' => Carbon::now()->subYears(1)->toDateString(),
                        'past_treatments' => 'Extractions of tooth 18 and 28; multiple amalgam fillings replaced with composite.',
                        'notes' => 'Generalized moderate gingivitis, calculus build-up.',
                    ]
                );
            }
        }

        $p1 = $patientModels['PAT-2026-0001'];
        $p2 = $patientModels['PAT-2026-0002'];
        $p3 = $patientModels['PAT-2026-0003'];

        // 6. Interactive Odontogram Data (Dental Charts & History) for Patient 1
        $chartDataP1 = [
            ['tooth' => 16, 'surface' => 'occlusal', 'condition' => 'filled', 'notes' => 'Composite filling sound and functional'],
            ['tooth' => 26, 'surface' => 'occlusal', 'condition' => 'caries', 'notes' => 'Incipient occlusal caries detected'],
            ['tooth' => 36, 'surface' => 'mesial', 'condition' => 'caries', 'notes' => 'Interproximal caries noted on bitewing'],
            ['tooth' => 46, 'surface' => 'whole', 'condition' => 'root_canal', 'notes' => 'Treated in 2022, asymptomatic, ready for full crown'],
            ['tooth' => 18, 'surface' => 'whole', 'condition' => 'missing', 'notes' => 'Congenitally absent / unerupted'],
            ['tooth' => 28, 'surface' => 'whole', 'condition' => 'missing', 'notes' => 'Extracted in 2020'],
            ['tooth' => 38, 'surface' => 'whole', 'condition' => 'impacted', 'notes' => 'Mesioangular impaction class II'],
            ['tooth' => 48, 'surface' => 'whole', 'condition' => 'impacted', 'notes' => 'Horizontal impaction'],
        ];

        foreach ($chartDataP1 as $c) {
            DentalChart::updateOrCreate(
                ['patient_id' => $p1->id, 'tooth_number' => $c['tooth'], 'surface' => $c['surface']],
                [
                    'condition' => $c['condition'],
                    'notes' => $c['notes'],
                    'updated_by_id' => $dentist1->id,
                ]
            );

            DentalChartHistory::create([
                'patient_id' => $p1->id,
                'tooth_number' => $c['tooth'],
                'surface' => $c['surface'],
                'previous_condition' => 'healthy',
                'new_condition' => $c['condition'],
                'notes' => 'Initial baseline odontogram charting',
                'changed_by_id' => $dentist1->id,
            ]);
        }

        // 7. Clinical Examination for Patient 1
        $exam = Examination::updateOrCreate(
            ['examination_number' => 'EXAM-2026-0001'],
            [
                'patient_id' => $p1->id,
                'dentist_id' => $dentist1->id,
                'exam_date' => Carbon::now()->subDays(5)->toDateString(),
                'chief_complaint' => 'Mild sensitivity on lower right tooth and routine checkup requested.',
                'history_of_present_complaint' => 'Sensitivity began 2 weeks ago when drinking cold coffee. No spontaneous night pain.',
                'clinical_findings' => 'Tooth 46 has old extensive filling following root canal, cusp margin unsupported. Tooth 26 occlusal enamel breakdown.',
                'diagnosis' => '1. Defective restoration on tooth 46 requiring crown; 2. Dental caries occlusal tooth 26; 3. Mild localized gingivitis.',
                'treatment_recommendations' => 'Oral prophylaxis, composite restoration on tooth 26, Zirconia crown on tooth 46.',
                'oral_exam_findings' => [
                    'gingiva' => 'Pink, localized marginal erythema on lower molars',
                    'mucosa' => 'Intact, no mucosal lesions or ulcerations',
                    'tongue' => 'Normal size, dorsal coating within normal limits',
                    'palate' => 'Hard and soft palate normal and symmetrical',
                    'occlusion' => 'Class I canine and molar relation',
                    'tmj' => 'No clicking, no tenderness or deviation on opening',
                ],
                'clinical_notes' => 'Discussed treatment plan options and material choices with patient. Patient opted for Zirconia aesthetic crown.',
            ]
        );

        // 8. Appointments (Queue & Schedule)
        $today = Carbon::today()->toDateString();
        $tomorrow = Carbon::tomorrow()->toDateString();

        $appointmentsData = [
            [
                'appointment_number' => 'APT-2026-0001',
                'patient_id' => $p1->id,
                'dentist_id' => $dentist1->id,
                'service_id' => $serviceModels['SRV-008']->id, // Zirconia Crown
                'appointment_date' => $today,
                'start_time' => '09:00:00',
                'end_time' => '10:30:00',
                'status' => 'in_treatment',
                'queue_number' => 1,
                'checked_in_at' => Carbon::now()->subMinutes(35),
                'reason' => 'Tooth 46 Crown Preparation and Digital Impression',
                'notes' => 'Pre-medicated with blood pressure checked: 122/80 mmHg.',
            ],
            [
                'appointment_number' => 'APT-2026-0002',
                'patient_id' => $p2->id,
                'dentist_id' => $dentist1->id,
                'service_id' => $serviceModels['SRV-002']->id, // Cleaning
                'appointment_date' => $today,
                'start_time' => '11:00:00',
                'end_time' => '11:45:00',
                'status' => 'waiting',
                'queue_number' => 2,
                'checked_in_at' => Carbon::now()->subMinutes(10),
                'reason' => 'Routine 6-Month Oral Prophylaxis',
                'notes' => 'Patient in reception waiting area.',
            ],
            [
                'appointment_number' => 'APT-2026-0003',
                'patient_id' => $p3->id,
                'dentist_id' => $dentist2->id,
                'service_id' => $serviceModels['SRV-005']->id, // Wisdom tooth
                'appointment_date' => $tomorrow,
                'start_time' => '14:00:00',
                'end_time' => '15:30:00',
                'status' => 'confirmed',
                'queue_number' => null,
                'checked_in_at' => null,
                'reason' => 'Odontectomy / Wisdom Tooth Extraction Consultation',
                'notes' => 'Needs panoramic X-ray evaluation.',
            ],
            [
                'appointment_number' => 'APT-2026-0004',
                'patient_id' => $p1->id,
                'dentist_id' => $dentist1->id,
                'service_id' => $serviceModels['SRV-001']->id,
                'appointment_date' => Carbon::now()->subDays(5)->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '09:45:00',
                'status' => 'completed',
                'queue_number' => 1,
                'checked_in_at' => Carbon::now()->subDays(5)->setHour(8)->setMinute(50),
                'reason' => 'Initial comprehensive examination',
                'notes' => 'Completed examination and treatment planning.',
            ],
        ];

        $createdAppointments = [];
        foreach ($appointmentsData as $apt) {
            $createdAppointments[$apt['appointment_number']] = Appointment::updateOrCreate(
                ['appointment_number' => $apt['appointment_number']],
                $apt
            );
        }

        // 9. Treatment Plan for Patient 1
        $plan = TreatmentPlan::updateOrCreate(
            ['plan_number' => 'TRP-2026-0001'],
            [
                'patient_id' => $p1->id,
                'dentist_id' => $dentist1->id,
                'title' => 'Quadrant 4 Rehabilitation & Restorative Plan',
                'diagnosis' => 'Defective restoration tooth 46 post-endodontics; Dental caries tooth 26.',
                'total_estimated_cost' => 18300.00,
                'status' => 'accepted',
                'patient_decision' => 'Accepted all phases after consultation.',
                'decision_date' => Carbon::now()->subDays(5)->toDateString(),
                'notes' => 'Split into two visits: Visit 1 for cleaning & restoration, Visit 2 for crown prep.',
            ]
        );

        $planItem1 = TreatmentPlanItem::updateOrCreate(
            ['treatment_plan_id' => $plan->id, 'procedure_name' => 'Oral Prophylaxis (Deep Cleaning)'],
            [
                'service_id' => $serviceModels['SRV-002']->id,
                'tooth_number' => 'Full Mouth',
                'surface' => 'All',
                'estimated_cost' => 1500.00,
                'priority' => 'high',
                'sessions_required' => 1,
                'status' => 'completed',
                'notes' => 'Completed on initial visit',
            ]
        );

        $planItem2 = TreatmentPlanItem::updateOrCreate(
            ['treatment_plan_id' => $plan->id, 'procedure_name' => 'Composite Filling tooth 26'],
            [
                'service_id' => $serviceModels['SRV-003']->id,
                'tooth_number' => '26',
                'surface' => 'Occlusal',
                'estimated_cost' => 1800.00,
                'priority' => 'medium',
                'sessions_required' => 1,
                'status' => 'completed',
                'notes' => 'Completed on initial visit',
            ]
        );

        $planItem3 = TreatmentPlanItem::updateOrCreate(
            ['treatment_plan_id' => $plan->id, 'procedure_name' => 'Full Zirconia Aesthetic Crown tooth 46'],
            [
                'service_id' => $serviceModels['SRV-008']->id,
                'tooth_number' => '46',
                'surface' => 'Whole',
                'estimated_cost' => 15000.00,
                'priority' => 'high',
                'sessions_required' => 2,
                'status' => 'in_progress',
                'notes' => 'Crown preparation session underway today',
            ]
        );

        // 10. Completed Treatment Records
        $treat1 = Treatment::updateOrCreate(
            ['treatment_number' => 'TRT-2026-0001'],
            [
                'patient_id' => $p1->id,
                'dentist_id' => $dentist1->id,
                'appointment_id' => $createdAppointments['APT-2026-0004']->id,
                'treatment_plan_item_id' => $planItem1->id,
                'procedure_name' => 'Oral Prophylaxis (Deep Cleaning)',
                'tooth_number' => 'Full Mouth',
                'surface' => 'All',
                'diagnosis' => 'Generalized marginal gingivitis with supragingival calculus',
                'procedure_notes' => 'Ultrasonic scaling performed followed by fine paste polishing and fluoride application.',
                'materials_used' => 'Prophy paste fine grit, 1.23% APF topical fluoride gel',
                'anesthesia' => 'None required',
                'complications' => 'None. Slight bleeding on lower anteriors, stopped immediately.',
                'follow_up_instructions' => 'Avoid rinsing or eating for 30 minutes. Gentle brushing.',
                'cost' => 1500.00,
                'payment_status' => 'paid',
            ]
        );

        $treat2 = Treatment::updateOrCreate(
            ['treatment_number' => 'TRT-2026-0002'],
            [
                'patient_id' => $p1->id,
                'dentist_id' => $dentist1->id,
                'appointment_id' => $createdAppointments['APT-2026-0004']->id,
                'treatment_plan_item_id' => $planItem2->id,
                'procedure_name' => 'Composite Light-Cure Tooth Filling',
                'tooth_number' => '26',
                'surface' => 'Occlusal',
                'diagnosis' => 'Dental caries occlusal pit',
                'procedure_notes' => 'Caries excavation done under rubber dam isolation. 37% phosphoric acid etch 15s, bonding agent light cured 20s, shade A2 nano-hybrid composite placed in increments.',
                'materials_used' => '3M Filtek Z350 XT Shade A2, Scotchbond Universal, Etchant',
                'anesthesia' => 'Infiltration 2% Lidocaine with 1:100,000 Epinephrine (1.0 mL)',
                'complications' => 'None',
                'follow_up_instructions' => 'Avoid biting hard items until anesthesia wears off.',
                'cost' => 1800.00,
                'payment_status' => 'paid',
            ]
        );

        // 11. Prescription for Patient 1
        $rx = Prescription::updateOrCreate(
            ['rx_number' => 'RX-2026-0001'],
            [
                'patient_id' => $p1->id,
                'dentist_id' => $dentist1->id,
                'appointment_id' => $createdAppointments['APT-2026-0004']->id,
                'prescription_date' => Carbon::now()->subDays(5)->toDateString(),
                'notes' => 'Post-treatment pain relief and oral hygiene rinse.',
            ]
        );

        PrescriptionItem::updateOrCreate(
            ['prescription_id' => $rx->id, 'medication_name' => 'Mefenamic Acid 500mg Capsule'],
            [
                'dosage' => '500 mg',
                'frequency' => 'Every 8 hours as needed for dental pain',
                'duration' => '3 days',
                'quantity' => 9,
                'instructions' => 'Take with or immediately after food.',
            ]
        );

        PrescriptionItem::updateOrCreate(
            ['prescription_id' => $rx->id, 'medication_name' => 'Chlorhexidine Digluconate 0.12% Oral Rinse'],
            [
                'dosage' => '15 mL',
                'frequency' => 'Twice daily after brushing',
                'duration' => '7 days',
                'quantity' => 1,
                'instructions' => 'Swish for 30 seconds then spit out. Do not swallow.',
            ]
        );

        // 12. Invoices & Billing
        $inv = Invoice::updateOrCreate(
            ['invoice_number' => 'INV-2026-0001'],
            [
                'patient_id' => $p1->id,
                'dentist_id' => $dentist1->id,
                'appointment_id' => $createdAppointments['APT-2026-0004']->id,
                'invoice_date' => Carbon::now()->subDays(5)->toDateString(),
                'due_date' => Carbon::now()->subDays(5)->toDateString(),
                'subtotal' => 3300.00,
                'discount_type' => 'fixed',
                'discount_value' => 300.00,
                'discount_amount' => 300.00,
                'tax_amount' => 0.00,
                'total_amount' => 3000.00,
                'paid_amount' => 3000.00,
                'balance_amount' => 0.00,
                'status' => 'paid',
                'notes' => 'Prompt payment professional courtesy discount applied.',
            ]
        );

        InvoiceItem::updateOrCreate(
            ['invoice_id' => $inv->id, 'item_name' => 'Oral Prophylaxis (Deep Cleaning)'],
            [
                'service_id' => $serviceModels['SRV-002']->id,
                'treatment_id' => $treat1->id,
                'description' => 'Full mouth scaling and polishing',
                'quantity' => 1,
                'unit_price' => 1500.00,
                'total_price' => 1500.00,
            ]
        );

        InvoiceItem::updateOrCreate(
            ['invoice_id' => $inv->id, 'item_name' => 'Composite Light-Cure Tooth Filling (Tooth 26)'],
            [
                'service_id' => $serviceModels['SRV-003']->id,
                'treatment_id' => $treat2->id,
                'description' => 'Occlusal surface restoration',
                'quantity' => 1,
                'unit_price' => 1800.00,
                'total_price' => 1800.00,
            ]
        );

        // 13. Payment Receipt
        Payment::updateOrCreate(
            ['payment_number' => 'PAY-2026-0001'],
            [
                'invoice_id' => $inv->id,
                'patient_id' => $p1->id,
                'amount' => 3000.00,
                'payment_method' => 'gcash',
                'reference_number' => 'GCASH-983419482',
                'receipt_number' => 'OR-2026-0001',
                'cashier_id' => $cashier->id,
                'payment_date' => Carbon::now()->subDays(5)->setHour(10)->setMinute(30),
                'notes' => 'Paid in full via GCash e-wallet transfer.',
            ]
        );

        // 14. Follow-Up & Recall Tracking
        FollowUp::updateOrCreate(
            ['patient_id' => $p1->id, 'follow_up_type' => 'routine_recall_6mo'],
            [
                'dentist_id' => $dentist1->id,
                'treatment_id' => $treat1->id,
                'scheduled_date' => Carbon::now()->addMonths(6)->toDateString(),
                'status' => 'pending',
                'notes' => 'Six-month dental prophylaxis and examination recall.',
            ]
        );

        FollowUp::updateOrCreate(
            ['patient_id' => $p1->id, 'follow_up_type' => 'treatment_check'],
            [
                'dentist_id' => $dentist1->id,
                'treatment_id' => $treat2->id,
                'scheduled_date' => Carbon::now()->addDays(7)->toDateString(),
                'status' => 'pending',
                'notes' => 'Check occlusion and margins on tooth 26 restoration.',
            ]
        );

        // 15. Audit Trail Logs (Philippine RA 10173 compliance)
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'patient_registered',
            'model_type' => Patient::class,
            'model_id' => $p1->id,
            'description' => 'Registered new patient Jose Dela Cruz (PAT-2026-0001) with RA 10173 privacy consent signed.',
            'new_values' => ['patient_number' => 'PAT-2026-0001', 'name' => 'Jose Dela Cruz', 'privacy_consent' => true],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);

        AuditLog::create([
            'user_id' => $dentist1->id,
            'action' => 'odontogram_updated',
            'model_type' => DentalChart::class,
            'model_id' => $p1->id,
            'description' => 'Updated odontogram for patient Jose Dela Cruz: charted caries on teeth 26, 36; root canal on tooth 46.',
            'new_values' => ['teeth_affected' => [26, 36, 46]],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);

        AuditLog::create([
            'user_id' => $cashier->id,
            'action' => 'payment_recorded',
            'model_type' => Payment::class,
            'model_id' => 1,
            'description' => 'Recorded payment of ₱3,000.00 for Invoice INV-2026-0001 via GCash (Ref #GCASH-983419482).',
            'new_values' => ['amount' => 3000.00, 'receipt' => 'OR-2026-0001'],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);

        // Phase 2 Clinic Operations Seeder
        $this->call(PhaseTwoClinicOperationsSeeder::class);
    }
}
