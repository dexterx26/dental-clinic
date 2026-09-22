@extends('layouts.app')

@section('title', 'Prescription ' . $prescription->rx_number)

@section('content')
<div class="page-header no-print">
    <div>
        <h1 class="page-title">Dental Prescription: {{ $prescription->rx_number }}</h1>
        <div class="page-subtitle">Issued on {{ $prescription->prescription_date->format('F j, Y') }}</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn btn-primary" onclick="window.print();">
            <i class="fa-solid fa-print"></i> Print Rx Sheet
        </button>
        <a href="{{ route('patients.show', ['patient' => $prescription->patient, 'tab' => 'prescriptions']) }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back to Patient Record
        </a>
    </div>
</div>

<!-- Standard Dental Prescription Sheet Layout -->
<div class="card" style="max-width: 750px; margin: 0 auto; padding: 40px; background: #ffffff; border: 1px solid #cbd5e1; box-shadow: 0 10px 25px rgba(0,0,0,0.08);">
    <!-- Clinic & Doctor Header -->
    <div style="text-align: center; border-bottom: 2px solid #0f766e; padding-bottom: 20px; margin-bottom: 24px;">
        <h2 style="font-size: 22px; font-weight: 800; color: #0f766e; text-transform: uppercase; letter-spacing: 0.5px;">BrightSmile Dental & Oral Health Center</h2>
        <div style="font-size: 13px; color: #475569; margin-top: 4px;">Unit 402 Medical Arts Tower, Bonifacio Global City, Taguig, Philippines</div>
        <div style="font-size: 12px; color: #64748b;">Tel: +63 917 123 4567 / (02) 8888-9999 &bull; Email: contact@brightsmiledental.ph</div>

        <div style="margin-top: 14px; padding-top: 10px; border-top: 1px dashed #e2e8f0; font-size: 14px; color: #0f172a;">
            <strong>Dr. {{ $prescription->dentist->name }}</strong>
            <span style="color: #64748b; margin-left: 6px;">{{ $prescription->dentist->specialization ?? 'General Dentistry' }}</span>
            @if($prescription->dentist->license_number)
            <div style="font-size: 12px; color: #0f766e; font-weight: 600;">PRC License No: {{ $prescription->dentist->license_number }}</div>
            @endif
        </div>
    </div>

    <!-- Patient Metadata Row -->
    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px; font-size: 13.5px; margin-bottom: 24px; padding-bottom: 14px; border-bottom: 1px solid #e2e8f0;">
        <div>
            <span style="color: #64748b;">Patient:</span> <strong>{{ $prescription->patient->full_name }}</strong>
        </div>
        <div>
            <span style="color: #64748b;">Age/Sex:</span> <strong>{{ $prescription->patient->computed_age }} / {{ $prescription->patient->gender }}</strong>
        </div>
        <div style="text-align: right;">
            <span style="color: #64748b;">Date:</span> <strong>{{ $prescription->prescription_date->format('M d, Y') }}</strong>
        </div>
    </div>

    @if($prescription->notes)
    <div style="font-size: 12.5px; color: #475569; margin-bottom: 20px; font-style: italic;">
        Indication: {{ $prescription->notes }}
    </div>
    @endif

    <!-- Rx Symbol -->
    <div style="font-family: serif; font-size: 44px; font-weight: bold; color: #0f766e; margin-bottom: 16px; line-height: 1;">
        &#8478;
    </div>

    <!-- Medication Items -->
    <div style="margin-left: 24px; min-height: 260px;">
        @foreach($prescription->items as $idx => $item)
        <div style="margin-bottom: 24px; page-break-inside: avoid;">
            <div style="display: flex; justify-content: space-between; align-items: baseline;">
                <div style="font-size: 16px; font-weight: 700; color: #0f172a;">
                    {{ $idx + 1 }}. {{ $item->medication_name }} {{ $item->dosage }}
                </div>
                <div style="font-size: 14px; font-weight: 600; color: #0f766e;">
                    #{{ $item->quantity }}
                </div>
            </div>
            <div style="font-size: 13.5px; color: #334155; margin-top: 4px; padding-left: 18px;">
                Sig: <strong>{{ $item->frequency }}</strong> for <strong>{{ $item->duration }}</strong>.
                @if($item->instructions)
                    <div style="color: #64748b; font-size: 12.5px; margin-top: 2px;">Note: {{ $item->instructions }}</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Doctor Signature & License Line -->
    <div style="display: flex; justify-content: flex-end; margin-top: 50px; padding-top: 20px;">
        <div style="text-align: center; width: 260px;">
            <div style="border-bottom: 1.5px solid #0f172a; height: 45px; margin-bottom: 6px;"></div>
            <div style="font-weight: 700; font-size: 15px; color: #0f172a;">Dr. {{ $prescription->dentist->name }}</div>
            <div style="font-size: 12px; color: #64748b;">Dental Surgeon</div>
            @if($prescription->dentist->license_number)
            <div style="font-size: 12px; color: #0f766e; font-weight: 600;">PRC Reg: {{ $prescription->dentist->license_number }}</div>
            @endif
            <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">PTR No: Available upon request</div>
        </div>
    </div>
</div>
@endsection
