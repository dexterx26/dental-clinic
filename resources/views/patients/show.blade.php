@extends('layouts.app')

@section('title', $patient->full_name . ' - Dental Record')

@section('content')
<!-- Patient Master Header Card -->
<div class="patient-header-card">
    <div class="patient-profile-snippet">
        <div class="patient-large-avatar">
            @if($patient->photo)
                <img src="{{ asset('storage/' . $patient->photo) }}" alt="{{ $patient->full_name }}">
            @else
                {{ strtoupper(substr($patient->first_name, 0, 1) . substr($patient->last_name, 0, 1)) }}
            @endif
        </div>
        <div class="patient-info-title">
            <div style="display: flex; align-items: center; gap: 10px;">
                <h2>{{ $patient->full_name }}</h2>
                <span class="badge {{ $patient->status === 'active' ? 'badge-success' : 'badge-secondary' }}" style="font-size: 11px;">
                    {{ ucfirst($patient->status) }}
                </span>
            </div>
            <div class="patient-meta-row">
                <span class="patient-meta-item">
                    <i class="fa-solid fa-id-badge"></i> <strong>{{ $patient->patient_number }}</strong>
                </span>
                <span class="patient-meta-item">
                    <i class="fa-solid fa-cake-candles"></i> {{ $patient->computed_age }} yrs ({{ $patient->dob ? $patient->dob->format('M d, Y') : 'N/A' }}) &bull; {{ $patient->gender }}
                </span>
                <span class="patient-meta-item">
                    <i class="fa-solid fa-phone"></i> {{ $patient->phone }}
                </span>
                @if($patient->preferredDentist)
                <span class="patient-meta-item">
                    <i class="fa-solid fa-user-doctor"></i> Dr. {{ $patient->preferredDentist->name }}
                </span>
                @endif
            </div>

            <!-- Medical Alerts row -->
            <div class="medical-alerts">
                @if($patient->medicalHistory && !empty($patient->medicalHistory->allergies))
                <span class="med-alert-pill" title="{{ $patient->medicalHistory->allergies }}">
                    <i class="fa-solid fa-triangle-exclamation"></i> ALLERGY: {{ $patient->medicalHistory->allergies }}
                </span>
                @endif
                @if($patient->medicalHistory && !empty($patient->medicalHistory->conditions))
                    @foreach((array)$patient->medicalHistory->conditions as $condName => $condVal)
                    <span class="med-alert-pill" style="background: rgba(245, 158, 11, 0.95);" title="{{ $condVal }}">
                        <i class="fa-solid fa-heart-pulse"></i> {{ $condName }}
                    </span>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Action Hub -->
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); color:white; border-color: rgba(255,255,255,0.3);">
            <i class="fa-solid fa-calendar-plus"></i> Book Appt
        </a>
        <a href="{{ route('examinations.create', $patient) }}" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); color:white; border-color: rgba(255,255,255,0.3);">
            <i class="fa-solid fa-clipboard-check"></i> New Exam
        </a>
        <a href="{{ route('treatment-plans.create', $patient) }}" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); color:white; border-color: rgba(255,255,255,0.3);">
            <i class="fa-solid fa-list-check"></i> New Plan
        </a>
        <a href="{{ route('treatments.create', $patient) }}" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); color:white; border-color: rgba(255,255,255,0.3);">
            <i class="fa-solid fa-stethoscope"></i> Procedure
        </a>
        <a href="{{ route('prescriptions.create', $patient) }}" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); color:white; border-color: rgba(255,255,255,0.3);">
            <i class="fa-solid fa-prescription"></i> Rx
        </a>
        <a href="{{ route('invoices.create', $patient) }}" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.15); color:white; border-color: rgba(255,255,255,0.3);">
            <i class="fa-solid fa-receipt"></i> Invoice
        </a>
        <a href="{{ route('patients.edit', $patient) }}" class="btn btn-secondary btn-sm" style="background: white; color: var(--primary);">
            <i class="fa-solid fa-user-pen"></i> Edit Profile
        </a>
    </div>
</div>

@php
    $currentTab = request('tab', 'odontogram');
@endphp

