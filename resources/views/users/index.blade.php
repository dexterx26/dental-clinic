@extends('layouts.app')

@section('title', 'Staff & User Management')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Clinic Staff & User Management</h1>
        <div class="page-subtitle">Manage user accounts, roles, access permissions, and dentist timetables</div>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-user-plus"></i> Add Staff Account
    </a>
</div>

<!-- Role Filters -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px;">
        <form action="{{ route('users.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center;">
            <label class="form-label" style="margin-bottom: 0;">Filter by Role:</label>
            <select name="role" class="form-control" style="max-width: 220px;" onchange="this.form.submit();">
                <option value="">All Roles</option>
                <option value="administrator" {{ $role === 'administrator' ? 'selected' : '' }}>Administrator</option>
                <option value="dentist" {{ $role === 'dentist' ? 'selected' : '' }}>Dentist</option>
                <option value="receptionist" {{ $role === 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                <option value="dental_assistant" {{ $role === 'dental_assistant' ? 'selected' : '' }}>Dental Assistant</option>
                <option value="cashier" {{ $role === 'cashier' ? 'selected' : '' }}>Cashier</option>
            </select>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th>Role</th>
                        <th>Email & Contact</th>
                        <th>License / Specialization</th>
                        <th>Account Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="user-avatar" style="background: #0f766e; color: white;">
                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 700;">{{ $u->name }}</div>
                                    <div style="font-size: 11px; color: var(--text-light);">Added: {{ $u->created_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $roleBadge = [
                                    'administrator' => 'badge-primary',
                                    'dentist' => 'badge-info',
                                    'receptionist' => 'badge-success',
                                    'cashier' => 'badge-warning',
                                    'dental_assistant' => 'badge-secondary',
                                ];
                            @endphp
                            <span class="badge {{ $roleBadge[$u->role] ?? 'badge-secondary' }}">
                                {{ ucwords(str_replace('_', ' ', $u->role)) }}
                            </span>
                        </td>
                        <td>
                            <div>{{ $u->email }}</div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $u->phone ?? 'No phone' }}</div>
                        </td>
                        <td>
                            @if($u->license_number)
                                <div style="font-weight: 600; color: var(--primary);">{{ $u->license_number }}</div>
                            @endif
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $u->specialization ?? '-' }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $u->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $u->is_active ? 'Active' : 'Deactivated' }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('users.edit', $u) }}" class="btn btn-secondary btn-sm">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            No user accounts found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
