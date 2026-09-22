@extends('layouts.app')

@section('title', 'Patient Check-In & Live Queue')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Front Desk Queue Management</h1>
        <div class="page-subtitle">
            Live patient waiting room queue for {{ \Carbon\Carbon::today()->format('l, F j, Y') }}
        </div>
    </div>
    <div>
        <a href="{{ route('appointments.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-calendar-plus"></i> Schedule New Visit
        </a>
    </div>
</div>

<!-- Arriving Patients Ready for Check-In -->
@if($unassignedAppointments->count() > 0)
<div class="card" style="border: 2px dashed #38bdf8; background: #f0f9ff; margin-bottom: 24px;">
    <div class="card-header" style="background: transparent; border-bottom: 1px solid #bae6fd;">
        <div class="card-title" style="color: #0369a1;">
            <i class="fa-solid fa-bell"></i>
            <span>Arriving Scheduled Patients Awaiting Front Desk Check-In ({{ $unassignedAppointments->count() }})</span>
        </div>
    </div>
    <div class="card-body" style="padding: 12px 20px;">
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            @foreach($unassignedAppointments as $unassigned)
            <div style="background: white; border: 1px solid #bae6fd; border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex: 1; min-width: 280px;">
                <div>
                    <div style="font-weight: 700; font-size: 14px;">{{ $unassigned->patient->full_name }}</div>
                    <div style="font-size: 12px; color: var(--text-muted);">
                        Time: <strong>{{ date('h:i A', strtotime($unassigned->start_time)) }}</strong> &bull; Dr. {{ $unassigned->dentist->name }}
                    </div>
                </div>
                <form action="{{ route('queue.check-in', $unassigned) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-check-to-slot"></i> Check In
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Live Queue Board -->
<div class="queue-board">
    <!-- Column 1: Waiting in Reception -->
    <div class="queue-column" style="border-top: 4px solid var(--warning);">
        <div class="queue-col-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-chair" style="color: var(--warning);"></i>
                <span>1. Waiting Room</span>
            </div>
            <span class="badge badge-warning">{{ $queue->where('status', 'waiting')->count() }}</span>
        </div>

        @forelse($queue->where('status', 'waiting') as $item)
        <div class="queue-item">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span class="queue-token">Token #{{ $item->queue_number }}</span>
                <span style="font-size: 11px; color: var(--text-light);">
                    In: {{ $item->checked_in_at ? $item->checked_in_at->format('h:i A') : 'N/A' }}
                </span>
            </div>
            <div style="font-weight: 700; font-size: 14px; margin-top: 4px;">
                <a href="{{ route('patients.show', $item->patient) }}">{{ $item->patient->full_name }}</a>
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">
                Dr. {{ $item->dentist->name }} &bull; {{ $item->service ? $item->service->name : 'Consultation' }}
            </div>
            <div style="display: flex; gap: 6px; margin-top: 8px;">
                <form action="{{ route('appointments.update-status', $item) }}" method="POST" style="flex: 1;">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="in_consultation">
                    <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%;">
                        Call Patient
                    </button>
                </form>
                <form action="{{ route('appointments.update-status', $item) }}" method="POST" style="flex: 1;">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="in_treatment">
                    <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">
                        To Chair
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 13px;">
            No patients currently waiting.
        </div>
        @endforelse
    </div>

    <!-- Column 2: In Consultation / In Treatment -->
    <div class="queue-column" style="border-top: 4px solid var(--primary);">
        <div class="queue-col-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-stethoscope" style="color: var(--primary);"></i>
                <span>2. In Dental Chair / Treatment</span>
            </div>
            <span class="badge badge-primary">{{ $queue->whereIn('status', ['in_consultation', 'in_treatment'])->count() }}</span>
        </div>

        @forelse($queue->whereIn('status', ['in_consultation', 'in_treatment']) as $item)
        <div class="queue-item" style="border-color: var(--primary-border); background: #f0fdfa;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span class="queue-token">Token #{{ $item->queue_number }}</span>
                <span class="badge badge-primary">{{ ucwords(str_replace('_', ' ', $item->status)) }}</span>
            </div>
            <div style="font-weight: 700; font-size: 14px; margin-top: 4px;">
                <a href="{{ route('patients.show', $item->patient) }}">{{ $item->patient->full_name }}</a>
            </div>
            <div style="font-size: 12px; color: var(--text-muted);">
                Dr. {{ $item->dentist->name }} &bull; {{ $item->service ? $item->service->name : 'Procedure' }}
            </div>
            <div style="display: flex; gap: 6px; margin-top: 8px;">
                <a href="{{ route('patients.show', ['patient' => $item->patient, 'tab' => 'odontogram']) }}" class="btn btn-secondary btn-sm" style="flex: 1;">
                    <i class="fa-solid fa-tooth"></i> Chart
                </a>
                <form action="{{ route('appointments.update-status', $item) }}" method="POST" style="flex: 1;">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn btn-success btn-sm" style="width: 100%;">
                        Complete
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 13px;">
            No patients currently in treatment chairs.
        </div>
        @endforelse
    </div>

    <!-- Column 3: Completed Visits Today -->
    <div class="queue-column" style="border-top: 4px solid var(--success);">
        <div class="queue-col-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-check" style="color: var(--success);"></i>
                <span>3. Completed Today</span>
            </div>
            <span class="badge badge-success">{{ $queue->where('status', 'completed')->count() }}</span>
        </div>

        @forelse($queue->where('status', 'completed') as $item)
        <div class="queue-item" style="opacity: 0.9;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 12px; font-weight: 700; color: var(--text-muted);">Token #{{ $item->queue_number }}</span>
                <span class="badge badge-success">Finished</span>
            </div>
            <div style="font-weight: 600; font-size: 13.5px; margin-top: 2px;">
                <a href="{{ route('patients.show', $item->patient) }}">{{ $item->patient->full_name }}</a>
            </div>
            <div style="font-size: 11.5px; color: var(--text-muted);">
                Dr. {{ $item->dentist->name }}
            </div>
            <div style="margin-top: 6px; text-align: right;">
                <a href="{{ route('invoices.create', ['patient' => $item->patient, 'appointment_id' => $item->id]) }}" class="btn btn-secondary btn-sm" style="font-size: 11px;">
                    <i class="fa-solid fa-receipt"></i> Bill / Receipt
                </a>
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 13px;">
            No visits completed yet today.
        </div>
        @endforelse
    </div>
</div>
@endsection
