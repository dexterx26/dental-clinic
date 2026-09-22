@extends('layouts.app')

@section('title', 'Generate Invoice - ' . $patient->full_name)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Generate Clinic Invoice</h1>
        <div class="page-subtitle">Patient: <strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_number }})</div>
    </div>
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'billing']) }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Patient
    </a>
</div>

<!-- Unbilled Procedures Quick Import Alert -->
@if($unbilledTreatments->count() > 0)
<div class="card" style="border: 2px dashed #f59e0b; background: #fffbeb; margin-bottom: 20px;">
    <div class="card-header" style="background: transparent; border-bottom: 1px solid #fde68a;">
        <div class="card-title" style="color: #b45309;">
            <i class="fa-solid fa-receipt"></i>
            <span>Unbilled Executed Procedures Available ({{ $unbilledTreatments->count() }})</span>
        </div>
    </div>
    <div class="card-body" style="padding: 12px 20px;">
        <div style="font-size: 13px; color: #92400e; margin-bottom: 8px;">
            The following procedures were completed but have not yet been billed:
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            @foreach($unbilledTreatments as $ut)
            <button type="button" class="btn btn-secondary btn-sm" onclick="importTreatment({{ $ut->id }}, '{{ addslashes($ut->procedure_name) }}', {{ $ut->cost }})" style="background: white;">
                <i class="fa-solid fa-plus"></i> Add: {{ $ut->procedure_name }} (₱{{ number_format($ut->cost, 2) }})
            </button>
            @endforeach
        </div>
    </div>
</div>
@endif

<form action="{{ route('invoices.store', $patient) }}" method="POST" id="invoice-form">
    @csrf
    @if($appointmentId)
        <input type="hidden" name="appointment_id" value="{{ $appointmentId }}">
    @endif

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-file-invoice" style="color: var(--primary);"></i>
                <span>Invoice Information</span>
            </div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="dentist_id">Attending Dentist</label>
                        <select name="dentist_id" id="dentist_id" class="form-control">
                            <option value="">Clinic General</option>
                            @foreach($dentists as $d)
                                <option value="{{ $d->id }}" {{ (old('dentist_id', $patient->preferred_dentist_id) == $d->id) ? 'selected' : '' }}>
                                    Dr. {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="invoice_date">Invoice Date</label>
                        <input type="date" name="invoice_date" id="invoice_date" class="form-control" required value="{{ old('invoice_date', \Carbon\Carbon::today()->format('Y-m-d')) }}">
                    </div>
                </div>

                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="due_date">Payment Due Date</label>
                        <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date', \Carbon\Carbon::today()->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
                <span>Invoice Line Items</span>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" id="add-line-item-btn">
                <i class="fa-solid fa-plus"></i> Add Line Item
            </button>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Item Description / Procedure</th>
                            <th style="width: 15%;">Quantity</th>
                            <th style="width: 18%;">Unit Price (₱)</th>
                            <th style="width: 17%; text-align: right;">Total (₱)</th>
                        </tr>
                    </thead>
                    <tbody id="invoice-items-tbody">
                        <tr class="inv-row">
                            <td>
                                <input type="text" name="items[0][item_name]" class="form-control item-name" required placeholder="e.g. Oral Prophylaxis / Tooth Extraction">
                                <input type="hidden" name="items[0][treatment_id]" class="item-treatment-id" value="">
                            </td>
                            <td>
                                <input type="number" name="items[0][quantity]" class="form-control item-qty" min="1" value="1" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="items[0][unit_price]" class="form-control item-price" min="0" value="0.00" required>
                            </td>
                            <td style="text-align: right; font-weight: 700; padding-top: 18px;">
                                ₱<span class="row-total">0.00</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Totals & Discounts Section -->
            <div style="padding: 24px; border-top: 2px solid var(--border); background: #f8fafc;">
                <div style="max-width: 400px; margin-left: auto;">
                    <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 8px;">
                        <span>Subtotal:</span>
                        <strong>₱<span id="subtotal-val">0.00</span></strong>
                    </div>

                    <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                        <select name="discount_type" id="discount_type" class="form-control" style="flex: 1;">
                            <option value="">No Discount</option>
                            <option value="fixed">Fixed Amount (₱)</option>
                            <option value="percentage">Percentage (%)</option>
                        </select>
                        <input type="number" step="0.01" name="discount_value" id="discount_value" class="form-control" style="flex: 1;" placeholder="0" min="0" value="0">
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 14px; color: #b91c1c; margin-bottom: 12px;">
                        <span>Discount:</span>
                        <span>-₱<span id="discount-amount-val">0.00</span></span>
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: 800; color: var(--primary); padding-top: 10px; border-top: 1px solid var(--border);">
                        <span>Total Due:</span>
                        <span>₱<span id="grand-total-val">0.00</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label" for="notes">Invoice Remarks / Payment Terms</label>
        <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="e.g. Senior Citizen discount applied / Payment terms notes...">{{ old('notes') }}</textarea>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 30px;">
        <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'billing']) }}" class="btn btn-secondary btn-lg">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-receipt"></i> Save & Generate Invoice
        </button>
    </div>