<!-- Navigation Tabs -->
<div class="tab-nav">
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'odontogram']) }}" class="tab-link {{ $currentTab === 'odontogram' ? 'active' : '' }}">
        <i class="fa-solid fa-tooth"></i> Interactive Odontogram
    </a>
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'medical']) }}" class="tab-link {{ $currentTab === 'medical' ? 'active' : '' }}">
        <i class="fa-solid fa-notes-medical"></i> Medical & Dental History
    </a>
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'examinations']) }}" class="tab-link {{ $currentTab === 'examinations' ? 'active' : '' }}">
        <i class="fa-solid fa-clipboard-check"></i> Examinations ({{ $patient->examinations->count() }})
    </a>
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'plans']) }}" class="tab-link {{ $currentTab === 'plans' ? 'active' : '' }}">
        <i class="fa-solid fa-list-check"></i> Treatment Plans ({{ $patient->treatmentPlans->count() }})
    </a>
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'treatments']) }}" class="tab-link {{ $currentTab === 'treatments' ? 'active' : '' }}">
        <i class="fa-solid fa-stethoscope"></i> Procedures Executed ({{ $patient->treatments->count() }})
    </a>
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'prescriptions']) }}" class="tab-link {{ $currentTab === 'prescriptions' ? 'active' : '' }}">
        <i class="fa-solid fa-prescription"></i> Prescriptions ({{ $patient->prescriptions->count() }})
    </a>
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'billing']) }}" class="tab-link {{ $currentTab === 'billing' ? 'active' : '' }}">
        <i class="fa-solid fa-file-invoice-dollar"></i> Invoices & Payments ({{ $patient->invoices->count() }})
    </a>
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'documents']) }}" class="tab-link {{ $currentTab === 'documents' ? 'active' : '' }}">
        <i class="fa-solid fa-x-ray"></i> X-Rays & Imaging ({{ $patient->documents->count() }})
    </a>
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'recalls']) }}" class="tab-link {{ $currentTab === 'recalls' ? 'active' : '' }}">
        <i class="fa-solid fa-clock-rotate-left"></i> Recalls & Follow-Ups ({{ $patient->followUps->count() }})
    </a>
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'lab_cases']) }}" class="tab-link {{ $currentTab === 'lab_cases' ? 'active' : '' }}">
        <i class="fa-solid fa-flask-vial"></i> Lab Cases ({{ $patient->labCases->count() }})
    </a>
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'consents']) }}" class="tab-link {{ $currentTab === 'consents' ? 'active' : '' }}">
        <i class="fa-solid fa-file-signature"></i> Consents ({{ $patient->consentForms->count() }})
    </a>
    <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'appointments']) }}" class="tab-link {{ $currentTab === 'appointments' ? 'active' : '' }}">
        <i class="fa-solid fa-calendar-days"></i> Appointments ({{ $patient->appointments->count() }})
    </a>
</div>

{{-- TAB 1: INTERACTIVE ODONTOGRAM --}}
@if($currentTab === 'odontogram')
@php
    $chartMap = [];
    foreach($patient->dentalCharts as $c) {
        $chartMap["{$c->tooth_number}_{$c->surface}"] = [
            'tooth_number' => $c->tooth_number,
            'surface' => $c->surface,
            'condition' => $c->condition,
            'notes' => $c->notes,
        ];
    }
@endphp

