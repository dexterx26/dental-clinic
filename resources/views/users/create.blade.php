@extends('layouts.app')

@section('title', 'Add Staff Account')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Add Clinic Staff Account</h1>
        <div class="page-subtitle">Create a new user account and assign Role-Based Access Control (RBAC)</div>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Users
    </a>
</div>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-user-plus" style="color: var(--primary);"></i>
                <span>Account Information</span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="name">Full Name</label>
                            <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. Dr. Juan Dela Cruz" value="{{ old('name') }}">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="email">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control" required placeholder="e.g. jdelacruz@dentalclinic.com" value="{{ old('email') }}">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="password">Initial Password</label>
                            <input type="password" name="password" id="password" class="form-control" required minlength="6" value="password123">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="role">User Role</label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="administrator">Administrator (Full System Access)</option>
                                <option value="dentist" selected>Dentist (Assigned Patients, Charting, Rx, Plans)</option>
                                <option value="receptionist">Receptionist (Appointments, Check-In, Queue)</option>
                                <option value="dental_assistant">Dental Assistant (Clinical Assisting, Notes)</option>
                                <option value="cashier">Cashier (Billing, Invoices, Payments, Receipts)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="phone">Contact Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control" placeholder="+63 9XX XXX XXXX" value="{{ old('phone') }}">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="license_number">PRC License Number (Dentists)</label>
                            <input type="text" name="license_number" id="license_number" class="form-control" placeholder="e.g. PRC-0081234" value="{{ old('license_number') }}">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="specialization">Specialization / Department</label>
                            <input type="text" name="specialization" id="specialization" class="form-control" placeholder="e.g. Orthodontics / Oral Surgery" value="{{ old('specialization') }}">
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 16px;">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check"></i> Create Staff Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
