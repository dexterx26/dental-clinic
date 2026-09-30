@extends('layouts.app')

@section('title', 'Consent ' . $consentForm->consent_number)

@section('content')
<div class="content-header no-print">
    <div>
        <h1 class="page-title">Consent Form: {{ $consentForm->consent_number }}</h1>
        <p class="page-subtitle">Digitally verified informed consent &bull; Signed on {{ $consentForm->signed_at->format('F d, Y h:i A') }}</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" class="btn btn-secondary" onclick="window.print()">
            <i class="fa-solid fa-print"></i>
            <span>Print Legal Copy</span>
        </button>
        <a href="{{ route('consent-forms.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Consents</span>
        </a>
    </div>
</div>

<div class="card" style="max-width: 850px; margin: 0 auto; padding: 44px; box-shadow: var(--shadow-md); border-top: 6px solid var(--primary); background: #ffffff;">
    <!-- Clinic Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 24px; border-bottom: 2px solid var(--border-light); margin-bottom: 28px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <div style="width: 44px; height: 44px; background: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 22px;">
                    <i class="fa-solid fa-tooth"></i>
                </div>
                <div>
                    <h2 style="font-size: 22px; margin: 0; color: var(--text-main);">BrightSmile Dental Clinic</h2>
                    <span style="font-size: 13px; color: var(--text-muted);">Oral Surgery & Comprehensive Dental Care</span>
                </div>
            </div>
            <div style="font-size: 12px; color: var(--text-muted); line-height: 1.4;">
                Medical Plaza Suite 400, Metro Manila, Philippines<br>
                Tel: (02) 8123-4567 &bull; DOH Clinic Reg. #2026-DCL-0891
            </div>
        </div>

        <div style="text-align: right;">
            <div style="font-size: 12px; text-transform: uppercase; font-weight: 700; color: var(--text-light); letter-spacing: 0.05em;">DOCUMENT NUMBER</div>
            <div style="font-size: 18px; font-weight: 800; font-family: monospace; color: var(--primary); margin: 3px 0;">
                {{ $consentForm->consent_number }}
            </div>
            <span class="badge" style="background: var(--success-light); color: var(--success); font-size: 11px; padding: 4px 8px;">
                <i class="fa-solid fa-shield-check"></i> RA 10173 Verified
            </span>
        </div>
    </div>

    <!-- Title -->
    <div style="text-align: center; margin-bottom: 28px;">
        <h3 style="font-size: 20px; font-weight: 800; text-transform: uppercase; color: var(--text-main); margin-bottom: 6px;">
            {{ $consentForm->title }}
        </h3>
        <div style="font-size: 13px; color: var(--text-muted);">
            Category: {{ ucwords(str_replace('_', ' ', $consentForm->consent_type)) }}
        </div>
    </div>

    <!-- Patient & Dentist Profile Box -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 28px; background: var(--bg-main); padding: 18px; border-radius: var(--radius-md); font-size: 13px;">
        <div>
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-light); margin-bottom: 6px;">PATIENT INFORMATION</div>
            <div><strong>Name:</strong> {{ $consentForm->patient->full_name }}</div>
            <div><strong>Patient ID:</strong> {{ $consentForm->patient->patient_number }}</div>
            <div><strong>DOB & Age:</strong> {{ $consentForm->patient->dob->format('M d, Y') }} ({{ $consentForm->patient->age }} years old)</div>
            <div><strong>Contact:</strong> {{ $consentForm->patient->phone }}</div>
            <div><strong>Address:</strong> {{ $consentForm->patient->address ?: 'N/A' }}</div>
        </div>

        <div>
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-light); margin-bottom: 6px;">CLINICAL METADATA</div>
            <div><strong>Attending Dentist:</strong> {{ $consentForm->dentist->name }}</div>
            <div><strong>Date & Time Signed:</strong> {{ $consentForm->signed_at->format('F d, Y h:i A') }}</div>
            @if($consentForm->witness_name)
                <div><strong>Clinical Witness:</strong> {{ $consentForm->witness_name }}</div>
            @endif
            @if($consentForm->treatment)
                <div><strong>Related Procedure:</strong> {{ $consentForm->treatment->procedure_name }}</div>
            @endif
        </div>
    </div>

    <!-- Legal Terms & Disclosures -->
    <div style="margin-bottom: 32px;">
        <h4 style="font-size: 14px; text-transform: uppercase; font-weight: 700; color: var(--text-main); margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid var(--border);">
            Terms of Informed Consent & Risk Acknowledgment
        </h4>
        <div style="font-size: 14px; line-height: 1.7; color: #1e293b; text-align: justify; white-space: pre-wrap;">{{ $consentForm->description_and_risks }}</div>
    </div>

    @if($consentForm->notes)
    <div style="margin-bottom: 28px; padding: 12px; background: var(--bg-main); border-radius: var(--radius-sm); font-size: 13px;">
        <strong>Clinical Remarks:</strong> {{ $consentForm->notes }}
    </div>
    @endif

    <!-- Legal Signatures -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 36px; padding-top: 24px; border-top: 2px solid var(--border-light);">
        <!-- Patient Digital Signature -->
        <div>
            <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">
                Patient / Legal Guardian Signature:
            </div>
            <div style="height: 110px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: #fafafa; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 8px;">
                <img src="{{ $consentForm->patient_signature }}" alt="Patient Digital Signature" style="max-height: 100%; max-width: 100%; object-fit: contain;">
            </div>
            <div style="margin-top: 6px; font-size: 12px; color: var(--text-main); font-weight: 600;">
                {{ $consentForm->patient->full_name }}
            </div>
            <div style="font-size: 11px; color: var(--text-muted);">
                Digitally captured on {{ $consentForm->signed_at->format('M d, Y h:i A') }}
            </div>
        </div>

        <!-- Attending Dentist Verification -->
        <div>
            <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">
                Attending Dentist Verification:
            </div>
            <div style="height: 110px; border: 1px dashed var(--border); border-radius: var(--radius-sm); display: flex; flex-direction: column; align-items: center; justify-content: center; background: #fafafa; padding: 8px; text-align: center;">
                <i class="fa-solid fa-user-doctor" style="font-size: 24px; color: var(--primary); margin-bottom: 6px;"></i>
                <div style="font-size: 13px; font-weight: 700; color: var(--text-main);">{{ $consentForm->dentist->name }}</div>
                <div style="font-size: 11px; color: var(--text-muted);">Licensed Dental Practitioner</div>
            </div>
            <div style="margin-top: 6px; font-size: 12px; color: var(--text-main); font-weight: 600;">
                Clinical Witness: {{ $consentForm->witness_name ?: 'Attending Staff' }}
            </div>
            <div style="font-size: 11px; color: var(--text-muted);">
                Compliant with DOH & Philippine RA 10173
            </div>
        </div>
    </div>
</div>
@endsection