<div class="odontogram-container" 
     id="odontogram-app" 
     data-patient-id="{{ $patient->id }}"
     data-update-url="{{ route('dental-chart.update', $patient) }}"
     data-history-url="{{ route('dental-chart.history', $patient) }}"
     data-chart-data="{{ json_encode($chartMap) }}">

    <!-- Odontogram Toolbar -->
    <div class="odontogram-controls">
        <div>
            <div style="font-weight: 700; font-size: 14px; margin-bottom: 6px;">Select Clinical Condition:</div>
            <div class="condition-palette">
                <button type="button" class="palette-btn active" data-condition="caries">
                    <span class="swatch" style="background:#ef4444;"></span> Caries / Decay
                </button>
                <button type="button" class="palette-btn" data-condition="filled">
                    <span class="swatch" style="background:#3b82f6;"></span> Filled (Composite/Amalgam)
                </button>
                <button type="button" class="palette-btn" data-condition="crown">
                    <span class="swatch" style="background:#f59e0b;"></span> Crown (PFM/Zirconia)
                </button>
                <button type="button" class="palette-btn" data-condition="root_canal">
                    <span class="swatch" style="background:#8b5cf6;"></span> Root Canal (RCT)
                </button>
                <button type="button" class="palette-btn" data-condition="missing">
                    <span class="swatch" style="background:#cbd5e1;"></span> Missing
                </button>
                <button type="button" class="palette-btn" data-condition="extracted">
                    <span class="swatch" style="background:#64748b;"></span> Extracted
                </button>
                <button type="button" class="palette-btn" data-condition="implant">
                    <span class="swatch" style="background:#06b6d4;"></span> Implant
                </button>
                <button type="button" class="palette-btn" data-condition="fractured">
                    <span class="swatch" style="background:#ec4899;"></span> Fractured
                </button>
                <button type="button" class="palette-btn" data-condition="impacted">
                    <span class="swatch" style="background:#6366f1;"></span> Impacted
                </button>
                <button type="button" class="palette-btn" data-condition="healthy">
                    <span class="swatch" style="background:#ffffff; border:1px solid #cbd5e1;"></span> Healthy / Sound
                </button>
            </div>
        </div>

        <div style="display: flex; gap: 8px; align-items: center;">
            <div style="background: #f1f5f9; padding: 4px; border-radius: 6px; display: flex; gap: 2px;">
                <button type="button" class="numbering-toggle-btn active btn btn-sm" data-system="fdi" style="border:none; padding:4px 10px;">FDI (11-48)</button>
                <button type="button" class="numbering-toggle-btn btn btn-sm" data-system="universal" style="border:none; padding:4px 10px;">Universal (1-32)</button>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" id="view-chart-history-btn">
                <i class="fa-solid fa-clock-rotate-left"></i> History Audit
            </button>
        </div>
    </div>

    <!-- UPPER ARCH -->
    <div class="teeth-arch">
        <div class="arch-title">Upper Maxillary Arch (Right to Left)</div>
        <div class="teeth-row" id="teeth-upper-row">
            <!-- Rendered by odontogram.js -->
        </div>
    </div>

    <!-- LOWER ARCH -->
    <div class="teeth-arch">
        <div class="teeth-row" id="teeth-lower-row">
            <!-- Rendered by odontogram.js -->
        </div>
        <div class="arch-title" style="margin-top: 10px; margin-bottom: 0;">Lower Mandibular Arch (Right to Left)</div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--border); font-size: 12px; color: var(--text-muted);">
        <span><i class="fa-solid fa-circle-info"></i> Click any individual tooth surface (Buccal, Lingual, Mesial, Distal, Occlusal) to instantly apply the active condition. Double click for custom notes.</span>
        <span>Versioned historical snapshots preserved on every update.</span>
    </div>
</div>

