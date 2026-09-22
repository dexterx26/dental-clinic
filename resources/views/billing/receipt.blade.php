@extends('layouts.app')

@section('title', 'Official Receipt ' . $payment->receipt_number)

@section('content')
<div class="page-header no-print">
    <div>
        <h1 class="page-title">Official Receipt: {{ $payment->receipt_number }}</h1>
        <div class="page-subtitle">Payment Ref: {{ $payment->payment_number }} &bull; Date: {{ $payment->payment_date->format('F j, Y h:i A') }}</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn btn-primary" onclick="window.print();">
            <i class="fa-solid fa-print"></i> Print Official Receipt
        </button>
        <a href="{{ route('invoices.show', $payment->invoice) }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back to Invoice
        </a>
    </div>
</div>

<!-- Printable Official Receipt Card -->
<div class="card" style="max-width: 650px; margin: 0 auto; padding: 36px; border: 1.5px solid #cbd5e1; background: white;">
    <!-- Clinic Header -->
    <div style="text-align: center; border-bottom: 2px solid #0f766e; padding-bottom: 16px; margin-bottom: 20px;">
        <h2 style="font-size: 20px; font-weight: 800; color: #0f766e;">BrightSmile Dental & Oral Health Center</h2>
        <div style="font-size: 12.5px; color: #475569;">Unit 402 Medical Arts Tower, Bonifacio Global City, Taguig</div>
        <div style="font-size: 12px; color: #64748b;">TIN: 009-876-543-000 NV &bull; Tel: (02) 8888-9999</div>
        <div style="margin-top: 10px; font-size: 16px; font-weight: 800; color: #0f172a; letter-spacing: 1px;">
            OFFICIAL RECEIPT
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; font-size: 13.5px; margin-bottom: 20px;">
        <div>
            <span style="color: #64748b;">OR Number:</span> <strong style="color: #0f766e;">{{ $payment->receipt_number }}</strong>
        </div>
        <div>
            <span style="color: #64748b;">Date:</span> <strong>{{ $payment->payment_date->format('M d, Y h:i A') }}</strong>
        </div>
    </div>

    <!-- Receipt Details -->
    <div style="background: #f8fafc; padding: 18px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 24px; font-size: 13.5px; line-height: 1.8;">
        <div>
            <span style="color: #64748b;">Received From:</span> <strong>{{ $payment->patient->full_name }}</strong>
        </div>
        <div>
            <span style="color: #64748b;">Patient Number:</span> <strong>{{ $payment->patient->patient_number }}</strong>
        </div>
        <div>
            <span style="color: #64748b;">Applied to Invoice:</span> <strong>{{ $payment->invoice->invoice_number }}</strong>
        </div>
        <div>
            <span style="color: #64748b;">Payment Method:</span> <strong>{{ strtoupper($payment->payment_method) }}</strong>
            @if($payment->reference_number)
                <span style="color: #64748b;">(Ref: {{ $payment->reference_number }})</span>
            @endif
        </div>
    </div>

    <!-- Amount In Words & Box -->
    <div style="border: 2px solid #0f766e; border-radius: 8px; padding: 16px; margin-bottom: 24px; background: #f0fdfa; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="font-size: 11px; text-transform: uppercase; color: #0f766e; font-weight: 700;">Amount Paid (PHP)</div>
            <div style="font-size: 24px; font-weight: 800; color: #0f766e;">
                ₱{{ number_format($payment->amount, 2) }}
            </div>
        </div>
        <div style="text-align: right; font-size: 12.5px; color: #334155;">
            <div>Invoice Total: ₱{{ number_format($payment->invoice->total_amount, 2) }}</div>
            <div>Remaining Balance: <strong>₱{{ number_format($payment->invoice->balance_amount, 2) }}</strong></div>
        </div>
    </div>

    @if($payment->notes)
    <div style="font-size: 12px; color: #64748b; margin-bottom: 24px;">
        Remarks: {{ $payment->notes }}
    </div>
    @endif

    <!-- Cashier Signoff -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 40px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
        <div style="font-size: 11px; color: #94a3b8;">
            Valid for dental clinic financial claims.<br>System-generated official receipt.
        </div>
        <div style="text-align: center; width: 200px;">
            <div style="border-bottom: 1px solid #0f172a; height: 35px; margin-bottom: 4px;"></div>
            <div style="font-weight: 700; font-size: 13px;">{{ $payment->cashier->name }}</div>
            <div style="font-size: 11px; color: #64748b;">Authorized Cashier</div>
        </div>
    </div>
</div>
@endsection
