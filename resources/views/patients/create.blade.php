@extends('layouts.app')

@section('title', 'Register New Patient')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">New Patient Registration</h1>
        <div class="page-subtitle">Onboard patient and record baseline demographic data</div>
    </div>
    <a href="{{ route('patients.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Directory
    </a>
</div>

<form action="{{ route('patients.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

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
                        <input type="text" name="first_name" id="first_name" class="form-control" required value="{{ old('first_name') }}" placeholder="e.g. Juan">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="middle_name">Middle Name</label>
                        <input type="text" name="middle_name" id="middle_name" class="form-control" value="{{ old('middle_name') }}" placeholder="e.g. Protacio">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="last_name">Last Name</label>
                        <input type="text" name="last_name" id="last_name" class="form-control" required value="{{ old('last_name') }}" placeholder="e.g. Dela Cruz">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="dob">Date of Birth</label>
                        <input type="date" name="dob" id="dob" class="form-control" required value="{{ old('dob') }}">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="gender">Gender</label>
                        <select name="gender" id="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="civil_status">Civil Status</label>
                        <select name="civil_status" id="civil_status" class="form-control">
                            <option value="Single" {{ old('civil_status') === 'Single' ? 'selected' : '' }}>Single</option>
                            <option value="Married" {{ old('civil_status') === 'Married' ? 'selected' : '' }}>Married</option>
                            <option value="Widowed" {{ old('civil_status') === 'Widowed' ? 'selected' : '' }}>Widowed</option>
                            <option value="Separated" {{ old('civil_status') === 'Separated' ? 'selected' : '' }}>Separated</option>
                        </select>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="nationality">Nationality</label>
                        <input type="text" name="nationality" id="nationality" class="form-control" value="{{ old('nationality', 'Filipino') }}">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="occupation">Occupation</label>
                        <input type="text" name="occupation" id="occupation" class="form-control" value="{{ old('occupation') }}" placeholder="e.g. Account Executive">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label required" for="phone">Mobile / Contact Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control" required value="{{ old('phone') }}" placeholder="+63 9XX XXX XXXX">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="patient@example.com">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Residential Address</label>
                <textarea name="address" id="address" class="form-control" rows="2" placeholder="Street, Barangay, City / Municipality, Province">{{ old('address') }}</textarea>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-phone-volume" style="color: var(--primary);"></i>
                <span>Emergency Contact & Clinical Preferences</span>
            </div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="emergency_contact_name">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact_name" id="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name') }}">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="emergency_contact_phone">Emergency Contact Phone</label>
                        <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone') }}">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="emergency_contact_relationship">Relationship</label>
                        <input type="text" name="emergency_contact_relationship" id="emergency_contact_relationship" class="form-control" placeholder="e.g. Spouse, Parent, Sibling" value="{{ old('emergency_contact_relationship') }}">
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
                                <option value="{{ $dentist->id }}" {{ old('preferred_dentist_id') == $dentist->id ? 'selected' : '' }}>
                                    {{ $dentist->name }} ({{ $dentist->specialization ?? 'General Dentistry' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="referral_source">Referral Source</label>
                        <input type="text" name="referral_source" id="referral_source" class="form-control" placeholder="e.g. Google Search, Friend, Walk-in" value="{{ old('referral_source') }}">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="photo">Patient Photo (Optional)</label>
                        <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="notes">General Intake Notes</label>
                <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Special considerations, dental anxiety, language preferences...">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>

    <!-- Republic Act 10173 Compliance Consent Card -->
    <div class="card" style="border: 2px solid #bbf7d0; background-color: #f0fdf4;">
        <div class="card-header" style="background-color: #dcfce7; border-bottom: 1px solid #bbf7d0;">
            <div class="card-title" style="color: #166534;">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Data Privacy Act of 2012 (RA 10173) Consent</span>
            </div>
        </div>
        <div class="card-body">
            <p style="font-size: 13px; color: #166534; line-height: 1.6; margin-bottom: 12px;">
                In compliance with the Data Privacy Act of 2012 (Republic Act No. 10173), I hereby authorize BrightSmile Dental Clinic to collect, process, and securely store personal, sensitive, and medical/dental health records for the purpose of dental examination, diagnosis, treatment planning, and clinic management. The patient retains the right to access, dispute, and request correction of their personal data.
            </p>
            <div class="form-check">
                <input type="checkbox" name="privacy_consent" id="privacy_consent" value="1" required checked>
                <label for="privacy_consent" style="font-weight: 700; color: #166534; cursor: pointer; font-size: 13.5px;">
                    Patient has read, understood, and consented to the data privacy policy and record keeping.
                </label>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 30px;">
        <a href="{{ route('patients.index') }}" class="btn btn-secondary btn-lg">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-check"></i> Register Patient & Open Chart
        </button>
    </div>
</form>
@endsection
