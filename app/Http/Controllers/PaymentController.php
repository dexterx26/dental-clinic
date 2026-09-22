<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Treatment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $invoice->balance_amount,
            'payment_method' => 'required|in:cash,credit_card,debit_card,bank_transfer,gcash,maya,other',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $year = date('Y');
        $payCount = Payment::whereYear('created_at', $year)->count() + 1;
        $payNumber = sprintf('PAY-%s-%04d', $year, $payCount);
        $receiptNumber = sprintf('OR-%s-%04d', $year, $payCount);

        $payment = Payment::create([
            'payment_number' => $payNumber,
            'invoice_id' => $invoice->id,
            'patient_id' => $invoice->patient_id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'] ?? null,
            'receipt_number' => $receiptNumber,
            'cashier_id' => Auth::id(),
            'payment_date' => Carbon::now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update invoice balances
        $newPaidAmount = $invoice->paid_amount + $validated['amount'];
        $newBalance = max(0, $invoice->total_amount - $newPaidAmount);
        $newStatus = ($newBalance <= 0) ? 'paid' : 'partially_paid';

        $invoice->update([
            'paid_amount' => $newPaidAmount,
            'balance_amount' => $newBalance,
            'status' => $newStatus,
        ]);

        // If invoice is fully paid, update linked treatments to paid
        if ($newStatus === 'paid') {
            foreach ($invoice->items as $item) {
                if ($item->treatment_id) {
                    Treatment::where('id', $item->treatment_id)->update(['payment_status' => 'paid']);
                }
            }
        }

        AuditLog::log('payment_received', "Received payment of ₱" . number_format($payment->amount, 2) . " ({$payment->receipt_number}) for Invoice {$invoice->invoice_number} via " . strtoupper($payment->payment_method), Payment::class, $payment->id);

        return redirect()->route('payments.receipt', $payment)->with('success', "Payment recorded! Official Receipt #{$payment->receipt_number} issued.");
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['invoice.items', 'patient', 'cashier']);
        return view('billing.receipt', compact('payment'));
    }
}
