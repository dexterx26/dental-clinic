@extends('layouts.app')

@section('title', 'Edit Patient - ' . $patient->full_name)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Patient: {{ $patient->full_name }}</h1>
        <div class="page-subtitle">{{ $patient->patient_number }} &bull; Registered {{ $patient->registration_date->format('M d, Y') }}</div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('patients.show', $patient) }}" class="btn btn-secondary">
            <i class="fa-solid fa-folder-open"></i> Back to Patient Chart
        </a>
    </div>
</div>

<form action="{{ route('patients.update', $patient) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-id-card" style="color: var(--primary);"></i>
                <span>Personal & Demographic Information</span>
            </div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="first_name">First Name</label>
                        <input type="text" name="first_name" id="first_name" class="form-control" required value="{{ old('first_name', $patient->first_name) }}">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="middle_name">Middle Name</label>
                        <input type="text" name="middle_name" id="middle_name" class="form-control" value="{{ old('middle_name', $patient->middle_name) }}">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="last_name">Last Name</label>
                        <input type="text" name="last_name" id="last_name" class="form-control" required value="{{ old('last_name', $patient->last_name) }}">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="dob">Date of Birth</label>
                        <input type="date" name="dob" id="dob" class="form-control" required value="{{ old('dob', $patient->dob ? $patient->dob->format('Y-m-d') : '') }}">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="gender">Gender</label>
                        <select name="gender" id="gender" class="form-control" required>
                            <option value="Male" {{ old('gender', $patient->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender', $patient->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender', $patient->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="civil_status">Civil Status</label>
                        <select name="civil_status" id="civil_status" class="form-control">
                            <option value="Single" {{ old('civil_status', $patient->civil_status) === 'Single' ? 'selected' : '' }}>Single</option>
                            <option value="Married" {{ old('civil_status', $patient->civil_status) === 'Married' ? 'selected' : '' }}>Married</option>
                            <option value="Widowed" {{ old('civil_status', $patient->civil_status) === 'Widowed' ? 'selected' : '' }}>Widowed</option>
                            <option value="Separated" {{ old('civil_status', $patient->civil_status) === 'Separated' ? 'selected' : '' }}>Separated</option>
                        </select>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="nationality">Nationality</label>
                        <input type="text" name="nationality" id="nationality" class="form-control" value="{{ old('nationality', $patient->nationality) }}">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="occupation">Occupation</label>
                        <input type="text" name="occupation" id="occupation" class="form-control" value="{{ old('occupation', $patient->occupation) }}">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="phone">Mobile / Contact Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control" required value="{{ old('phone', $patient->phone) }}">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $patient->email) }}">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="status">Record Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="active" {{ old('status', $patient->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $patient->status) === 'inactive' ? 'selected' : '' }}>Inactive / Archived</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Residential Address</label>
                <textarea name="address" id="address" class="form-control" rows="2">{{ old('address', $patient->address) }}</textarea>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-phone-volume" style="color: var(--primary);"></i>
                <span>Emergency Contact & Preferences</span>
            </div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="emergency_contact_name">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact_name" id="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $patient->emergency_contact_name) }}">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="emergency_contact_phone">Emergency Contact Phone</label>
                        <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $patient->emergency_contact_phone) }}">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="emergency_contact_relationship">Relationship</label>
                        <input type="text" name="emergency_contact_relationship" id="emergency_contact_relationship" class="form-control" value="{{ old('emergency_contact_relationship', $patient->emergency_contact_relationship) }}">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="preferred_dentist_id">Preferred Attending Dentist</label>
                        <select name="preferred_dentist_id" id="preferred_dentist_id" class="form-control">
                            <option value="">Any Available Dentist</option>
                            @foreach($dentists as $dentist)
                                <option value="{{ $dentist->id }}" {{ old('preferred_dentist_id', $patient->preferred_dentist_id) == $dentist->id ? 'selected' : '' }}>
                                    {{ $dentist->name }} ({{ $dentist->specialization ?? 'General Dentistry' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="referral_source">Referral Source</label>
                        <input type="text" name="referral_source" id="referral_source" class="form-control" value="{{ old('referral_source', $patient->referral_source) }}">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="photo">Update Photo (Optional)</label>
                        <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="notes">General Notes</label>
                <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes', $patient->notes) }}</textarea>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 30px;">
        <a href="{{ route('patients.show', $patient) }}" class="btn btn-secondary btn-lg">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-floppy-disk"></i> Save Profile Changes
        </button>
    </div>
</form>
@endsection
