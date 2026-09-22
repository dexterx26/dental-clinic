@extends('layouts.app')

@section('title', 'Record Clinical Procedure - ' . $patient->full_name)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Record Executed Dental Procedure</h1>
        <div class="page-subtitle">Patient: <strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_number }})</div>
    </div>
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'treatments']) }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Patient
    </a>
</div>

<div style="max-width: 900px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-stethoscope" style="color: var(--primary);"></i>
                <span>Clinical Procedure Execution Form</span>
            </div>
            @if($planItem)
                <span class="badge badge-info">From Plan: {{ $planItem->treatmentPlan->plan_number }}</span>
            @endif
        </div>
        <div class="card-body">
            <form action="{{ route('treatments.store', $patient) }}" method="POST">
                @csrf
                @if($planItem)
                    <input type="hidden" name="treatment_plan_item_id" value="{{ $planItem->id }}">
                @endif
                @if($appointment)
                    <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                @endif

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="procedure_name">Procedure Name</label>
                            <input type="text" name="procedure_name" id="procedure_name" class="form-control" required placeholder="e.g. Composite Light-Cure Filling / Molar Root Canal" value="{{ old('procedure_name', $planItem ? $planItem->procedure_name : ($appointment && $appointment->service ? $appointment->service->name : '')) }}">
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="dentist_id">Operating Dentist</label>
                            <select name="dentist_id" id="dentist_id" class="form-control" required>
                                @foreach($dentists as $d)
                                    <option value="{{ $d->id }}" {{ (old('dentist_id', $appointment ? $appointment->dentist_id : $patient->preferred_dentist_id) == $d->id) ? 'selected' : '' }}>
                                        Dr. {{ $d->name }} ({{ $d->specialization ?? 'Dentistry' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="tooth_number">Tooth Number (FDI)</label>
                            <input type="text" name="tooth_number" id="tooth_number" class="form-control" placeholder="e.g. 16, 26, 46, Full Mouth" value="{{ old('tooth_number', $planItem ? $planItem->tooth_number : '') }}">
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="surface">Tooth Surface</label>
                            <input type="text" name="surface" id="surface" class="form-control" placeholder="e.g. Occlusal, Mesial, Buccal, Whole" value="{{ old('surface', $planItem ? $planItem->surface : '') }}">
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="cost">Procedure Cost (₱)</label>
                            <input type="number" step="0.01" name="cost" id="cost" class="form-control" required min="0" value="{{ old('cost', $planItem ? $planItem->estimated_cost : ($appointment && $appointment->service ? $appointment->service->standard_price : '0.00')) }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="diagnosis">Indication / Pre-Op Diagnosis</label>
                    <input type="text" name="diagnosis" id="diagnosis" class="form-control" placeholder="e.g. Dental caries class I occlusal surface" value="{{ old('diagnosis', $planItem ? $planItem->treatmentPlan->diagnosis : '') }}">
                </div>

                <div class="form-group">
                    <label class="form-label required" for="procedure_notes">Operative Procedure Notes</label>
                    <textarea name="procedure_notes" id="procedure_notes" class="form-control" required rows="4" placeholder="Step-by-step notes: isolation, excavation, etching/bonding, increments, light curing, bite check, polishing...">{{ old('procedure_notes') }}</textarea>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="materials_used">Dental Materials & Brands Used</label>
                            <input type="text" name="materials_used" id="materials_used" class="form-control" placeholder="e.g. 3M Filtek Z350 Shade A2, Scotchbond Universal" value="{{ old('materials_used') }}">
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="anesthesia">Local Anesthesia Administered</label>
                            <input type="text" name="anesthesia" id="anesthesia" class="form-control" placeholder="e.g. Infiltration 2% Lidocaine with 1:100,000 Epinephrine (1 cartridge)" value="{{ old('anesthesia') }}">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="complications">Complications or Adverse Events</label>
                            <input type="text" name="complications" id="complications" class="form-control" placeholder="e.g. None. Minor bleeding controlled immediately." value="{{ old('complications', 'None') }}">
                        </div>
                    </div>

                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="follow_up_instructions">Post-Operative Instructions to Patient</label>
                            <input type="text" name="follow_up_instructions" id="follow_up_instructions" class="form-control" placeholder="e.g. Avoid hot liquids while numb. Gentle brushing." value="{{ old('follow_up_instructions') }}">
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'treatments']) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-check"></i> Save Permanent Procedure Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