<!-- Charted Conditions Summary Table -->
<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
            <span>Currently Charted Conditions</span>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tooth #</th>
                        <th>Surface</th>
                        <th>Condition</th>
                        <th>Clinical Notes</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patient->dentalCharts->where('condition', '!=', 'healthy') as $chart)
                    <tr>
                        <td><strong>Tooth #{{ $chart->tooth_number }}</strong></td>
                        <td><span class="badge badge-secondary">{{ ucfirst($chart->surface) }}</span></td>
                        <td>
                            <span class="badge" style="background:#f1f5f9; font-weight:700;">
                                {{ ucwords(str_replace('_', ' ', $chart->condition)) }}
                            </span>
                        </td>
                        <td>{{ $chart->notes ?? 'No notes recorded.' }}</td>
                        <td style="font-size: 11.5px; color: var(--text-light);">
                            {{ $chart->updated_at->diffForHumans() }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 24px; color: var(--text-muted);">
                            All charted teeth are currently healthy / sound.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- History Modal -->
<div id="chart-history-modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div style="background:#ffffff; border-radius:12px; width:100%; max-width:600px; max-height:80vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="padding:16px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:700;">Odontogram History Audit Log</h3>
            <button type="button" onclick="document.getElementById('chart-history-modal').style.display='none'" style="border:none; background:transparent; font-size:18px; cursor:pointer;">&times;</button>
        </div>
        <div id="chart-history-list" style="padding:20px; overflow-y:auto; flex-grow:1;">
            <!-- History items loaded via JS -->
        </div>
    </div>
</div>
@endif

{{-- TAB 2: MEDICAL & DENTAL HISTORY --}}
@if($currentTab === 'medical')
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Medical History Form -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-heart-pulse" style="color: #ef4444;"></i>
                <span>Systemic Medical History</span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('medical-history.update', $patient) }}" method="POST">
                @csrf
                @php
                    $med = $patient->medicalHistory;
                    $conditions = (array)($med ? $med->conditions : []);
                    $commonConditions = [
                        'Hypertension' => 'High Blood Pressure',
                        'Diabetes' => 'Diabetes Mellitus',
                        'Cardiac Disease' => 'Heart Disease / Murmur / Stents',
                        'Asthma' => 'Bronchial Asthma / Respiratory',
                        'Bleeding Disorder' => 'Prolonged bleeding / Hemophilia',
                        'Kidney / Liver' => 'Renal or Hepatic condition',
                        'Epilepsy' => 'Seizures / Neurological',
                        'Pregnancy' => 'Currently Pregnant (Females)',
                        'Hepatitis / TB' => 'Infectious Diseases',
                    ];
                @endphp

                <div class="form-group">
                    <label class="form-label" style="margin-bottom: 8px;">Check all conditions that apply:</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        @foreach($commonConditions as $cKey => $cLabel)
                        <div class="form-check">
                            <input type="checkbox" name="conditions[{{ $cKey }}]" id="cond_{{ Str::slug($cKey) }}" value="Positive" {{ isset($conditions[$cKey]) ? 'checked' : '' }}>
                            <label for="cond_{{ Str::slug($cKey) }}" style="font-size: 13px; cursor:pointer;">{{ $cLabel }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="custom_condition">Other Medical Conditions</label>
                    <input type="text" name="custom_condition" id="custom_condition" class="form-control" placeholder="Specify any other diagnosed conditions..." value="{{ $conditions['Other'] ?? '' }}">
                </div>

                <div class="form-group">
                    <label class="form-label required" for="allergies" style="color: #b91c1c;">Allergies (Medications, Latex, Anesthesia, Food)</label>
                    <textarea name="allergies" id="allergies" class="form-control" style="border-color: #fecaca; background: #fff5f5;" rows="2" placeholder="e.g. Penicillin, Amoxicillin, Aspirin, Ibuprofen, Latex">{{ $med->allergies ?? '' }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="current_medications">Current Daily Medications</label>
                    <textarea name="current_medications" id="current_medications" class="form-control" rows="2" placeholder="Prescription drugs, maintenance pills, supplements...">{{ $med->current_medications ?? '' }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="past_surgeries">Past Surgeries & Hospitalizations</label>
                    <textarea name="past_surgeries" id="past_surgeries" class="form-control" rows="2" placeholder="Year, procedure, complications...">{{ $med->past_surgeries ?? '' }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="lifestyle_notes">Lifestyle & Habits</label>
                    <input type="text" name="lifestyle_notes" id="lifestyle_notes" class="form-control" placeholder="Smoking (sticks/day), alcohol, coffee/tea consumption..." value="{{ $med->lifestyle_notes ?? '' }}">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Medical History
                </button>
            </form>
        </div>
    </div>

    <!-- Dental History Form -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-tooth" style="color: var(--primary);"></i>
                <span>Previous Dental Treatment History</span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('dental-history.update', $patient) }}" method="POST">
                @csrf
                @php
                    $dh = $patient->dentalHistory;
                @endphp

                <div class="form-group">
                    <label class="form-label" for="previous_dentist">Previous Dentist / Dental Clinic</label>
                    <input type="text" name="previous_dentist" id="previous_dentist" class="form-control" value="{{ $dh->previous_dentist ?? '' }}" placeholder="Name of previous dentist or clinic">
                </div>

                <div class="form-group">
                    <label class="form-label" for="last_dental_visit">Date of Last Dental Visit</label>
                    <input type="date" name="last_dental_visit" id="last_dental_visit" class="form-control" value="{{ $dh && $dh->last_dental_visit ? $dh->last_dental_visit->format('Y-m-d') : '' }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="past_treatments">Past Dental Treatments & Surgeries</label>
                    <textarea name="past_treatments" id="past_treatments" class="form-control" rows="5" placeholder="Record past root canals, extractions, orthodontic braces, implants, crowns, dentures, periodontal surgeries...">{{ $dh->past_treatments ?? '' }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="notes">Dental Sensitivities & Chief Oral Concerns</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Sensitivity to hot/cold, bleeding during brushing, jaw clicking, dental phobia...">{{ $dh->notes ?? '' }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Dental History
                </button>
            </form>
        </div>
    </div>
</div>
@endif

{{-- TAB 3: EXAMINATIONS --}}
@if($currentTab === 'examinations')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-clipboard-check" style="color: var(--primary);"></i>
            <span>Clinical Examinations & Oral Diagnoses</span>
        </div>
        <a href="{{ route('examinations.create', $patient) }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> New Dental Examination
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Exam #</th>
                        <th>Exam Date</th>
                        <th>Attending Dentist</th>
                        <th>Chief Complaint</th>
                        <th>Clinical Diagnosis</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patient->examinations as $exam)
                    <tr>
                        <td><strong>{{ $exam->examination_number }}</strong></td>
                        <td>{{ $exam->exam_date->format('M d, Y') }}</td>
                        <td>Dr. {{ $exam->dentist->name }}</td>
                        <td>{{ Str::limit($exam->chief_complaint ?? 'None recorded', 40) }}</td>
                        <td><span style="font-weight: 600; color: #0f172a;">{{ Str::limit($exam->diagnosis, 45) }}</span></td>
                        <td style="text-align: right;">
                            <a href="{{ route('examinations.show', $exam) }}" class="btn btn-secondary btn-sm">
                                View Full Report
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            No examinations recorded for this patient yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- TAB 4: TREATMENT PLANS --}}
@if($currentTab === 'plans')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
            <span>Treatment Plans & Patient Decision Tracking</span>
        </div>
        <a href="{{ route('treatment-plans.create', $patient) }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Create Treatment Plan
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Plan #</th>
                        <th>Title / Diagnosis</th>
                        <th>Dentist</th>
                        <th>Est. Cost</th>
                        <th>Status</th>
                        <th>Decision Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patient->treatmentPlans as $tp)
                    <tr>
                        <td><strong>{{ $tp->plan_number }}</strong></td>
                        <td>
                            <div style="font-weight: 600;">{{ $tp->title }}</div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ Str::limit($tp->diagnosis, 40) }}</div>
                        </td>
                        <td>Dr. {{ $tp->dentist->name }}</td>
                        <td><strong>₱{{ number_format($tp->total_estimated_cost, 2) }}</strong></td>
                        <td>
                            @php
                                $tpBadge = [
                                    'proposed' => 'badge-secondary',
                                    'presented' => 'badge-info',
                                    'accepted' => 'badge-success',
                                    'declined' => 'badge-danger',
                                    'completed' => 'badge-primary',
                                    'partially_completed' => 'badge-warning',
                                ];
                            @endphp
                            <span class="badge {{ $tpBadge[$tp->status] ?? 'badge-secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $tp->status)) }}
                            </span>
                        </td>
                        <td>{{ $tp->decision_date ? $tp->decision_date->format('M d, Y') : 'Pending' }}</td>
                        <td style="text-align: right;">
                            <a href="{{ route('treatment-plans.show', $tp) }}" class="btn btn-secondary btn-sm">Details</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            No treatment plans created for this patient yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- TAB 5: PROCEDURES EXECUTED (TREATMENTS) --}}
