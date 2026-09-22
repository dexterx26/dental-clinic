@extends('layouts.app')

@section('title', 'Edit Staff Member - ' . $user->name)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Staff Account: {{ $user->name }}</h1>
        <div class="page-subtitle">Role: <strong>{{ ucwords(str_replace('_', ' ', $user->role)) }}</strong> &bull; Member since {{ $user->created_at->format('M d, Y') }}</div>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Users
    </a>
</div>

<form action="{{ route('users.update', $user) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-user-pen" style="color: var(--primary);"></i>
                <span>Account Information</span>
            </div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="name">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control" required value="{{ old('name', $user->name) }}">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="email">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" required value="{{ old('email', $user->email) }}">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="password">New Password (leave blank to keep current)</label>
                        <input type="password" name="password" id="password" class="form-control" minlength="6" placeholder="Leave blank to keep current password">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="role">User Role</label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="administrator" {{ $user->role === 'administrator' ? 'selected' : '' }}>Administrator</option>
                            <option value="dentist" {{ $user->role === 'dentist' ? 'selected' : '' }}>Dentist</option>
                            <option value="receptionist" {{ $user->role === 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                            <option value="dental_assistant" {{ $user->role === 'dental_assistant' ? 'selected' : '' }}>Dental Assistant</option>
                            <option value="cashier" {{ $user->role === 'cashier' ? 'selected' : '' }}>Cashier</option>
                        </select>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="is_active">Account Status</label>
                        <select name="is_active" id="is_active" class="form-control" required>
                            <option value="1" {{ $user->is_active ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Deactivated</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="license_number">PRC License Number</label>
                        <input type="text" name="license_number" id="license_number" class="form-control" value="{{ old('license_number', $user->license_number) }}">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="specialization">Specialization</label>
                        <input type="text" name="specialization" id="specialization" class="form-control" value="{{ old('specialization', $user->specialization) }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dentist Weekly Working Schedule (Section 28) -->
    @if($user->role === 'dentist')
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-clock" style="color: var(--primary);"></i>
                <span>Weekly Working Schedule (Prevents Double-Booking & Enforces Availability)</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Day of Week</th>
                            <th style="width: 15%;">Available?</th>
                            <th style="width: 20%;">Shift Start</th>
                            <th style="width: 20%;">Shift End</th>
                            <th style="width: 25%;">Lunch Break</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $days = [
                                1 => 'Monday',
                                2 => 'Tuesday',
                                3 => 'Wednesday',
                                4 => 'Thursday',
                                5 => 'Friday',
                                6 => 'Saturday',
                                0 => 'Sunday',
                            ];
                            $schedMap = $user->schedules->keyBy('day_of_week');
                        @endphp
                        @foreach($days as $dayNum => $dayName)
                        @php
                            $sched = $schedMap[$dayNum] ?? null;
                            $isAvail = $sched ? $sched->is_available : ($dayNum !== 0);
                        @endphp
                        <tr>
                            <td><strong>{{ $dayName }}</strong></td>
                            <td>
                                <div class="form-check" style="margin: 0;">
                                    <input type="checkbox" name="schedules[{{ $dayNum }}][is_available]" value="1" {{ $isAvail ? 'checked' : '' }}>
                                    <label style="font-size: 13px;">Working</label>
                                </div>
                            </td>
                            <td>
                                <input type="time" name="schedules[{{ $dayNum }}][start_time]" class="form-control" value="{{ $sched ? date('H:i', strtotime($sched->start_time)) : '08:30' }}">
                            </td>
                            <td>
                                <input type="time" name="schedules[{{ $dayNum }}][end_time]" class="form-control" value="{{ $sched ? date('H:i', strtotime($sched->end_time)) : '17:30' }}">
                            </td>
                            <td>
                                <div style="display: flex; gap: 4px; align-items: center;">
                                    <input type="time" name="schedules[{{ $dayNum }}][break_start]" class="form-control" style="width: 110px;" value="{{ $sched && $sched->break_start ? date('H:i', strtotime($sched->break_start)) : '12:00' }}">
                                    <span>to</span>
                                    <input type="time" name="schedules[{{ $dayNum }}][break_end]" class="form-control" style="width: 110px;" value="{{ $sched && $sched->break_end ? date('H:i', strtotime($sched->break_end)) : '13:00' }}">
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 30px;">
        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-floppy-disk"></i> Save Staff Profile & Schedule
        </button>
    </div>
</form>
@endsection
