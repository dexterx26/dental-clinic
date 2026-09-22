@extends('layouts.app')

@section('title', 'Patient Directory')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Patient Directory</h1>
        <div class="page-subtitle">Manage patient records, demographics, and clinical charts</div>
    </div>
    <div>
        <a href="{{ route('patients.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-user-plus"></i>
            <span>Register New Patient</span>
        </a>
    </div>
</div>

<!-- Search and Filter Bar -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px;">
        <form action="{{ route('patients.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div style="flex: 2; min-width: 260px;">
                <input type="text" name="search" class="form-control" placeholder="Search by name, patient #, phone, or email..." value="{{ $search }}">
            </div>
            <div style="flex: 1; min-width: 160px;">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-secondary">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if($search || $status)
                <a href="{{ route('patients.index') }}" class="btn btn-secondary" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Patients List Table -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Patient #</th>
                        <th>Name</th>
                        <th>Age / Gender</th>
                        <th>Contact</th>
                        <th>Medical Alerts</th>
                        <th>Preferred Dentist</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                    <tr>
                        <td>
                            <strong style="color: var(--primary);">{{ $patient->patient_number }}</strong>
                            <div style="font-size: 11px; color: var(--text-light);">Reg: {{ $patient->registration_date->format('M d, Y') }}</div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="user-avatar" style="width: 34px; height: 34px; font-size: 13px; background: #0f766e;">
                                    {{ strtoupper(substr($patient->first_name, 0, 1) . substr($patient->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('patients.show', $patient) }}" style="font-weight: 700; font-size: 14px;">
                                        {{ $patient->full_name }}
                                    </a>
                                    <div style="font-size: 11.5px; color: var(--text-muted);">{{ $patient->occupation ?? 'Patient' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $patient->computed_age }} yrs</div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $patient->gender }}</div>
                        </td>
                        <td>
                            <div><i class="fa-solid fa-phone" style="font-size: 11px; color: var(--text-light);"></i> {{ $patient->phone }}</div>
                            @if($patient->email)
                            <div style="font-size: 11.5px; color: var(--text-muted);"><i class="fa-solid fa-envelope" style="font-size: 11px; color: var(--text-light);"></i> {{ $patient->email }}</div>
                            @endif
                        </td>
                        <td>
                            @if($patient->medicalHistory && !empty($patient->medicalHistory->allergies))
                                <span class="badge badge-danger" title="{{ $patient->medicalHistory->allergies }}">
                                    <i class="fa-solid fa-triangle-exclamation"></i> Allergy
                                </span>
                            @endif
                            @if($patient->medicalHistory && !empty($patient->medicalHistory->conditions))
                                <span class="badge badge-warning" title="Has recorded medical conditions">
                                    <i class="fa-solid fa-notes-medical"></i> Medical Alert
                                </span>
                            @endif
                            @if(!$patient->medicalHistory || (empty($patient->medicalHistory->allergies) && empty($patient->medicalHistory->conditions)))
                                <span class="badge badge-success">No Alerts</span>
                            @endif
                        </td>
                        <td>
                            {{ $patient->preferredDentist ? $patient->preferredDentist->name : 'Any Available' }}
                        </td>
                        <td>
                            <span class="badge {{ $patient->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                                {{ ucfirst($patient->status) }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                <a href="{{ route('patients.show', ['patient' => $patient, 'tab' => 'odontogram']) }}" class="btn btn-secondary btn-sm" title="Dental Chart">
                                    <i class="fa-solid fa-tooth"></i> Chart
                                </a>
                                <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" class="btn btn-primary btn-sm" title="Book Appointment">
                                    <i class="fa-solid fa-calendar-plus"></i>
                                </a>
                                <a href="{{ route('patients.show', $patient) }}" class="btn btn-secondary btn-sm" title="View Full Record">
                                    <i class="fa-solid fa-folder-open"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            <i class="fa-solid fa-user-slash" style="font-size: 32px; color: #cbd5e1; margin-bottom: 12px; display: block;"></i>
                            No patients found matching your search.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top: 16px;">
    {{ $patients->links() }}
</div>
@endsection
