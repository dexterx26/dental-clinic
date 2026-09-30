@extends('layouts.app')

@section('title', 'Digital Consent Forms')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Digital Consent Forms & Signatures</h1>
        <p class="page-subtitle">Paperless informed consent with in-browser digital signatures (RA 10173 compliant)</p>
    </div>
    <a href="{{ route('consent-forms.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-file-signature"></i>
        <span>Execute New Consent</span>
    </a>
</div>

<!-- Filters -->
<div class="card" style="padding: 16px; margin-bottom: 20px;">
    <form action="{{ route('consent-forms.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
        <div style="flex: 1; min-width: 240px; position: relative;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 12px; color: var(--text-light);"></i>
            <input type="text" name="search" class="form-control" style="padding-left: 36px;" placeholder="Search consent #, patient name, patient ID, procedure..." value="{{ request('search') }}">
        </div>

        <div style="width: 240px;">
            <select name="consent_type" class="form-control" onchange="this.form.submit()">
                <option value="">All Consent Categories</option>
                @foreach($consentTypes as $key => $label)
                    <option value="{{ $key }}" {{ request('consent_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-secondary">Filter</button>
        @if(request()->anyFilled(['search', 'consent_type']))
            <a href="{{ route('consent-forms.index') }}" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i></a>
        @endif
    </form>
</div>

<!-- Consents Table -->
<div class="card" style="overflow: hidden;">
    <table class="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: var(--bg-main); text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">
                <th style="padding: 14px 18px;">Consent #</th>
                <th style="padding: 14px 18px;">Patient</th>
                <th style="padding: 14px 18px;">Procedure / Consent Type</th>
                <th style="padding: 14px 18px;">Attending Dentist</th>
                <th style="padding: 14px 18px;">Signed Date & Time</th>
                <th style="padding: 14px 18px;">Signature Verification</th>
                <th style="padding: 14px 18px; text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($consentForms as $form)
            <tr style="border-top: 1px solid var(--border);">
                <td style="padding: 14px 18px;">
                    <a href="{{ route('consent-forms.show', $form) }}" style="font-weight: 700; font-family: monospace; color: var(--primary);">
                        {{ $form->consent_number }}
                    </a>
                </td>
                <td style="padding: 14px 18px;">
                    <div style="font-weight: 600; color: var(--text-main);">
                        <a href="{{ route('patients.show', $form->patient) }}">{{ $form->patient->full_name }}</a>
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted);">ID: {{ $form->patient->patient_number }}</div>
                </td>
                <td style="padding: 14px 18px;">
                    <div style="font-weight: 600; font-size: 13px; color: var(--text-main);">{{ $form->title }}</div>
                    <span class="badge" style="background: var(--primary-light); color: var(--primary); font-size: 11px; margin-top: 3px;">
                        {{ $consentTypes[$form->consent_type] ?? $form->consent_type }}
                    </span>
                </td>
                <td style="padding: 14px 18px; font-size: 13px;">
                    {{ $form->dentist->name ?? 'Dr. Unassigned' }}
                </td>
                <td style="padding: 14px 18px; font-size: 13px;">
                    <div>{{ $form->signed_at->format('M d, Y') }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ $form->signed_at->format('h:i A') }}</div>
                </td>
                <td style="padding: 14px 18px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="badge" style="background: var(--success-light); color: var(--success); font-size: 11px;">
                            <i class="fa-solid fa-signature"></i> Digitally Signed
                        </span>
                        <div style="width: 50px; height: 26px; border: 1px solid var(--border); border-radius: 4px; background: #fff; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                            <img src="{{ $form->patient_signature }}" alt="Sig" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </div>
                    </div>
                </td>
                <td style="padding: 14px 18px; text-align: right;">
                    <a href="{{ route('consent-forms.show', $form) }}" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-certificate"></i> View Document
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding: 40px; text-align: center; color: var(--text-muted);">
                    No digital consent forms executed yet. Click "Execute New Consent" to record patient informed consent.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($consentForms->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
            {{ $consentForms->links() }}
        </div>
    @endif
</div>
@endsection
