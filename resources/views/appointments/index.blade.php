@extends('layouts.app')

@section('title', 'Appointment Schedule')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Clinic Appointment Schedule</h1>
        <div class="page-subtitle">Manage daily patient appointments, dentist timetables, and statuses</div>
    </div>
    <div>
        <a href="{{ route('appointments.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-calendar-plus"></i>
            <span>Schedule Appointment</span>
        </a>
    </div>
</div>

<!-- Date & Dentist Filter Bar -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px;">
        <form action="{{ route('appointments.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 180px;">
                <label class="form-label" style="font-size: 11.5px; margin-bottom: 4px;">Appointment Date</label>
                <input type="date" name="date" class="form-control" value="{{ $date }}">
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label class="form-label" style="font-size: 11.5px; margin-bottom: 4px;">Attending Dentist</label>
                <select name="dentist_id" class="form-control">
                    <option value="">All Dentists</option>
                    @foreach($dentists as $d)
                        <option value="{{ $d->id }}" {{ $dentistId == $d->id ? 'selected' : '' }}>Dr. {{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 1; min-width: 160px;">
                <label class="form-label" style="font-size: 11.5px; margin-bottom: 4px;">Status</label>
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="scheduled" {{ $status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="waiting" {{ $status === 'waiting' ? 'selected' : '' }}>Waiting in Queue</option>
                    <option value="in_treatment" {{ $status === 'in_treatment' ? 'selected' : '' }}>In Treatment</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="no_show" {{ $status === 'no_show' ? 'selected' : '' }}>No Show</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-secondary">
                    <i class="fa-solid fa-filter"></i> Apply Filter
                </button>
                <a href="{{ route('appointments.index', ['date' => \Carbon\Carbon::today()->toDateString()]) }}" class="btn btn-secondary" title="Today">
                    Today
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Appointments Schedule Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-calendar-day" style="color: var(--primary);"></i>
            <span>Appointments for {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}</span>
        </div>
        <span class="badge badge-primary">{{ $appointments->total() }} Total</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Time Slot</th>
                        <th>Patient</th>
                        <th>Attending Dentist</th>
                        <th>Service / Reason</th>
                        <th>Queue Token</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $apt)
                    <tr>
                        <td>
                            <strong>{{ date('h:i A', strtotime($apt->start_time)) }}</strong>
                            <div style="font-size: 11px; color: var(--text-light);">to {{ date('h:i A', strtotime($apt->end_time)) }}</div>
                        </td>
                        <td>
                            <a href="{{ route('patients.show', $apt->patient) }}" style="font-weight: 700;">
                                {{ $apt->patient->full_name }}
                            </a>
                            <div style="font-size: 11.5px; color: var(--text-muted);">
                                {{ $apt->patient->patient_number }} &bull; {{ $apt->patient->phone }}
                            </div>
                        </td>
                        <td>
                            <div>Dr. {{ $apt->dentist->name }}</div>
                            <div style="font-size: 11px; color: var(--text-light);">{{ $apt->dentist->specialization ?? 'General' }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 500;">{{ $apt->service ? $apt->service->name : ($apt->reason ?? 'Consultation') }}</div>
                            @if($apt->service)
                            <div style="font-size: 11px; color: var(--primary);">₱{{ number_format($apt->service->standard_price, 2) }}</div>
                            @endif
                        </td>
                        <td>
                            @if($apt->queue_number)
                                <span class="queue-token" style="font-size: 14px;">Token #{{ $apt->queue_number }}</span>
                            @else
                                <span style="font-size: 12px; color: var(--text-light);">-</span>
                            @endif
                        </td>
                        <td>
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
                            <span class="badge {{ $badgeMap[$apt->status] ?? 'badge-secondary' }}">
                                {{ ucwords(str_replace('_', ' ', $apt->status)) }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 4px;">
                                @if(in_array($apt->status, ['scheduled', 'confirmed']))
                                <form action="{{ route('queue.check-in', $apt) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm" title="Check-In to Waiting Queue">
                                        <i class="fa-solid fa-check-to-slot"></i> Check In
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('appointments.show', $apt) }}" class="btn btn-secondary btn-sm" title="View Details">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            <i class="fa-regular fa-calendar-xmark" style="font-size: 30px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                            No appointments found for this selected date and criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top: 16px;">
    {{ $appointments->links() }}
</div>
@endsection
