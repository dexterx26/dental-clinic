@extends('layouts.app')

@section('title', 'Issue Prescription - ' . $patient->full_name)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Issue Dental Prescription (Rx)</h1>
        <div class="page-subtitle">Patient: <strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_number }})</div>
    </div>
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'prescriptions']) }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Patient
    </a>
</div>

<!-- Allergy Alert Warning Banner -->
@if($patient->medicalHistory && !empty($patient->medicalHistory->allergies))
<div class="alert alert-danger" style="margin-bottom: 20px;">
    <i class="fa-solid fa-triangle-exclamation" style="font-size: 20px;"></i>
    <div>
        <strong>PATIENT ALLERGY ALERT:</strong> {{ $patient->medicalHistory->allergies }}
        <div style="font-size: 12px; margin-top: 2px;">Ensure no contraindicating medications are prescribed.</div>
    </div>
</div>
@endif

<form action="{{ route('prescriptions.store', $patient) }}" method="POST" id="rx-form">
    @csrf
    @if($appointment)
        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
    @endif

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-prescription" style="color: var(--primary);"></i>
                <span>Prescription Details</span>
            </div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="dentist_id">Prescribing Dentist</label>
                        <select name="dentist_id" id="dentist_id" class="form-control" required>
                            @foreach($dentists as $d)
                                <option value="{{ $d->id }}" {{ (old('dentist_id', $appointment ? $appointment->dentist_id : $patient->preferred_dentist_id) == $d->id) ? 'selected' : '' }}>
                                    Dr. {{ $d->name }} (PRC: {{ $d->license_number ?? 'Verified' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="prescription_date">Prescription Date</label>
                        <input type="date" name="prescription_date" id="prescription_date" class="form-control" required value="{{ old('prescription_date', \Carbon\Carbon::today()->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="notes">Special Clinical Instructions / Indication</label>
                <input type="text" name="notes" id="notes" class="form-control" placeholder="e.g. Post-extraction infection prophylaxis and pain management" value="{{ old('notes') }}">
            </div>
        </div>
    </div>

    <!-- Prescription Medications Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-pills" style="color: var(--primary);"></i>
                <span>Medications</span>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" id="add-rx-item-btn">
                <i class="fa-solid fa-plus"></i> Add Medication
            </button>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Drug Name & Form</th>
                            <th style="width: 15%;">Dosage</th>
                            <th style="width: 20%;">Frequency</th>
                            <th style="width: 12%;">Duration</th>
                            <th style="width: 10%;">Qty</th>
                            <th style="width: 18%; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="rx-tbody">
                        <tr class="rx-row">
                            <td>
                                <input type="text" name="items[0][medication_name]" class="form-control" required placeholder="e.g. Amoxicillin 500mg Capsule">
                                <input type="text" name="items[0][instructions]" class="form-control" style="margin-top: 4px; font-size: 11.5px;" placeholder="Specific instructions (e.g. Take after meals)">
                            </td>
                            <td>
                                <input type="text" name="items[0][dosage]" class="form-control" required placeholder="e.g. 500 mg">
                            </td>
                            <td>
                                <input type="text" name="items[0][frequency]" class="form-control" required placeholder="e.g. Every 8 hours">
                            </td>
                            <td>
                                <input type="text" name="items[0][duration]" class="form-control" required placeholder="e.g. 7 days">
                            </td>
                            <td>
                                <input type="number" name="items[0][quantity]" class="form-control" min="1" value="21" required>
                            </td>
                            <td style="text-align: right;">
                                <button type="button" class="btn btn-danger btn-sm remove-rx-btn" style="visibility: hidden;">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 30px;">
        <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'prescriptions']) }}" class="btn btn-secondary btn-lg">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-check"></i> Generate Prescription
        </button>
    </div>
</form>

<script>
    let rxIndex = 1;
    const rxTbody = document.getElementById('rx-tbody');
    const addRxBtn = document.getElementById('add-rx-item-btn');

    addRxBtn.addEventListener('click', function () {
        const tr = document.createElement('tr');
        tr.className = 'rx-row';
        tr.innerHTML = `
            <td>
                <input type="text" name="items[${rxIndex}][medication_name]" class="form-control" required placeholder="e.g. Mefenamic Acid 500mg">
                <input type="text" name="items[${rxIndex}][instructions]" class="form-control" style="margin-top: 4px; font-size: 11.5px;" placeholder="Specific instructions">
            </td>
            <td>
                <input type="text" name="items[${rxIndex}][dosage]" class="form-control" required placeholder="e.g. 500 mg">
            </td>
            <td>
                <input type="text" name="items[${rxIndex}][frequency]" class="form-control" required placeholder="e.g. Every 8 hrs prn">
            </td>
            <td>
                <input type="text" name="items[${rxIndex}][duration]" class="form-control" required placeholder="e.g. 3 days">
            </td>
            <td>
                <input type="number" name="items[${rxIndex}][quantity]" class="form-control" min="1" value="10" required>
            </td>
            <td style="text-align: right;">
                <button type="button" class="btn btn-danger btn-sm remove-rx-btn">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;
        rxTbody.appendChild(tr);
        rxIndex++;
        bindRxRow(tr);
    });

    function bindRxRow(row) {
        const btn = row.querySelector('.remove-rx-btn');
        if (btn) {
            btn.addEventListener('click', function () { row.remove(); });
        }
    }

    document.querySelectorAll('.rx-row').forEach(bindRxRow);
</script>
@endsection