</form>

<script>
    let invRowIndex = 1;
    const invTbody = document.getElementById('invoice-items-tbody');
    const addBtn = document.getElementById('add-line-item-btn');
    const discountType = document.getElementById('discount_type');
    const discountValue = document.getElementById('discount_value');

    function calculateInvoice() {
        let subtotal = 0;
        document.querySelectorAll('.inv-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value || 1);
            const price = parseFloat(row.querySelector('.item-price').value || 0);
            const rowTotal = qty * price;
            row.querySelector('.row-total').textContent = rowTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            subtotal += rowTotal;
        });

        document.getElementById('subtotal-val').textContent = subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        let discountAmt = 0;
        const discVal = parseFloat(discountValue.value || 0);
        if (discountType.value === 'percentage') {
            discountAmt = subtotal * (discVal / 100);
        } else if (discountType.value === 'fixed') {
            discountAmt = Math.min(subtotal, discVal);
        }

        document.getElementById('discount-amount-val').textContent = discountAmt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const grandTotal = Math.max(0, subtotal - discountAmt);
        document.getElementById('grand-total-val').textContent = grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    addBtn.addEventListener('click', function () {
        const tr = document.createElement('tr');
        tr.className = 'inv-row';
        tr.innerHTML = `
            <td>
                <input type="text" name="items[${invRowIndex}][item_name]" class="form-control item-name" required placeholder="Item description">
                <input type="hidden" name="items[${invRowIndex}][treatment_id]" class="item-treatment-id" value="">
            </td>
            <td>
                <input type="number" name="items[${invRowIndex}][quantity]" class="form-control item-qty" min="1" value="1" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${invRowIndex}][unit_price]" class="form-control item-price" min="0" value="0.00" required>
            </td>
            <td style="text-align: right; font-weight: 700; padding-top: 18px;">
                ₱<span class="row-total">0.00</span>
            </td>
        `;
        invTbody.appendChild(tr);
        invRowIndex++;
        bindInvRow(tr);
    });

    function bindInvRow(row) {
        row.querySelector('.item-qty').addEventListener('input', calculateInvoice);
        row.querySelector('.item-price').addEventListener('input', calculateInvoice);
    }

    function importTreatment(id, name, cost) {
        // If first row is empty, fill it; otherwise append
        const firstRow = document.querySelector('.inv-row');
        const firstInput = firstRow.querySelector('.item-name');
        if (!firstInput.value) {
            firstInput.value = name;
            firstRow.querySelector('.item-price').value = cost;
            firstRow.querySelector('.item-treatment-id').value = id;
        } else {
            const tr = document.createElement('tr');
            tr.className = 'inv-row';
            tr.innerHTML = `
                <td>
                    <input type="text" name="items[${invRowIndex}][item_name]" class="form-control item-name" required value="${name}">
                    <input type="hidden" name="items[${invRowIndex}][treatment_id]" class="item-treatment-id" value="${id}">
                </td>
                <td>
                    <input type="number" name="items[${invRowIndex}][quantity]" class="form-control item-qty" min="1" value="1" required>
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${invRowIndex}][unit_price]" class="form-control item-price" min="0" value="${cost}" required>
                </td>
                <td style="text-align: right; font-weight: 700; padding-top: 18px;">
                    ₱<span class="row-total">${cost.toFixed(2)}</span>
                </td>
            `;
            invTbody.appendChild(tr);
            invRowIndex++;
            bindInvRow(tr);
        }
        calculateInvoice();
    }

    document.querySelectorAll('.inv-row').forEach(bindInvRow);
    discountType.addEventListener('change', calculateInvoice);
    discountValue.addEventListener('input', calculateInvoice);
    calculateInvoice();
</script>
@endsection
