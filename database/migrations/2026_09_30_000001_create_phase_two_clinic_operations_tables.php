<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Phase 2 Clinic Operations:
     * - Suppliers & Purchase Orders
     * - Inventory Items & Stock Movements
     * - Dental Laboratory Management
     * - Digital Consent Forms with Signatures
     */
    public function up(): void
    {
        // 1. Suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Inventory Items (Dental Supplies & Materials)
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('product_name');
            $table->string('category')->default('Restorative'); // Restorative, Preventive, Surgical, Orthodontic, PPE, Anesthetic, Impression, Endodontic, Laboratory, Other
            $table->string('brand')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('unit')->default('piece'); // box, piece, bottle, pack, tube, kit
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->integer('stock_level')->default(0);
            $table->integer('min_stock_level')->default(5);
            $table->date('expiration_date')->nullable();
            $table->string('batch_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Stock Movements (Tracking received, used, adjusted, expired, damaged, returned)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->enum('type', ['received', 'used', 'adjusted', 'damaged', 'expired', 'returned']);
            $table->integer('quantity'); // positive or negative adjustment
            $table->integer('previous_stock');
            $table->integer('new_stock');
            $table->string('reference_type')->nullable(); // e.g. purchase_order, treatment, manual
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 4. Purchase Orders
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('received_date')->nullable();
            $table->enum('status', ['draft', 'submitted', 'ordered', 'received', 'cancelled'])->default('draft');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Purchase Order Items
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->integer('quantity_ordered');
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->timestamps();
        });

        // 6. Dental Laboratory Cases
        Schema::create('dental_lab_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('dentist_id')->constrained('users')->onDelete('cascade');
            $table->string('lab_name');
            $table->string('technician_name')->nullable();
            $table->string('appliance_type'); // Crown, Bridge, Full Denture, Partial Denture, Night Guard, Orthodontic Retainer, Implant Abutment, Veneer, Other
            $table->string('tooth_number')->nullable(); // e.g. 11, 21, Upper Arch
            $table->string('shade')->nullable(); // e.g. A1, A2, B1, Vita 3D
            $table->date('sent_date');
            $table->date('expected_delivery_date');
            $table->date('actual_delivery_date')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->enum('status', [
                'sent', 'in_progress', 'delivered', 'fitted', 'adjustment_needed', 'rejected', 'cancelled'
            ])->default('sent');
            $table->text('instructions')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 7. Digital Consent Forms
        Schema::create('consent_forms', function (Blueprint $table) {
            $table->id();
            $table->string('consent_number')->unique();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('dentist_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('treatment_id')->nullable()->constrained('treatments')->nullOnDelete();
            $table->enum('consent_type', [
                'tooth_extraction', 'root_canal', 'implant_surgery', 
                'orthodontic_treatment', 'anesthesia', 'general_treatment', 'xray', 'other'
            ])->default('general_treatment');
            $table->string('title');
            $table->longText('description_and_risks');
            $table->longText('patient_signature'); // Base64 data URI PNG from signature canvas
            $table->dateTime('signed_at');
            $table->string('witness_name')->nullable();
            $table->longText('witness_signature')->nullable();
            $table->enum('status', ['signed', 'revoked'])->default('signed');
            $table->text('notes')->nullable();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consent_forms');
        Schema::dropIfExists('dental_lab_cases');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('suppliers');
    }
};