@if($currentTab === 'treatments')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-stethoscope" style="color: var(--primary);"></i>
            <span>Completed Clinical Procedure Execution Records</span>
        </div>
        <a href="{{ route('treatments.create', $patient) }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Record Completed Procedure
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Record #</th>
                        <th>Date</th>
                        <th>Procedure</th>
                        <th>Tooth / Surface</th>
                        <th>Dentist</th>
                        <th>Cost</th>
                        <th>Payment</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patient->treatments as $trt)
                    <tr>
                        <td><strong>{{ $trt->treatment_number }}</strong></td>
                        <td>{{ $trt->created_at->format('M d, Y') }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $trt->procedure_name }}</div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ Str::limit($trt->procedure_notes, 35) }}</div>
                        </td>
                        <td>
                            @if($trt->tooth_number)
                                <span class="badge badge-secondary">#{{ $trt->tooth_number }} ({{ $trt->surface ?? 'Whole' }})</span>
                            @else
                                <span class="badge badge-secondary">General</span>
                            @endif
                        </td>
                        <td>Dr. {{ $trt->dentist->name }}</td>
                        <td><strong>₱{{ number_format($trt->cost, 2) }}</strong></td>
                        <td>
                            <span class="badge {{ $trt->payment_status === 'paid' ? 'badge-success' : 'badge-danger' }}">
                                {{ ucfirst($trt->payment_status) }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('treatments.show', $trt) }}" class="btn btn-secondary btn-sm">Notes & Details</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            No executed procedure records on file.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- TAB 6: PRESCRIPTIONS (Rx) --}}
