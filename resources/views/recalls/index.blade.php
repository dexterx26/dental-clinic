@extends('layouts.app')

@section('title', 'Follow-Up & Recalls')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Follow-Up & Preventive Recalls</h1>
        <div class="page-subtitle">Track six-month cleanings, annual dental check-ups, and post-procedure follow-ups</div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px;">
        <form action="{{ route('recalls.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 160px;">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="contacted" {{ $status === 'contacted' ? 'selected' : '' }}>Patient Contacted</option>
                    <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Confirmed / Booked</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 180px;">
                <select name="due" class="form-control">
                    <option value="">All Due Dates</option>
                    <option value="this_week" {{ $due === 'this_week' ? 'selected' : '' }}>Due This Week</option>
                    <option value="overdue" {{ $due === 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 180px;">
                <select name="type" class="form-control">
                    <option value="">All Recall Types</option>
                    <option value="routine_recall_6mo" {{ $type === 'routine_recall_6mo' ? 'selected' : '' }}>Routine 6-Month Prophylaxis</option>
                    <option value="annual_checkup" {{ $type === 'annual_checkup' ? 'selected' : '' }}>Annual Checkup</option>
                    <option value="treatment_check" {{ $type === 'treatment_check' ? 'selected' : '' }}>Treatment Check</option>
                    <option value="orthodontic_adjustment" {{ $type === 'orthodontic_adjustment' ? 'selected' : '' }}>Orthodontic Adjustment</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-secondary">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Recalls Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i>
            <span>Scheduled Recalls List</span>
        </div>
        <span class="badge badge-primary">{{ $recalls->total() }} Records</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Due Date</th>
                        <th>Patient Name</th>
                        <th>Contact</th>
                        <th>Recall Type</th>
                        <th>Assigned Dentist</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recalls as $recall)
                    <tr>
                        <td>
                            @php
                                $isOverdue = ($recall->status === 'pending' && $recall->scheduled_date < \Carbon\Carbon::today());
                            @endphp
                            <strong style="color: {{ $isOverdue ? '#b91c1c' : 'var(--text-main)' }};">
                                {{ $recall->scheduled_date->format('M d, Y') }}
                            </strong>
                            @if($isOverdue)
                                <div style="font-size: 11px; color: #b91c1c; font-weight: 700;">OVERDUE</div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('patients.show', $recall->patient) }}" style="font-weight: 700;">
                                {{ $recall->patient->full_name }}
                            </a>
                            <div style="font-size: 11px; color: var(--text-light);">{{ $recall->patient->patient_number }}</div>
                        </td>
                        <td>{{ $recall->patient->phone }}</td>
                        <td>
                            <span class="badge badge-secondary">{{ ucwords(str_replace('_', ' ', $recall->follow_up_type)) }}</span>
                        </td>
                        <td>{{ $recall->dentist ? 'Dr. ' . $recall->dentist->name : 'Clinic Staff' }}</td>
                        <td>
                            @php
                                $rBadge = [
                                    'pending' => 'badge-warning',
                                    'contacted' => 'badge-info',
                                    'confirmed' => 'badge-primary',
                                    'completed' => 'badge-success',
                                    'cancelled' => 'badge-danger',
                                ];
                            @endphp
                            <span class="badge {{ $rBadge[$recall->status] ?? 'badge-secondary' }}">{{ ucfirst($recall->status) }}</span>
                        </td>
                        <td>{{ $recall->notes ?? '-' }}</td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 4px;">
                                @if($recall->status === 'pending')
                                <form action="{{ route('recalls.update-status', $recall) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="contacted">
                                    <button type="submit" class="btn btn-secondary btn-sm" title="Mark Patient Contacted">
                                        <i class="fa-solid fa-phone"></i> Contacted
                                    </button>
                                </form>
                                @endif

                                @if($recall->status !== 'completed')
                                <form action="{{ route('recalls.update-status', $recall) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="btn btn-success btn-sm" title="Mark Completed">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                                <a href="{{ route('appointments.create', ['patient_id' => $recall->patient->id]) }}" class="btn btn-primary btn-sm" title="Book Appointment">
                                    <i class="fa-solid fa-calendar-plus"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            No recall records found matching criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top: 16px;">
    {{ $recalls->links() }}
</div>
@endsection
