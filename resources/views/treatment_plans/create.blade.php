@extends('layouts.app')

@section('title', 'Create Treatment Plan - ' . $patient->full_name)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Create Treatment Plan</h1>
        <div class="page-subtitle">Patient: <strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_number }})</div>
    </div>
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'plans']) }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Plans
    </a>
</div>

<form action="{{ route('treatment-plans.store', $patient) }}" method="POST" id="treatment-plan-form">
    @csrf

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-file-signature" style="color: var(--primary);"></i>
                <span>Plan Overview</span>
            </div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="title">Treatment Plan Title</label>
                        <input type="text" name="title" id="title" class="form-control" required placeholder="e.g. Quadrant 1 Restorative Plan / Full Arch Rehabilitation" value="{{ old('title') }}">
                    </div>
                </div>

                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="dentist_id">Prescribing Dentist</label>
                        <select name="dentist_id" id="dentist_id" class="form-control" required>
                            @foreach($dentists as $d)
                                <option value="{{ $d->id }}" {{ (old('dentist_id', $patient->preferred_dentist_id) == $d->id) ? 'selected' : '' }}>
                                    Dr. {{ $d->name }} ({{ $d->specialization ?? 'Dentistry' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="diagnosis">Associated Diagnosis</label>
                <input type="text" name="diagnosis" id="diagnosis" class="form-control" placeholder="e.g. Deep caries tooth 16, missing tooth 15 requiring bridge" value="{{ old('diagnosis') }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="notes">Treatment Plan Notes & Patient Discussion</label>
                <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Discussion of treatment phases, alternative options offered, estimated duration...">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>

    <!-- Treatment Plan Items Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-list-ol" style="color: var(--primary);"></i>
                <span>Proposed Procedures & Steps</span>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" id="add-plan-item-btn">
                <i class="fa-solid fa-plus"></i> Add Procedure Step
            </button>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" id="plan-items-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Procedure Name / Catalog</th>
                            <th style="width: 12%;">Tooth #</th>
                            <th style="width: 12%;">Surface</th>
                            <th style="width: 12%;">Priority</th>
                            <th style="width: 10%;">Sessions</th>
                            <th style="width: 15%;">Estimated Cost (₱)</th>
                            <th style="width: 14%; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="plan-items-tbody">
                        <!-- Default First Item -->
                        <tr class="plan-item-row">
                            <td>
                                <input type="text" name="items[0][procedure_name]" class="form-control item-name" required placeholder="e.g. Composite Light-Cure Filling">
                            </td>
                            <td>
                                <input type="text" name="items[0][tooth_number]" class="form-control" placeholder="Tooth # (e.g. 16)">
                            </td>
                            <td>
                                <input type="text" name="items[0][surface]" class="form-control" placeholder="e.g. Occlusal">
                            </td>
                            <td>
                                <select name="items[0][priority]" class="form-control">
                                    <option value="high">High</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="low">Low</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="items[0][sessions_required]" class="form-control" min="1" value="1" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" name="items[0][estimated_cost]" class="form-control item-cost" min="0" value="0.00" required>
                            </td>
                            <td style="text-align: right;">
                                <button type="button" class="btn btn-danger btn-sm remove-row-btn" style="visibility: hidden;">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background: #f8fafc; font-weight: 700;">
                            <td colspan="5" style="text-align: right; padding: 14px 16px;">Total Estimated Plan Cost:</td>
                            <td style="padding: 14px 16px; font-size: 16px; color: var(--primary);">
                                ₱<span id="total-cost-display">0.00</span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 30px;">
        <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'plans']) }}" class="btn btn-secondary btn-lg">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-check"></i> Save Treatment Plan
        </button>
    </div>
</form>

<script>
    let rowIndex = 1;
    const tbody = document.getElementById('plan-items-tbody');
    const addBtn = document.getElementById('add-plan-item-btn');
    const totalDisplay = document.getElementById('total-cost-display');

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.item-cost').forEach(input => {
            total += parseFloat(input.value || 0);
        });
        totalDisplay.textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    addBtn.addEventListener('click', function () {
        const tr = document.createElement('tr');
        tr.className = 'plan-item-row';
        tr.innerHTML = `
            <td>
                <input type="text" name="items[${rowIndex}][procedure_name]" class="form-control item-name" required placeholder="e.g. Porcelain Crown">
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][tooth_number]" class="form-control" placeholder="Tooth #">
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][surface]" class="form-control" placeholder="Surface">
            </td>
            <td>
                <select name="items[${rowIndex}][priority]" class="form-control">
                    <option value="high">High</option>
                    <option value="medium" selected>Medium</option>
                    <option value="low">Low</option>
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][sessions_required]" class="form-control" min="1" value="1" required>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][estimated_cost]" class="form-control item-cost" min="0" value="0.00" required>
            </td>
            <td style="text-align: right;">
                <button type="button" class="btn btn-danger btn-sm remove-row-btn">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        rowIndex++;
        bindEvents(tr);
    });

    function bindEvents(row) {
        row.querySelector('.item-cost').addEventListener('input', calculateTotal);
        const removeBtn = row.querySelector('.remove-row-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                row.remove();
                calculateTotal();
            });
        }
    }

    document.querySelectorAll('.plan-item-row').forEach(bindEvents);
    calculateTotal();
</script>
@endsection