@if($currentTab === 'prescriptions')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-prescription" style="color: var(--primary);"></i>
            <span>Prescription History (Rx)</span>
        </div>
        <a href="{{ route('prescriptions.create', $patient) }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Issue New Prescription
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Rx #</th>
                        <th>Date</th>
                        <th>Prescribing Dentist</th>
                        <th>Medications</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patient->prescriptions as $rx)
                    <tr>
                        <td><strong style="color: var(--primary);">{{ $rx->rx_number }}</strong></td>
                        <td>{{ $rx->prescription_date->format('M d, Y') }}</td>
                        <td>Dr. {{ $rx->dentist->name }}</td>
                        <td>
                            <ul style="margin: 0; padding-left: 18px; font-size: 13px;">
                                @foreach($rx->items as $item)
                                    <li><strong>{{ $item->medication_name }}</strong> ({{ $item->dosage }}) - {{ $item->frequency }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('prescriptions.show', $rx) }}" class="btn btn-secondary btn-sm" target="_blank">
                                <i class="fa-solid fa-print"></i> Printable Rx
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            No prescriptions issued for this patient.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- TAB 7: BILLING & INVOICES --}}
@if($currentTab === 'billing')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-file-invoice-dollar" style="color: var(--primary);"></i>
            <span>Invoices & Billing History</span>
        </div>
        <a href="{{ route('invoices.create', $patient) }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Create Invoice
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patient->invoices as $inv)
                    <tr>
                        <td><strong>{{ $inv->invoice_number }}</strong></td>
                        <td>{{ $inv->invoice_date->format('M d, Y') }}</td>
                        <td>₱{{ number_format($inv->total_amount, 2) }}</td>
                        <td style="color: #15803d;">₱{{ number_format($inv->paid_amount, 2) }}</td>
                        <td style="color: {{ $inv->balance_amount > 0 ? '#b91c1c' : '#475569' }};">
                            <strong>₱{{ number_format($inv->balance_amount, 2) }}</strong>
                        </td>
                        <td>
                            @php
                                $invBadge = [
                                    'unpaid' => 'badge-danger',
                                    'partially_paid' => 'badge-warning',
                                    'paid' => 'badge-success',
                                ];
                            @endphp
                            <span class="badge {{ $invBadge[$inv->status] ?? 'badge-secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $inv->status)) }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('invoices.show', $inv) }}" class="btn btn-secondary btn-sm">
                                <i class="fa-solid fa-file-invoice"></i> View Invoice
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            No invoices recorded for this patient.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- TAB 8: DOCUMENTS & X-RAYS --}}
@if($currentTab === 'documents')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-x-ray" style="color: var(--primary);"></i>
            <span>Radiographs, Photos & Scans</span>
        </div>
    </div>
    <div class="card-body">
        <!-- Upload Form -->
        <form action="{{ route('documents.store', $patient) }}" method="POST" enctype="multipart/form-data" style="margin-bottom: 24px; padding: 16px; background: #f8fafc; border: 1px dashed var(--border); border-radius: 8px;">
            @csrf
            <div class="form-row">
                <div class="form-col">
                    <label class="form-label required" for="document_file">Select Radiograph / Image / Document</label>
                    <input type="file" name="document_file" id="document_file" class="form-control" required accept="image/*,.pdf,.dicom">
                </div>
                <div class="form-col">
                    <label class="form-label required" for="category">Category</label>
                    <select name="category" id="category" class="form-control" required>
                        <option value="xray">Periapical X-Ray</option>
                        <option value="panoramic">Panoramic Radiograph</option>
                        <option value="photo">Intraoral / Extraoral Photo</option>
                        <option value="scan">CT / Digital Scan</option>
                        <option value="report">Lab / Dental Report</option>
                        <option value="consent">Signed Consent Form</option>
                    </select>
                </div>
                <div class="form-col">
                    <label class="form-label" for="tooth_number">Related Tooth #</label>
                    <input type="text" name="tooth_number" id="tooth_number" class="form-control" placeholder="e.g. 46 or Upper Arch">
                </div>
            </div>
            <div class="form-group" style="margin-top: 12px;">
                <label class="form-label" for="description">Clinical Notes / Findings</label>
                <input type="text" name="description" id="description" class="form-control" placeholder="e.g. Post-op bitewing showing periapical bone healing">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-cloud-arrow-up"></i> Upload File
            </button>
        </form>

        <!-- Document Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px;">
            @forelse($patient->documents as $doc)
            <div style="border: 1px solid var(--border); border-radius: 8px; overflow: hidden; background: white;">
                <div style="height: 140px; background: #0f172a; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                    @if(in_array(strtolower($doc->file_type), ['jpg', 'jpeg', 'png', 'webp']))
                        <img src="{{ asset('storage/' . $doc->file_path) }}" alt="{{ $doc->file_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <i class="fa-solid fa-file-pdf" style="font-size: 48px; color: #ef4444;"></i>
                    @endif
                </div>
                <div style="padding: 12px;">
                    <div style="font-weight: 600; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $doc->file_name }}">
                        {{ $doc->file_name }}
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                        <span class="badge badge-secondary">{{ ucfirst($doc->category) }}</span>
                        @if($doc->tooth_number)<span>Tooth #{{ $doc->tooth_number }}</span>@endif
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 10px; align-items: center;">
                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-secondary btn-sm" style="padding: 3px 8px; font-size: 11px;">
                            <i class="fa-solid fa-eye"></i> View
                        </a>
                        <form action="{{ route('documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Delete this document?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" style="padding: 3px 8px; font-size: 11px;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 30px; color: var(--text-muted);">
                No radiographs or imaging documents uploaded yet.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endif

