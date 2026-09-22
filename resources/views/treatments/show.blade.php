@extends('layouts.app')

@section('title', 'Procedure Record ' . $treatment->treatment_number)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Procedure Record: {{ $treatment->treatment_number }}</h1>
        <div class="page-subtitle">Executed on {{ $treatment->created_at->format('l, F j, Y \a\t h:i A') }}</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn btn-secondary" onclick="window.print();">
            <i class="fa-solid fa-print"></i> Print Record
        </button>
        <a href="{{ route('patients.show', ['patient' => $treatment->patient, 'tab' => 'treatments']) }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back to Patient
        </a>
    </div>
</div>

<div class="card" style="max-width: 850px; margin: 0 auto;">
    <div class="card-header" style="border-bottom: 2px solid var(--primary);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div class="brand-icon">
                <i class="fa-solid fa-stethoscope"></i>
            </div>
            <div>
                <h2 style="font-size: 18px; color: var(--primary);">{{ $treatment->procedure_name }}</h2>
                <div style="font-size: 12px; color: var(--text-muted);">
                    @if($treatment->tooth_number)
                        Tooth #{{ $treatment->tooth_number }} ({{ $treatment->surface ?? 'Whole' }})
                    @else
                        General Clinical Procedure
                    @endif
                </div>
            </div>
        </div>
        <div>
            <span class="badge {{ $treatment->payment_status === 'paid' ? 'badge-success' : 'badge-danger' }}" style="font-size: 13px; padding: 4px 10px;">
                {{ ucfirst($treatment->payment_status) }} (₱{{ number_format($treatment->cost, 2) }})
            </span>
        </div>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border); margin-bottom: 20px;">
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Patient Details</div>
                <div style="font-size: 15px; font-weight: 700; margin-top: 2px;">
                    <a href="{{ route('patients.show', $treatment->patient) }}">{{ $treatment->patient->full_name }}</a>
                </div>
                <div style="font-size: 12.5px; color: var(--text-muted);">{{ $treatment->patient->patient_number }} &bull; {{ $treatment->patient->phone }}</div>
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Operating Dentist</div>
                <div style="font-size: 15px; font-weight: 700; margin-top: 2px;">
                    Dr. {{ $treatment->dentist->name }}
                </div>
                <div style="font-size: 12.5px; color: var(--text-muted);">PRC No: {{ $treatment->dentist->license_number ?? 'PRC-Verified' }}</div>
            </div>
        </div>

        @if($treatment->diagnosis)
        <div style="margin-bottom: 18px;">
            <h4 style="font-size: 13px; font-weight: 700; color: var(--primary); text-transform: uppercase;">Pre-Op Diagnosis / Clinical Indication</h4>
            <div style="font-size: 14px; margin-top: 4px; color: var(--text-main);">
                {{ $treatment->diagnosis }}
            </div>
        </div>
        @endif

        <div style="margin-bottom: 20px;">
            <h4 style="font-size: 13px; font-weight: 700; color: var(--primary); text-transform: uppercase;">Operative Clinical Notes</h4>
            <div style="background: #f8fafc; border: 1px solid var(--border-light); padding: 14px; border-radius: 8px; font-size: 13.5px; line-height: 1.6; white-space: pre-line;">
                {{ $treatment->procedure_notes }}
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; font-size: 13px;">
            <div style="background: #ffffff; border: 1px solid var(--border); padding: 12px; border-radius: 6px;">
                <strong style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; display: block; margin-bottom: 4px;">Materials Used</strong>
                <div>{{ $treatment->materials_used ?? 'Standard clinical consumables' }}</div>
            </div>

            <div style="background: #ffffff; border: 1px solid var(--border); padding: 12px; border-radius: 6px;">
                <strong style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; display: block; margin-bottom: 4px;">Local Anesthesia</strong>
                <div>{{ $treatment->anesthesia ?? 'None administered' }}</div>
            </div>

            <div style="background: #ffffff; border: 1px solid var(--border); padding: 12px; border-radius: 6px;">
                <strong style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; display: block; margin-bottom: 4px;">Complications / Adverse Reactions</strong>
                <div>{{ $treatment->complications ?? 'None' }}</div>
            </div>

            <div style="background: #ffffff; border: 1px solid var(--border); padding: 12px; border-radius: 6px;">
                <strong style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; display: block; margin-bottom: 4px;">Post-Op Instructions</strong>
                <div>{{ $treatment->follow_up_instructions ?? 'Standard oral hygiene care' }}</div>
            </div>
        </div>

        <!-- Dentist Signoff -->
        <div style="display: flex; justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
            <div style="text-align: center; width: 220px;">
                <div style="border-bottom: 1px solid #0f172a; height: 36px; margin-bottom: 4px;"></div>
                <div style="font-weight: 700; font-size: 13.5px;">Dr. {{ $treatment->dentist->name }}</div>
                <div style="font-size: 11px; color: var(--text-muted);">Attending Dental Surgeon</div>
            </div>
        </div>
    </div>
</div>
@endsection
