@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="page-header no-print">
    <div>
        <h1 class="page-title">Invoice: {{ $invoice->invoice_number }}</h1>
        <div class="page-subtitle">Issued on {{ $invoice->invoice_date->format('F j, Y') }}</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn btn-secondary" onclick="window.print();">
            <i class="fa-solid fa-print"></i> Print Invoice
        </button>
        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back to Invoices
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Left Column: Printable Invoice Layout -->
    <div class="card" style="padding: 30px;">
        <!-- Clinic Branding Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 2px solid var(--primary); margin-bottom: 24px;">
            <div>
                <h2 style="font-size: 20px; font-weight: 800; color: var(--primary);">BrightSmile Dental & Oral Health Center</h2>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">Unit 402 Medical Arts Tower, Bonifacio Global City, Taguig</div>
                <div style="font-size: 12px; color: var(--text-light);">Phone: +63 917 123 4567 / Email: contact@brightsmiledental.ph</div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 20px; font-weight: 800; color: #0f172a;">INVOICE</div>
                <div style="font-size: 14px; font-weight: 700; color: var(--primary);">{{ $invoice->invoice_number }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Date: {{ $invoice->invoice_date->format('M d, Y') }}</div>
            </div>
        </div>

        <!-- Billed To / Dentist Info -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Billed To (Patient)</div>
                <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-top: 2px;">
                    <a href="{{ route('patients.show', $invoice->patient) }}">{{ $invoice->patient->full_name }}</a>
                </div>
                <div style="font-size: 12.5px; color: var(--text-muted);">
                    ID: {{ $invoice->patient->patient_number }} &bull; {{ $invoice->patient->phone }}
                </div>
                @if($invoice->patient->address)
                <div style="font-size: 12px; color: var(--text-light); margin-top: 2px;">{{ $invoice->patient->address }}</div>
                @endif
            </div>

            <div style="text-align: right;">
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Attending Clinician</div>
                <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-top: 2px;">
                    {{ $invoice->dentist ? 'Dr. ' . $invoice->dentist->name : 'Clinic Staff' }}
                </div>
                <div style="font-size: 12px; color: var(--text-muted);">{{ $invoice->dentist->specialization ?? 'Dental Practice' }}</div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-responsive" style="margin-bottom: 24px;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Unit Price</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $item->item_name }}</div>
                            @if($item->description)
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $item->description }}</div>
                            @endif
                        </td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">₱{{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align: right; font-weight: 600;">₱{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Summary Box -->
        <div style="max-width: 320px; margin-left: auto; font-size: 14px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                <span style="color: var(--text-muted);">Subtotal:</span>
                <strong>₱{{ number_format($invoice->subtotal, 2) }}</strong>
            </div>

            @if($invoice->discount_amount > 0)
            <div style="display: flex; justify-content: space-between; color: #b91c1c; margin-bottom: 6px;">
                <span>Discount ({{ $invoice->discount_type === 'percentage' ? $invoice->discount_value . '%' : 'Fixed' }}):</span>
                <span>-₱{{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
            @endif

            <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: 800; color: var(--primary); padding-top: 8px; border-top: 1px solid var(--border); margin-bottom: 8px;">
                <span>Total Amount:</span>
                <span>₱{{ number_format($invoice->total_amount, 2) }}</span>
            </div>

            <div style="display: flex; justify-content: space-between; color: #15803d; margin-bottom: 6px;">
                <span>Amount Paid:</span>
                <strong>₱{{ number_format($invoice->paid_amount, 2) }}</strong>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: 800; color: {{ $invoice->balance_amount > 0 ? '#b91c1c' : '#475569' }}; padding-top: 8px; border-top: 2px solid var(--border);">
                <span>Remaining Balance:</span>
                <span>₱{{ number_format($invoice->balance_amount, 2) }}</span>
            </div>
        </div>

        @if($invoice->notes)
        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px dashed var(--border); font-size: 12px; color: var(--text-muted);">
            <strong>Remarks:</strong> {{ $invoice->notes }}
        </div>
        @endif
    </div>

    <!-- Right Column: Payments Recorded & Collect Payment Form -->
    <div class="no-print">
        <!-- Record Payment Form -->
        @if($invoice->balance_amount > 0)
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-cash-register" style="color: var(--primary);"></i>
                    <span>Record Payment</span>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('payments.store', $invoice) }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label required" for="amount">Payment Amount (₱)</label>
                        <input type="number" step="0.01" name="amount" id="amount" class="form-control" required min="1" max="{{ $invoice->balance_amount }}" value="{{ $invoice->balance_amount }}">
                        <span style="font-size: 11px; color: var(--text-light); margin-top: 2px; display: block;">Maximum: ₱{{ number_format($invoice->balance_amount, 2) }}</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="payment_method">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="gcash">GCash (e-Wallet)</option>
                            <option value="maya">Maya (e-Wallet)</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="bank_transfer">Bank Transfer (InstaPay/PESONet)</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reference_number">Reference / Trace Number</label>
                        <input type="text" name="reference_number" id="reference_number" class="form-control" placeholder="GCash ref, terminal approval code, etc.">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="payment_notes">Receipt Remarks</label>
                        <input type="text" name="notes" id="payment_notes" class="form-control" placeholder="Optional notes...">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px;">
                        <i class="fa-solid fa-receipt"></i> Process Payment & Issue Receipt
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="card" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
            <div class="card-body" style="text-align: center; padding: 24px;">
                <i class="fa-solid fa-circle-check" style="font-size: 38px; color: #166534; margin-bottom: 8px;"></i>
                <h3 style="font-size: 16px; color: #166534; font-weight: 700;">Paid in Full</h3>
                <p style="font-size: 13px; color: #15803d; margin-top: 4px;">Zero balance remaining on this invoice.</p>
            </div>
        </div>
        @endif

        <!-- Payments Received History -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-receipt" style="color: var(--primary);"></i>
                    <span>Official Receipts Issued</span>
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>OR #</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->payments as $pay)
                            <tr>
                                <td>
                                    <strong>{{ $pay->receipt_number }}</strong>
                                    <div style="font-size: 10.5px; color: var(--text-light);">{{ $pay->payment_date->format('M d, Y') }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ strtoupper($pay->payment_method) }}</span>
                                </td>
                                <td style="color: #15803d; font-weight: 700;">
                                    ₱{{ number_format($pay->amount, 2) }}
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ route('payments.receipt', $pay) }}" class="btn btn-secondary btn-sm" target="_blank" title="View Official Receipt">
                                        <i class="fa-solid fa-print"></i> OR
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 18px; color: var(--text-muted); font-size: 12.5px;">
                                    No payments recorded yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