{{-- TAB 9: RECALLS & FOLLOW-UPS --}}
@if($currentTab === 'recalls')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i>
            <span>Scheduled Recalls & Preventative Follow-Ups</span>
        </div>
    </div>
    <div class="card-body">
        <!-- New Recall Form -->
        <form action="{{ route('recalls.store') }}" method="POST" style="margin-bottom: 20px; padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px solid var(--border);">
            @csrf
            <input type="hidden" name="patient_id" value="{{ $patient->id }}">
            <div class="form-row">
                <div class="form-col">
                    <label class="form-label required">Recall Type</label>
                    <select name="follow_up_type" class="form-control" required>
                        <option value="routine_recall_6mo">Routine 6-Month Oral Prophylaxis (Cleaning)</option>
                        <option value="annual_checkup">Annual Comprehensive Checkup</option>
                        <option value="treatment_check">Post-Treatment Check (Occlusion/Margin)</option>
                        <option value="orthodontic_adjustment">Orthodontic Adjustment / Wire Change</option>
                        <option value="suture_removal">Suture Removal</option>
                    </select>
                </div>
                <div class="form-col">
                    <label class="form-label required">Scheduled Date</label>
                    <input type="date" name="scheduled_date" class="form-control" required value="{{ \Carbon\Carbon::now()->addMonths(6)->format('Y-m-d') }}">
                </div>
                <div class="form-col">
                    <label class="form-label">Attending Dentist</label>
                    <select name="dentist_id" class="form-control">
                        <option value="">Any Available Dentist</option>
                        @foreach($dentists as $d)
                            <option value="{{ $d->id }}" {{ $patient->preferred_dentist_id == $d->id ? 'selected' : '' }}>Dr. {{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-top: 10px;">
                <label class="form-label">Notes</label>
                <input type="text" name="notes" class="form-control" placeholder="Recall instructions or notes...">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i> Schedule Recall
            </button>
        </form>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Due Date</th>
                        <th>Dentist</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patient->followUps as $f)
                    <tr>
                        <td><strong>{{ ucwords(str_replace('_', ' ', $f->follow_up_type)) }}</strong></td>
                        <td>{{ $f->scheduled_date->format('M d, Y') }}</td>
                        <td>{{ $f->dentist ? 'Dr. ' . $f->dentist->name : 'Clinic Staff' }}</td>
                        <td>
                            @php
                                $rBadge = [
                                    'pending' => 'badge-warning',
                                    'confirmed' => 'badge-info',
                                    'completed' => 'badge-success',
                                    'cancelled' => 'badge-danger',
                                    'overdue' => 'badge-danger',
                                ];
                            @endphp
                            <span class="badge {{ $rBadge[$f->status] ?? 'badge-secondary' }}">{{ ucfirst($f->status) }}</span>
                        </td>
                        <td>{{ $f->notes ?? '-' }}</td>
                        <td style="text-align: right;">
                            @if($f->status !== 'completed')
                            <form action="{{ route('recalls.update-status', $f) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-success btn-sm">Mark Done</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: var(--text-muted);">
                            No recalls scheduled.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- TAB 10: APPOINTMENTS HISTORY --}}
@if($currentTab === 'appointments')
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-calendar-days" style="color: var(--primary);"></i>
            <span>Appointments & Visit Timeline</span>
        </div>
        <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-calendar-plus"></i> Schedule Appointment
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Appt #</th>
                        <th>Date & Time</th>
                        <th>Attending Dentist</th>
                        <th>Service / Reason</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patient->appointments as $apt)
                    <tr>
                        <td><strong>{{ $apt->appointment_number }}</strong></td>
                        <td>
                            <strong>{{ $apt->appointment_date->format('M d, Y') }}</strong>
                            <div style="font-size: 11px; color: var(--text-light);">{{ date('h:i A', strtotime($apt->start_time)) }}</div>
                        </td>
                        <td>Dr. {{ $apt->dentist->name }}</td>
                        <td>{{ $apt->service ? $apt->service->name : ($apt->reason ?? 'Consultation') }}</td>
                        <td>
                            <span class="badge badge-secondary">{{ ucwords(str_replace('_', ' ', $apt->status)) }}</span>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('appointments.show', $apt) }}" class="btn btn-secondary btn-sm">Details</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            No appointments on record for this patient.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- TAB 11: LAB CASES --}}
