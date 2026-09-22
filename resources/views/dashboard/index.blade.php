@extends('layouts.app')

@section('title', 'Clinic Overview Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Welcome back, {{ auth()->user()->name }}</h1>
        <div class="page-subtitle">
            {{ \Carbon\Carbon::now()->format('l, F j, Y') }} &bull; Role: <strong>{{ ucwords(str_replace('_', ' ', auth()->user()->role)) }}</strong>
        </div>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('patients.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-user-plus"></i>
            <span>Register Patient</span>
        </a>
        <a href="{{ route('queue.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-users-line"></i>
            <span>Live Queue ({{ $waitingCount + $inTreatmentCount }})</span>
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fa-solid fa-calendar-day"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Today's Appointments</div>
            <div class="stat-value">{{ $todayAppointments->count() }}</div>
            <div class="stat-sub">{{ $completedTodayCount }} completed, {{ $inTreatmentCount }} in chair</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fa-solid fa-user-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Waiting in Queue</div>
            <div class="stat-value">{{ $waitingCount }}</div>
            <div class="stat-sub">Reception waiting area</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary">
            <i class="fa-solid fa-hospital-user"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Patients</div>
            <div class="stat-value">{{ $totalPatients }}</div>
            <div class="stat-sub">+{{ $newPatientsThisMonth }} registered this month</div>
        </div>
    </div>

    @if(auth()->user()->hasRole(['administrator', 'cashier']))
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fa-solid fa-peso-sign"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Collections Today</div>
            <div class="stat-value">₱{{ number_format($todayRevenue, 2) }}</div>
            <div class="stat-sub">Month: ₱{{ number_format($monthRevenue, 2) }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon danger">
            <i class="fa-solid fa-file-invoice"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Receivables</div>
            <div class="stat-value">₱{{ number_format($totalOutstanding, 2) }}</div>
            <div class="stat-sub">Outstanding balance</div>
        </div>
    </div>
    @endif
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
    <!-- Left Column: Appointments & Queue -->
    <div>
        <!-- Today's Schedule Table -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-calendar-check" style="color: var(--primary);"></i>
                    <span>Today's Clinical Schedule</span>
                </div>
                <a href="{{ route('appointments.index') }}" class="btn btn-secondary btn-sm">View Calendar</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Patient</th>
                                <th>Dentist</th>
                                <th>Service / Reason</th>
                                <th>Status</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($todayAppointments as $apt)
                            <tr>
                                <td>
                                    <strong>{{ date('h:i A', strtotime($apt->start_time)) }}</strong>
                                    <div style="font-size: 11px; color: var(--text-light);">{{ date('h:i A', strtotime($apt->end_time)) }}</div>
                                </td>
                                <td>
                                    <a href="{{ route('patients.show', $apt->patient) }}" style="font-weight: 600;">
                                        {{ $apt->patient->full_name }}
                                    </a>
                                    <div style="font-size: 11px; color: var(--text-light);">{{ $apt->patient->patient_number }}</div>
                                </td>
                                <td>{{ $apt->dentist->name }}</td>
                                <td>
                                    <div>{{ $apt->service ? $apt->service->name : ($apt->reason ?? 'Consultation') }}</div>
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
                                        <a href="{{ route('patients.show', ['patient' => $apt->patient, 'tab' => 'odontogram']) }}" class="btn btn-secondary btn-sm" title="Dental Chart">
                                            <i class="fa-solid fa-tooth"></i>
                                        </a>
                                        @if($apt->status === 'scheduled' || $apt->status === 'confirmed')
                                        <form action="{{ route('queue.check-in', $apt) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm" title="Check-In to Queue">
                                                <i class="fa-solid fa-check-to-slot"></i>
                                            </button>
                                        </form>
                                        @elseif($apt->status === 'waiting')
                                        <form action="{{ route('appointments.update-status', $apt) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="in_treatment">
                                            <button type="submit" class="btn btn-success btn-sm" title="Start Treatment">
                                                <i class="fa-solid fa-stethoscope"></i>
                                            </button>
                                        </form>
                                        @elseif($apt->status === 'in_treatment')
                                        <form action="{{ route('appointments.update-status', $apt) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn btn-secondary btn-sm" title="Mark Completed">
                                                <i class="fa-solid fa-flag-checkered"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                    <i class="fa-regular fa-calendar-xmark" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
                                    No appointments scheduled for today.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pending Treatment Plans -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
                    <span>Pending Treatment Plans Awaiting Decision</span>
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Plan #</th>
                                <th>Patient</th>
                                <th>Plan Title</th>
                                <th>Estimated Cost</th>
                                <th>Status</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingPlans as $plan)
                            <tr>
                                <td><strong>{{ $plan->plan_number }}</strong></td>
                                <td>
                                    <a href="{{ route('patients.show', $plan->patient) }}">{{ $plan->patient->full_name }}</a>
                                </td>
                                <td>{{ $plan->title }}</td>
                                <td><strong>₱{{ number_format($plan->total_estimated_cost, 2) }}</strong></td>
                                <td><span class="badge badge-warning">{{ ucfirst($plan->status) }}</span></td>
                                <td style="text-align: right;">
                                    <a href="{{ route('treatment-plans.show', $plan) }}" class="btn btn-secondary btn-sm">Review</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 20px; color: var(--text-muted);">
                                    No pending treatment plans.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Queue & Recalls -->
    <div>
        <!-- Live Waiting Queue -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-users-line" style="color: var(--primary);"></i>
                    <span>Waiting Room Queue</span>
                </div>
                <span class="badge badge-primary">{{ $queueAppointments->count() }} in clinic</span>
            </div>
            <div class="card-body">
                @forelse($queueAppointments as $qItem)
                <div class="queue-item" style="border-left: 4px solid {{ $qItem->status === 'in_treatment' ? 'var(--primary)' : 'var(--warning)' }};">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="queue-token">Token #{{ $qItem->queue_number }}</span>
                        <span class="badge {{ $qItem->status === 'in_treatment' ? 'badge-primary' : 'badge-warning' }}">
                            {{ ucwords(str_replace('_', ' ', $qItem->status)) }}
                        </span>
                    </div>
                    <div style="font-weight: 600; font-size: 14px; margin-top: 4px;">
                        <a href="{{ route('patients.show', $qItem->patient) }}">{{ $qItem->patient->full_name }}</a>
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted);">
                        <i class="fa-solid fa-user-doctor"></i> Dr. {{ $qItem->dentist->name }}
                    </div>
                    <div style="font-size: 11.5px; color: var(--text-light); display: flex; justify-content: space-between; margin-top: 6px;">
                        <span>In: {{ $qItem->checked_in_at ? $qItem->checked_in_at->format('h:i A') : 'N/A' }}</span>
                        <a href="{{ route('patients.show', ['patient' => $qItem->patient, 'tab' => 'odontogram']) }}" style="font-weight: 600;">
                            Open Chart &rarr;
                        </a>
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 24px; color: var(--text-muted);">
                    <i class="fa-solid fa-couch" style="font-size: 26px; margin-bottom: 8px; display: block; color: #cbd5e1;"></i>
                    Waiting room is currently clear.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Due Recalls & Follow-ups -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i>
                    <span>Recalls Due This Week</span>
                </div>
                <a href="{{ route('recalls.index') }}" class="btn btn-secondary btn-sm">All</a>
            </div>
            <div class="card-body">
                @forelse($dueRecalls as $rec)
                <div style="padding: 10px 0; border-bottom: 1px solid var(--border-light); font-size: 13px;">
                    <div style="display: flex; justify-content: space-between; font-weight: 600;">
                        <a href="{{ route('patients.show', $rec->patient) }}">{{ $rec->patient->full_name }}</a>
                        <span style="color: var(--primary);">{{ date('M d', strtotime($rec->scheduled_date)) }}</span>
                    </div>
                    <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">
                        {{ ucwords(str_replace('_', ' ', $rec->follow_up_type)) }} &bull; {{ $rec->patient->phone }}
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 16px; color: var(--text-muted); font-size: 12.5px;">
                    No pending recalls due this week.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Audit Trail Feed (For Administrators) -->
        @if(auth()->user()->isAdmin())
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i>
                    <span>System Audit Trail</span>
                </div>
                <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary btn-sm">Full Log</a>
            </div>
            <div class="card-body">
                @foreach($recentAudits as $log)
                <div style="padding: 8px 0; border-bottom: 1px solid var(--border-light); font-size: 12px;">
                    <div style="display: flex; justify-content: space-between; color: var(--text-muted);">
                        <span><strong>{{ $log->user ? $log->user->name : 'System' }}</strong></span>
                        <span>{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                    <div style="color: var(--text-main); margin-top: 2px;">
                        {{ $log->description }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
