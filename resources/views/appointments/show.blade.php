@extends('layouts.app')

@section('title', 'Appointment ' . $appointment->appointment_number)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Appointment: {{ $appointment->appointment_number }}</h1>
        <div class="page-subtitle">
            Scheduled for {{ $appointment->appointment_date->format('l, F j, Y') }} ({{ date('h:i A', strtotime($appointment->start_time)) }} - {{ date('h:i A', strtotime($appointment->end_time)) }})
        </div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('appointments.index', ['date' => $appointment->appointment_date->toDateString()]) }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back to Schedule
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <div>
        <!-- Appointment Summary Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-calendar-check" style="color: var(--primary);"></i>
                    <span>Visit Details</span>
                </div>
                @php
                    $badgeMap = [
                        'scheduled' => 'badge-secondary',
                        'confirmed' => 'badge-info',
                        'waiting' => 'badge-warning',
                        'in_treatment' => 'badge-primary',
                        'completed' => 'badge-success',
                        'cancelled' => 'badge-danger',
                        'no_show' => 'badge-danger',
                    ];
                @endphp
                <span class="badge {{ $badgeMap[$appointment->status] ?? 'badge-secondary' }}" style="font-size: 13px; padding: 4px 12px;">
                    {{ ucwords(str_replace('_', ' ', $appointment->status)) }}
                </span>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted);">Attending Dentist</div>
                        <div style="font-size: 15px; font-weight: 700; color: var(--text-main); margin-top: 2px;">
                            Dr. {{ $appointment->dentist->name }}
                        </div>
                        <div style="font-size: 12px; color: var(--text-light);">{{ $appointment->dentist->specialization ?? 'General Dentistry' }}</div>
                    </div>

                    <div>
                        <div style="font-size: 12px; color: var(--text-muted);">Service Requested</div>
                        <div style="font-size: 15px; font-weight: 700; color: var(--text-main); margin-top: 2px;">
                            {{ $appointment->service ? $appointment->service->name : 'General Consultation' }}
                        </div>
                        @if($appointment->service)
                        <div style="font-size: 12px; color: var(--primary); font-weight: 600;">
                            Standard Fee: ₱{{ number_format($appointment->service->standard_price, 2) }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Chief Complaint / Reason</label>
                    <div style="background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid var(--border);">
                        {{ $appointment->reason ?? 'No specific reason entered.' }}
                    </div>
                </div>

                @if($appointment->notes)
                <div class="form-group">
                    <label class="form-label">Clinical / Scheduling Notes</label>
                    <div style="background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid var(--border); white-space: pre-line;">
                        {{ $appointment->notes }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Clinical Encounter Actions -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-stethoscope" style="color: var(--primary);"></i>
                    <span>Clinical Actions for This Appointment</span>
                </div>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                    <a href="{{ route('patients.show', ['patient' => $appointment->patient, 'tab' => 'odontogram']) }}" class="btn btn-secondary" style="padding: 14px; display: flex; flex-direction: column; gap: 4px; text-align: center;">
                        <i class="fa-solid fa-tooth" style="font-size: 22px; color: var(--primary);"></i>
                        <span style="font-weight: 700;">Interactive Chart</span>
                        <span style="font-size: 11px; color: var(--text-muted);">View / update odontogram</span>
                    </a>

                    <a href="{{ route('examinations.create', ['patient' => $appointment->patient, 'appointment_id' => $appointment->id]) }}" class="btn btn-secondary" style="padding: 14px; display: flex; flex-direction: column; gap: 4px; text-align: center;">
                        <i class="fa-solid fa-clipboard-check" style="font-size: 22px; color: #0284c7;"></i>
                        <span style="font-weight: 700;">Record Exam</span>
                        <span style="font-size: 11px; color: var(--text-muted);">Findings & oral diagnosis</span>
                    </a>

                    <a href="{{ route('treatments.create', ['patient' => $appointment->patient, 'appointment_id' => $appointment->id]) }}" class="btn btn-secondary" style="padding: 14px; display: flex; flex-direction: column; gap: 4px; text-align: center;">
                        <i class="fa-solid fa-hand-holding-medical" style="font-size: 22px; color: #10b981;"></i>
                        <span style="font-weight: 700;">Record Procedure</span>
                        <span style="font-size: 11px; color: var(--text-muted);">Notes & materials used</span>
                    </a>

                    <a href="{{ route('prescriptions.create', ['patient' => $appointment->patient, 'appointment_id' => $appointment->id]) }}" class="btn btn-secondary" style="padding: 14px; display: flex; flex-direction: column; gap: 4px; text-align: center;">
                        <i class="fa-solid fa-prescription" style="font-size: 22px; color: #8b5cf6;"></i>
                        <span style="font-weight: 700;">Issue Prescription</span>
                        <span style="font-size: 11px; color: var(--text-muted);">Write medications Rx</span>
                    </a>

                    <a href="{{ route('invoices.create', ['patient' => $appointment->patient, 'appointment_id' => $appointment->id]) }}" class="btn btn-secondary" style="padding: 14px; display: flex; flex-direction: column; gap: 4px; text-align: center;">
                        <i class="fa-solid fa-file-invoice-dollar" style="font-size: 22px; color: #f59e0b;"></i>
                        <span style="font-weight: 700;">Generate Invoice</span>
                        <span style="font-size: 11px; color: var(--text-muted);">Billing & receipt</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Patient Snapshot & Status Transitions -->
    <div>
        <!-- Patient Summary -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-user" style="color: var(--primary);"></i>
                    <span>Patient Profile</span>
                </div>
            </div>
            <div class="card-body">
                <div style="font-weight: 700; font-size: 16px;">
                    <a href="{{ route('patients.show', $appointment->patient) }}">{{ $appointment->patient->full_name }}</a>
                </div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                    {{ $appointment->patient->patient_number }} &bull; {{ $appointment->patient->computed_age }} yrs ({{ $appointment->patient->gender }})
                </div>
                <div style="margin-top: 10px; font-size: 13px;">
                    <div><i class="fa-solid fa-phone" style="width: 16px; color: var(--text-light);"></i> {{ $appointment->patient->phone }}</div>
                    @if($appointment->patient->email)
                    <div style="margin-top: 4px;"><i class="fa-solid fa-envelope" style="width: 16px; color: var(--text-light);"></i> {{ $appointment->patient->email }}</div>
                    @endif
                </div>

                @if($appointment->patient->medicalHistory && !empty($appointment->patient->medicalHistory->allergies))
                <div style="margin-top: 12px; padding: 8px 12px; background: #fee2e2; border-radius: 6px; color: #b91c1c; font-size: 12px; font-weight: 600;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Allergy: {{ $appointment->patient->medicalHistory->allergies }}
                </div>
                @endif
            </div>
        </div>

        <!-- Status Transition Controller -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-arrows-rotate" style="color: var(--primary);"></i>
                    <span>Update Status</span>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('appointments.update-status', $appointment) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label class="form-label" for="status">Appointment Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="scheduled" {{ $appointment->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="confirmed" {{ $appointment->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="checked_in" {{ $appointment->status === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                            <option value="waiting" {{ $appointment->status === 'waiting' ? 'selected' : '' }}>Waiting in Reception</option>
                            <option value="in_consultation" {{ $appointment->status === 'in_consultation' ? 'selected' : '' }}>In Consultation</option>
                            <option value="in_treatment" {{ $appointment->status === 'in_treatment' ? 'selected' : '' }}>In Treatment</option>
                            <option value="completed" {{ $appointment->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $appointment->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="no_show" {{ $appointment->status === 'no_show' ? 'selected' : '' }}>No Show</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="notes">Status Update Note</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fa-solid fa-floppy-disk"></i> Update Status
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