@if($currentTab === 'lab_cases')
<div class="card" style="padding: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h3 style="margin: 0; font-size: 18px;"><i class="fa-solid fa-flask-vial text-primary"></i> Dental Laboratory Cases</h3>
            <p style="margin: 4px 0 0; font-size: 13px; color: var(--text-muted);">Prosthetics, crowns, and appliances manufactured for this patient</p>
        </div>
        <a href="{{ route('lab-cases.create', ['patient_id' => $patient->id]) }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Send Lab Case
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-main); text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">
                    <th style="padding: 12px 16px;">Case #</th>
                    <th style="padding: 12px 16px;">Appliance / Tooth</th>
                    <th style="padding: 12px 16px;">Laboratory</th>
                    <th style="padding: 12px 16px;">Sent / Due Date</th>
                    <th style="padding: 12px 16px;">Status</th>
                    <th style="padding: 12px 16px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($patient->labCases as $case)
                <tr style="border-top: 1px solid var(--border);">
                    <td style="padding: 12px 16px; font-weight: 700; font-family: monospace; color: var(--primary);">
                        {{ $case->case_number }}
                    </td>
                    <td style="padding: 12px 16px;">
                        <div style="font-weight: 600;">{{ $case->appliance_type }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">Tooth: {{ $case->tooth_number ?: 'N/A' }} &bull; Shade: {{ $case->shade ?: 'Standard' }}</div>
                    </td>
                    <td style="padding: 12px 16px; font-size: 13px;">{{ $case->lab_name }}</td>
                    <td style="padding: 12px 16px; font-size: 13px;">
                        <div>Sent: {{ $case->sent_date->format('M d, Y') }}</div>
                        <div style="color: var(--text-muted); font-size: 11px;">Due: {{ $case->expected_delivery_date->format('M d, Y') }}</div>
                    </td>
                    <td style="padding: 12px 16px;">
                        <span class="badge" style="background: var(--primary-light); color: var(--primary); text-transform: uppercase; font-size: 11px;">
                            {{ $case->status }}
                        </span>
                    </td>
                    <td style="padding: 12px 16px; text-align: right;">
                        <a href="{{ route('lab-cases.show', $case) }}" class="btn btn-secondary btn-sm">View Details</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
                        No laboratory cases registered for this patient.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- TAB 12: DIGITAL CONSENTS --}}
@if($currentTab === 'consents')
<div class="card" style="padding: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h3 style="margin: 0; font-size: 18px;"><i class="fa-solid fa-file-signature text-primary"></i> Digital Informed Consent Forms</h3>
            <p style="margin: 4px 0 0; font-size: 13px; color: var(--text-muted);">Legally binding, RA 10173 compliant digitally signed consents</p>
        </div>
        <a href="{{ route('consent-forms.create', ['patient_id' => $patient->id]) }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> New Consent Form
        </a>
    </div>

    <div style="overflow-x: auto;">
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-main); text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">
                    <th style="padding: 12px 16px;">Consent #</th>
                    <th style="padding: 12px 16px;">Procedure Title</th>
                    <th style="padding: 12px 16px;">Attending Dentist</th>
                    <th style="padding: 12px 16px;">Signed Timestamp</th>
                    <th style="padding: 12px 16px;">Signature</th>
                    <th style="padding: 12px 16px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($patient->consentForms as $form)
                <tr style="border-top: 1px solid var(--border);">
                    <td style="padding: 12px 16px; font-weight: 700; font-family: monospace; color: var(--primary);">
                        {{ $form->consent_number }}
                    </td>
                    <td style="padding: 12px 16px; font-weight: 600;">
                        {{ $form->title }}
                    </td>
                    <td style="padding: 12px 16px; font-size: 13px;">
                        {{ $form->dentist->name ?? 'Dr. Unassigned' }}
                    </td>
                    <td style="padding: 12px 16px; font-size: 13px;">
                        {{ $form->signed_at->format('M d, Y h:i A') }}
                    </td>
                    <td style="padding: 12px 16px;">
                        <div style="width: 60px; height: 28px; border: 1px solid var(--border); border-radius: 4px; background: #fff; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                            <img src="{{ $form->patient_signature }}" alt="Sig" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </div>
                    </td>
                    <td style="padding: 12px 16px; text-align: right;">
                        <a href="{{ route('consent-forms.show', $form) }}" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-certificate"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
                        No digital consent forms executed for this patient yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@push('scripts')
<script src="{{ asset('js/odontogram.js') }}"></script>
@endpush
@endsection
