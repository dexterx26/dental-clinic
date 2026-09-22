@extends('layouts.app')

@section('title', 'Examination Report ' . $examination->examination_number)

@section('content')
<div class="page-header no-print">
    <div>
        <h1 class="page-title">Dental Examination Report</h1>
        <div class="page-subtitle">{{ $examination->examination_number }} &bull; Exam Date: {{ $examination->exam_date->format('F j, Y') }}</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn btn-secondary" onclick="window.print();">
            <i class="fa-solid fa-print"></i> Print Report
        </button>
        <a href="{{ route('patients.show', ['patient' => $examination->patient, 'tab' => 'examinations']) }}" class="btn btn-secondary">
            <i class="fa-solid fa-folder-open"></i> Back to Patient Record
        </a>
    </div>
</div>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div class="card-header" style="border-bottom: 2px solid var(--primary);">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div class="brand-icon" style="width: 44px; height: 44px;">
                <i class="fa-solid fa-tooth"></i>
            </div>
            <div>
                <h2 style="font-size: 18px; color: var(--primary);">BrightSmile Dental & Oral Health Center</h2>
                <div style="font-size: 12px; color: var(--text-muted);">Clinical Examination & Diagnostic Findings</div>
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-weight: 700; font-size: 16px;">{{ $examination->examination_number }}</div>
            <div style="font-size: 12px; color: var(--text-light);">Date: {{ $examination->exam_date->format('M d, Y') }}</div>
        </div>
    </div>
    <div class="card-body">
        <!-- Patient Demographics Section -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border); margin-bottom: 20px;">
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Patient Information</div>
                <div style="font-size: 16px; font-weight: 700; color: var(--text-main); margin-top: 2px;">
                    {{ $examination->patient->full_name }}
                </div>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
                    ID: <strong>{{ $examination->patient->patient_number }}</strong> &bull; Age: {{ $examination->patient->computed_age }} &bull; Gender: {{ $examination->patient->gender }}
                </div>
                <div style="font-size: 13px; color: var(--text-muted);">Phone: {{ $examination->patient->phone }}</div>
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Attending Clinician</div>
                <div style="font-size: 15px; font-weight: 700; color: var(--text-main); margin-top: 2px;">
                    Dr. {{ $examination->dentist->name }}
                </div>
                <div style="font-size: 12px; color: var(--text-muted);">{{ $examination->dentist->specialization ?? 'General Dentistry' }}</div>
                @if($examination->dentist->license_number)
                <div style="font-size: 12px; color: var(--text-light);">License: {{ $examination->dentist->license_number }}</div>
                @endif
            </div>
        </div>

        <!-- Chief Complaint & HPI -->
        <div style="margin-bottom: 20px;">
            <h4 style="font-size: 14px; font-weight: 700; color: var(--primary); margin-bottom: 6px;">Chief Complaint</h4>
            <div style="background: #f8fafc; padding: 12px 16px; border-radius: 6px; font-size: 13.5px; border: 1px solid var(--border-light);">
                {{ $examination->chief_complaint ?? 'Routine oral examination / checkup requested.' }}
            </div>
        </div>

        @if($examination->history_of_present_complaint)
        <div style="margin-bottom: 20px;">
            <h4 style="font-size: 14px; font-weight: 700; color: var(--primary); margin-bottom: 6px;">History of Present Complaint</h4>
            <div style="font-size: 13.5px; color: var(--text-main); line-height: 1.6;">
                {{ $examination->history_of_present_complaint }}
            </div>
        </div>
        @endif

        <!-- Oral Examination Findings -->
        <div style="margin-bottom: 20px;">
            <h4 style="font-size: 14px; font-weight: 700; color: var(--primary); margin-bottom: 8px;">Oral & Periodontal Examination</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
                @php
                    $findings = (array)($examination->oral_exam_findings ?? []);
                @endphp
                <div style="padding: 10px; background: #f8fafc; border-radius: 6px; border: 1px solid var(--border-light);">
                    <strong>Gingiva & Periodontium:</strong>
                    <div>{{ $findings['gingiva'] ?? 'Clinically healthy, no signs of inflammation.' }}</div>
                </div>
                <div style="padding: 10px; background: #f8fafc; border-radius: 6px; border: 1px solid var(--border-light);">
                    <strong>Oral Mucosa & Palate:</strong>
                    <div>{{ $findings['mucosa'] ?? 'Intact and normal pink.' }}</div>
                </div>
                <div style="padding: 10px; background: #f8fafc; border-radius: 6px; border: 1px solid var(--border-light);">
                    <strong>Tongue & Floor:</strong>
                    <div>{{ $findings['tongue'] ?? 'Normal dorsal coating, no lesions.' }}</div>
                </div>
                <div style="padding: 10px; background: #f8fafc; border-radius: 6px; border: 1px solid var(--border-light);">
                    <strong>Occlusion & TMJ:</strong>
                    <div>{{ $findings['occlusion'] ?? 'Normal range of movement, asymptomatic.' }}</div>
                </div>
            </div>
        </div>

        @if($examination->clinical_findings)
        <div style="margin-bottom: 20px;">
            <h4 style="font-size: 14px; font-weight: 700; color: var(--primary); margin-bottom: 6px;">Detailed Hard Tissue & Radiographic Findings</h4>
            <div style="font-size: 13.5px; color: var(--text-main); line-height: 1.6; background: #ffffff; padding: 10px 0;">
                {{ $examination->clinical_findings }}
            </div>
        </div>
        @endif

        <!-- Diagnosis Box -->
        <div style="margin-bottom: 20px; padding: 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
            <h4 style="font-size: 14px; font-weight: 700; color: #166534; margin-bottom: 4px;">Clinical Diagnosis</h4>
            <div style="font-size: 15px; font-weight: 700; color: #14532d; line-height: 1.5;">
                {{ $examination->diagnosis }}
            </div>
        </div>

        @if($examination->treatment_recommendations)
        <div style="margin-bottom: 20px;">
            <h4 style="font-size: 14px; font-weight: 700; color: var(--primary); margin-bottom: 6px;">Treatment Recommendations</h4>
            <div style="font-size: 13.5px; color: var(--text-main); line-height: 1.6;">
                {{ $examination->treatment_recommendations }}
            </div>
        </div>
        @endif

        @if($examination->clinical_notes)
        <div style="margin-bottom: 30px;">
            <h4 style="font-size: 14px; font-weight: 700; color: var(--primary); margin-bottom: 6px;">Clinical Notes</h4>
            <div style="font-size: 13px; color: var(--text-muted); line-height: 1.6;">
                {{ $examination->clinical_notes }}
            </div>
        </div>
        @endif

        <!-- Doctor Signature Block -->
        <div style="display: flex; justify-content: flex-end; margin-top: 40px; padding-top: 20px; border-top: 1px solid var(--border);">
            <div style="text-align: center; width: 240px;">
                <div style="border-bottom: 1px solid #0f172a; height: 40px; margin-bottom: 6px;"></div>
                <div style="font-weight: 700; font-size: 14px;">Dr. {{ $examination->dentist->name }}</div>
                <div style="font-size: 12px; color: var(--text-muted);">Attending Dental Surgeon</div>
                @if($examination->dentist->license_number)
                <div style="font-size: 11px; color: var(--text-light);">PRC No. {{ $examination->dentist->license_number }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
