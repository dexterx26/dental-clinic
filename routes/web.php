<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ConsentFormController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DentalChartController;
use App\Http\Controllers\DentalHistoryController;
use App\Http\Controllers\DentalLabController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExaminationController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MedicalHistoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\RecallController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TreatmentPlanController;
use App\Http\Controllers\TreatmentRecordController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Patients & Clinical Records
    Route::resource('patients', PatientController::class);
    Route::post('/patients/{patient}/medical-history', [MedicalHistoryController::class, 'update'])->name('medical-history.update');
    Route::post('/patients/{patient}/dental-history', [DentalHistoryController::class, 'update'])->name('dental-history.update');
    Route::post('/patients/{patient}/dental-chart', [DentalChartController::class, 'update'])->name('dental-chart.update');
    Route::get('/patients/{patient}/dental-chart/history', [DentalChartController::class, 'history'])->name('dental-chart.history');
    Route::get('/patients/{patient}/odontogram-compare', [DentalChartController::class, 'compare'])->name('patients.odontogram.compare');

    // Examinations
    Route::get('/patients/{patient}/examinations/create', [ExaminationController::class, 'create'])->name('examinations.create');
    Route::post('/patients/{patient}/examinations', [ExaminationController::class, 'store'])->name('examinations.store');
    Route::get('/examinations/{examination}', [ExaminationController::class, 'show'])->name('examinations.show');

    // Treatment Plans
    Route::get('/patients/{patient}/treatment-plans/create', [TreatmentPlanController::class, 'create'])->name('treatment-plans.create');
    Route::post('/patients/{patient}/treatment-plans', [TreatmentPlanController::class, 'store'])->name('treatment-plans.store');
    Route::get('/treatment-plans/{treatmentPlan}', [TreatmentPlanController::class, 'show'])->name('treatment-plans.show');
    Route::patch('/treatment-plans/{treatmentPlan}/status', [TreatmentPlanController::class, 'updateStatus'])->name('treatment-plans.update-status');

    // Completed Treatment Records
    Route::get('/patients/{patient}/treatments/create', [TreatmentRecordController::class, 'create'])->name('treatments.create');
    Route::post('/patients/{patient}/treatments', [TreatmentRecordController::class, 'store'])->name('treatments.store');
    Route::get('/treatments/{treatment}', [TreatmentRecordController::class, 'show'])->name('treatments.show');

    // Prescriptions
    Route::get('/patients/{patient}/prescriptions/create', [PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::post('/patients/{patient}/prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');
    Route::get('/prescriptions/{prescription}', [PrescriptionController::class, 'show'])->name('prescriptions.show');

    // Documents & X-Rays
    Route::post('/patients/{patient}/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Appointments & Queue
    Route::resource('appointments', AppointmentController::class);
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.update-status');
    Route::get('/queue', [QueueController::class, 'index'])->name('queue.index');
    Route::post('/appointments/{appointment}/check-in', [QueueController::class, 'checkIn'])->name('queue.check-in');

    // Billing & Payments
    Route::get('/invoices', [BillingController::class, 'index'])->name('invoices.index');
    Route::get('/patients/{patient}/invoices/create', [BillingController::class, 'create'])->name('invoices.create');
    Route::post('/invoices/{patient}', [BillingController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [BillingController::class, 'show'])->name('invoices.show');
    Route::post('/invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    // Follow-ups & Recalls
    Route::get('/recalls', [RecallController::class, 'index'])->name('recalls.index');
    Route::post('/recalls', [RecallController::class, 'store'])->name('recalls.store');
    Route::patch('/recalls/{recall}/status', [RecallController::class, 'updateStatus'])->name('recalls.update-status');

    // Digital Consent Forms
    Route::resource('consent-forms', ConsentFormController::class)->only(['index', 'create', 'store', 'show']);

    // Dental Laboratory Cases
    Route::resource('lab-cases', DentalLabController::class);
    Route::patch('/lab-cases/{labCase}/status', [DentalLabController::class, 'updateStatus'])->name('lab-cases.update-status');

    // Dental Supplies Inventory & Stock Movements
    Route::resource('inventory', InventoryController::class);
    Route::post('/inventory/{inventory}/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjust');
    Route::get('/inventory/{inventory}/movements', [InventoryController::class, 'movements'])->name('inventory.movements');

    // Suppliers & Purchase Orders
    Route::resource('suppliers', SupplierController::class)->only(['index', 'store', 'update']);
    Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('/purchase-orders/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.update-status');
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'markReceived'])->name('purchase-orders.receive');

    // Reports & Analytics (Admin, Dentist, Cashier)
    Route::middleware(['role:administrator,dentist,cashier'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // Administrator Only Routes
    Route::middleware(['role:administrator'])->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
        Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/backup', [SettingController::class, 'backup'])->name('settings.backup');
    });
});
