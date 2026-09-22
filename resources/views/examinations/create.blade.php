@extends('layouts.app')

@section('title', 'Record Dental Examination - ' . $patient->full_name)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Comprehensive Dental Examination</h1>
        <div class="page-subtitle">Patient: <strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_number }})</div>
    </div>
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'examinations']) }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Examinations
    </a>
</div>

<form action="{{ route('examinations.store', $patient) }}" method="POST">
    @csrf
    @if($appointment)
        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
    @endif

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-clipboard-user" style="color: var(--primary);"></i>
                <span>General Examination Details</span>
            </div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="dentist_id">Examining Dentist</label>
                        <select name="dentist_id" id="dentist_id" class="form-control" required>
                            @foreach($dentists as $d)
                                <option value="{{ $d->id }}" {{ (old('dentist_id', $appointment ? $appointment->dentist_id : $patient->preferred_dentist_id) == $d->id) ? 'selected' : '' }}>
                                    Dr. {{ $d->name }} ({{ $d->specialization ?? 'General Dentistry' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="exam_date">Examination Date</label>
                        <input type="date" name="exam_date" id="exam_date" class="form-control" required value="{{ old('exam_date', \Carbon\Carbon::today()->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="chief_complaint">Chief Complaint</label>
                <input type="text" name="chief_complaint" id="chief_complaint" class="form-control" placeholder="In patient's own words (e.g. Pain on upper right molar when chewing cold food)" value="{{ old('chief_complaint', $appointment ? $appointment->reason : '') }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="history_of_present_complaint">History of Present Complaint (HPI)</label>
                <textarea name="history_of_present_complaint" id="history_of_present_complaint" class="form-control" rows="2" placeholder="Onset, duration, intensity, triggers, relieving factors...">{{ old('history_of_present_complaint') }}</textarea>
            </div>
        </div>
    </div>

    <!-- Intraoral & Soft Tissue Findings -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-tooth" style="color: var(--primary);"></i>
                <span>Oral & Periodontal Clinical Findings</span>
            </div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="oral_gingiva">Gingiva & Periodontium</label>
                        <input type="text" name="oral_exam[gingiva]" id="oral_gingiva" class="form-control" placeholder="e.g. Pink, firm, localized marginal erythema on lower molars">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="oral_mucosa">Oral Mucosa & Palate</label>
                        <input type="text" name="oral_exam[mucosa]" id="oral_mucosa" class="form-control" placeholder="e.g. Intact, moist, no mucosal ulcerations">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="oral_tongue">Tongue & Floor of Mouth</label>
                        <input type="text" name="oral_exam[tongue]" id="oral_tongue" class="form-control" placeholder="e.g. Normal dorsal surface, no induration">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="oral_occlusion">Occlusion & TMJ</label>
                        <input type="text" name="oral_exam[occlusion]" id="oral_occlusion" class="form-control" placeholder="e.g. Class I canine/molar; No TMJ clicking, normal range of motion">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="clinical_findings">Detailed Clinical & Hard Tissue Findings</label>
                <textarea name="clinical_findings" id="clinical_findings" class="form-control" rows="3" placeholder="Teeth conditions, defective restorations, calculus build-up, mobility, attrition...">{{ old('clinical_findings') }}</textarea>
            </div>
        </div>
    </div>

    <!-- Diagnosis & Recommendations -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-file-medical" style="color: var(--primary);"></i>
                <span>Diagnosis & Treatment Recommendations</span>
            </div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label required" for="diagnosis">Definitive / Working Diagnosis</label>
                <textarea name="diagnosis" id="diagnosis" class="form-control" required rows="2" placeholder="e.g. 1. Dental caries on tooth 26; 2. Defective restoration on tooth 46; 3. Generalized chronic marginal gingivitis">{{ old('diagnosis') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="treatment_recommendations">Recommended Treatment Options</label>
                <textarea name="treatment_recommendations" id="treatment_recommendations" class="form-control" rows="2" placeholder="e.g. Oral prophylaxis, Composite filling tooth 26, Full porcelain/zirconia crown tooth 46">{{ old('treatment_recommendations') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="clinical_notes">Dentist Clinical Notes & Discussion</label>
                <textarea name="clinical_notes" id="clinical_notes" class="form-control" rows="2" placeholder="Patient preferences, aesthetic considerations, consent discussions...">{{ old('clinical_notes') }}</textarea>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 30px;">
        <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'examinations']) }}" class="btn btn-secondary btn-lg">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-floppy-disk"></i> Save Examination Report
        </button>
    </div>
</form>
@endsection
