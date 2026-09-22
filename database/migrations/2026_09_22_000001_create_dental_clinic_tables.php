<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. System Settings
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        // 2. Dentist Schedules
        Schema::create('dentist_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dentist_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('day_of_week'); // 0=Sunday, 1=Monday, ..., 6=Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->boolean('is_available')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Patients
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_number')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('dob');
            $table->integer('age')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->string('civil_status')->nullable();
            $table->string('nationality')->default('Filipino');
            $table->string('occupation')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->foreignId('preferred_dentist_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('referral_source')->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('privacy_consent_accepted')->default(true);
            $table->timestamp('privacy_consent_date')->nullable();
            $table->date('registration_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Patient Medical Histories
        Schema::create('patient_medical_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->json('conditions')->nullable(); // diabetes, hypertension, asthma, cardiac, bleeding, pregnancy, etc.
            $table->text('allergies')->nullable();
            $table->text('current_medications')->nullable();
            $table->text('past_surgeries')->nullable();
            $table->text('family_history')->nullable();
            $table->text('lifestyle_notes')->nullable();
            $table->foreignId('recorded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Patient Dental Histories
        Schema::create('patient_dental_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->string('previous_dentist')->nullable();
            $table->date('last_dental_visit')->nullable();
            $table->text('past_treatments')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. Services & Price Management
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('service_code')->unique();
            $table->string('name');
            $table->string('category')->default('General');
            $table->text('description')->nullable();
            $table->decimal('standard_price', 10, 2)->default(0);
            $table->integer('duration_minutes')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. Appointments & Queue
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_number')->unique();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('dentist_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', [
                'scheduled', 'confirmed', 'checked_in', 'waiting', 
                'in_consultation', 'in_treatment', 'completed', 'cancelled', 'no_show', 'rescheduled'
            ])->default('scheduled');
            $table->integer('queue_number')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 8. Dental Charts (Odontogram current status)
        Schema::create('dental_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->integer('tooth_number'); // FDI standard (11-48, 51-85)
            $table->string('surface')->default('whole'); // whole, occlusal, mesial, distal, buccal, lingual, incisal
            $table->string('condition')->default('healthy'); // healthy, caries, filled, crown, bridge, implant, root_canal, missing, extracted, fractured, etc.
            $table->text('notes')->nullable();
            $table->string('color')->nullable();
            $table->foreignId('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['patient_id', 'tooth_number', 'surface']);
        });

        // 9. Dental Chart History (Preservation & Timeline)
        Schema::create('dental_chart_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->integer('tooth_number');
            $table->string('surface');
            $table->string('previous_condition')->nullable();
            $table->string('new_condition');
            $table->text('notes')->nullable();
            $table->foreignId('changed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 10. Examinations
        Schema::create('examinations', function (Blueprint $table) {
            $table->id();
            $table->string('examination_number')->unique();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('dentist_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->date('exam_date');
            $table->text('chief_complaint')->nullable();
            $table->text('history_of_present_complaint')->nullable();
            $table->text('clinical_findings')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment_recommendations')->nullable();
            $table->json('oral_exam_findings')->nullable(); // teeth, gingiva, tongue, palate, mucosa, occlusion, tmj
            $table->text('clinical_notes')->nullable();
            $table->timestamps();
        });

        // 11. Treatment Plans
        Schema::create('treatment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_number')->unique();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('dentist_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('diagnosis')->nullable();
            $table->decimal('total_estimated_cost', 10, 2)->default(0);
            $table->enum('status', [
                'proposed', 'presented', 'accepted', 'partially_completed', 'completed', 'declined', 'cancelled'
            ])->default('proposed');
            $table->text('patient_decision')->nullable();
            $table->date('decision_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 12. Treatment Plan Items
        Schema::create('treatment_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_plan_id')->constrained('treatment_plans')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('procedure_name');
            $table->string('tooth_number')->nullable();
            $table->string('surface')->nullable();
            $table->decimal('estimated_cost', 10, 2)->default(0);
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->integer('sessions_required')->default(1);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 13. Treatments / Procedure Execution Records
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->string('treatment_number')->unique();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('dentist_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('treatment_plan_item_id')->nullable()->constrained('treatment_plan_items')->nullOnDelete();
            $table->string('procedure_name');
            $table->string('tooth_number')->nullable();
            $table->string('surface')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('procedure_notes')->nullable();
            $table->text('materials_used')->nullable();
            $table->string('anesthesia')->nullable();
            $table->text('complications')->nullable();
            $table->text('follow_up_instructions')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid');
            $table->timestamps();
        });

        // 14. Prescriptions
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('rx_number')->unique();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('dentist_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->date('prescription_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 15. Prescription Items
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->onDelete('cascade');
            $table->string('medication_name');
            $table->string('dosage');
            $table->string('frequency');
            $table->string('duration');
            $table->integer('quantity')->default(1);
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        // 16. Patient Documents & Images
        Schema::create('patient_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('treatment_id')->nullable()->constrained('treatments')->nullOnDelete();
            $table->string('tooth_number')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->integer('file_size')->nullable();
            $table->enum('category', ['xray', 'panoramic', 'photo', 'scan', 'report', 'consent', 'other'])->default('photo');
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 17. Invoices & Billing
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('dentist_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->enum('discount_type', ['fixed', 'percentage'])->nullable();
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance_amount', 10, 2)->default(0);
            $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'cancelled'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 18. Invoice Items
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('treatment_id')->nullable()->constrained('treatments')->nullOnDelete();
            $table->string('item_name');
            $table->string('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->timestamps();
        });

        // 19. Payments & Receipts
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'credit_card', 'debit_card', 'bank_transfer', 'gcash', 'maya', 'other'])->default('cash');
            $table->string('reference_number')->nullable();
            $table->string('receipt_number')->unique();
            $table->foreignId('cashier_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 20. Follow-Ups & Recalls
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('dentist_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('treatment_id')->nullable()->constrained('treatments')->nullOnDelete();
            $table->enum('follow_up_type', [
                'treatment_check', 'routine_recall_6mo', 'annual_checkup', 
                'orthodontic_adjustment', 'suture_removal', 'other'
            ])->default('treatment_check');
            $table->date('scheduled_date');
            $table->enum('status', ['pending', 'contacted', 'confirmed', 'completed', 'cancelled', 'overdue'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 21. Audit Trail (Philippine RA 10173 compliance)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // e.g. login, patient_created, odontogram_updated, invoice_generated, payment_received
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('follow_ups');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('patient_documents');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('treatments');
        Schema::dropIfExists('treatment_plan_items');
        Schema::dropIfExists('treatment_plans');
        Schema::dropIfExists('examinations');
        Schema::dropIfExists('dental_chart_histories');
        Schema::dropIfExists('dental_charts');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('services');
        Schema::dropIfExists('patient_dental_histories');
        Schema::dropIfExists('patient_medical_histories');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('dentist_schedules');
        Schema::dropIfExists('system_settings');
    }
};
