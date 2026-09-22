<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Treatment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');

        $query = Invoice::with(['patient', 'dentist', 'payments']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($p) use ($search) {
                      $p->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('patient_number', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $totalReceivables = Invoice::whereIn('status', ['unpaid', 'partially_paid'])->sum('balance_amount');
        $totalCollectedMonth = Invoice::whereMonth('created_at', Carbon::now()->month)->sum('paid_amount');

        return view('billing.index', compact('invoices', 'status', 'search', 'totalReceivables', 'totalCollectedMonth'));
    }

    public function create(Patient $patient, Request $request)
    {
        $dentists = User::where('role', 'dentist')->where('is_active', true)->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $unbilledTreatments = Treatment::where('patient_id', $patient->id)
            ->where('payment_status', 'unpaid')
            ->get();
        $appointmentId = $request->query('appointment_id');

        return view('billing.create', compact('patient', 'dentists', 'services', 'unbilledTreatments', 'appointmentId'));
    }

    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'dentist_id' => 'nullable|exists:users,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.treatment_id' => 'nullable|exists:treatments,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += ($item['quantity'] * $item['unit_price']);
        }

        $discountAmount = 0;
        if (!empty($validated['discount_type']) && !empty($validated['discount_value'])) {
            if ($validated['discount_type'] === 'percentage') {
                $discountAmount = ($subtotal * ($validated['discount_value'] / 100));
            } else {
                $discountAmount = min($subtotal, (float)$validated['discount_value']);
            }
        }

        $totalAmount = max(0, $subtotal - $discountAmount);

        $year = date('Y');
        $count = Invoice::whereYear('created_at', $year)->count() + 1;
        $invoiceNumber = sprintf('INV-%s-%04d', $year, $count);

        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'patient_id' => $patient->id,
            'dentist_id' => $validated['dentist_id'] ?? null,
            'appointment_id' => $validated['appointment_id'] ?? null,
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'] ?? $validated['invoice_date'],
            'subtotal' => $subtotal,
            'discount_type' => $validated['discount_type'] ?? null,
            'discount_value' => $validated['discount_value'] ?? 0,
            'discount_amount' => $discountAmount,
            'tax_amount' => 0,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'balance_amount' => $totalAmount,
            'status' => 'unpaid',
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $itemData) {
            $lineTotal = $itemData['quantity'] * $itemData['unit_price'];
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $itemData['service_id'] ?? null,
                'treatment_id' => $itemData['treatment_id'] ?? null,
                'item_name' => $itemData['item_name'],
                'description' => null,
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'total_price' => $lineTotal,
            ]);

            // If linked to a treatment, update treatment status
            if (!empty($itemData['treatment_id'])) {
                Treatment::where('id', $itemData['treatment_id'])->update(['payment_status' => 'partially_paid']);
            }
        }

        AuditLog::log('invoice_generated', "Created invoice {$invoice->invoice_number} for {$patient->full_name} for ₱" . number_format($totalAmount, 2), Invoice::class, $invoice->id);

        return redirect()->route('invoices.show', $invoice)->with('success', "Invoice {$invoice->invoiceNumber} generated successfully.");
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['patient', 'dentist', 'items.service', 'items.treatment', 'payments.cashier']);
        return view('billing.show', compact('invoice'));
    }
}
