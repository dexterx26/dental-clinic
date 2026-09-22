@extends('layouts.app')

@section('title', 'Schedule Patient Appointment')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Book New Appointment</h1>
        <div class="page-subtitle">Schedule clinical consultation, procedure, or follow-up visit</div>
    </div>
    <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Schedule
    </a>
</div>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-calendar-plus" style="color: var(--primary);"></i>
                <span>Appointment Booking Form</span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('appointments.store') }}" method="POST">
                @csrf

                <!-- Patient Selection -->
                <div class="form-group">
                    <label class="form-label required" for="patient_id">Select Patient</label>
                    <select name="patient_id" id="patient_id" class="form-control" required>
                        <option value="">-- Choose Patient --</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}" {{ (old('patient_id') == $p->id || (isset($patient) && $patient->id == $p->id)) ? 'selected' : '' }}>
                                {{ $p->full_name }} ({{ $p->patient_number }}) - Phone: {{ $p->phone }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <!-- Dentist -->
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="dentist_id">Attending Dentist</label>
                            <select name="dentist_id" id="dentist_id" class="form-control" required>
                                <option value="">-- Choose Dentist --</option>
                                @foreach($dentists as $d)
                                    <option value="{{ $d->id }}" {{ old('dentist_id', (isset($patient) && $patient->preferred_dentist_id == $d->id) ? $d->id : '') == $d->id ? 'selected' : '' }}>
                                        Dr. {{ $d->name }} ({{ $d->specialization ?? 'General Dentistry' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Service -->
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="service_id">Dental Service</label>
                            <select name="service_id" id="service_id" class="form-control">
                                <option value="" data-duration="30">-- General Consultation / Examination (30 mins) --</option>
                                @foreach($services as $srv)
                                    <option value="{{ $srv->id }}" data-duration="{{ $srv->duration_minutes }}" {{ old('service_id') == $srv->id ? 'selected' : '' }}>
                                        {{ $srv->name }} (₱{{ number_format($srv->standard_price, 2) }} - {{ $srv->duration_minutes }}m)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Date -->
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="appointment_date">Appointment Date</label>
                            <input type="date" name="appointment_date" id="appointment_date" class="form-control" required value="{{ old('appointment_date', \Carbon\Carbon::today()->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <!-- Start Time -->
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="start_time">Start Time</label>
                            <input type="time" name="start_time" id="start_time" class="form-control" required value="{{ old('start_time', '09:00') }}">
                        </div>
                    </div>

                    <!-- End Time -->
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label required" for="end_time">End Time</label>
                            <input type="time" name="end_time" id="end_time" class="form-control" required value="{{ old('end_time', '09:45') }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reason">Chief Complaint / Reason for Appointment</label>
                    <input type="text" name="reason" id="reason" class="form-control" placeholder="e.g. Toothache on lower molar, routine prophylaxis, braces checkup" value="{{ old('reason') }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="notes">Internal Scheduling Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Pre-medication instructions, requests, assistant assignment...">{{ old('notes') }}</textarea>
                </div>

                <div class="form-check" style="margin-bottom: 24px;">
                    <input type="checkbox" name="allow_overlap" id="allow_overlap" value="1">
                    <label for="allow_overlap" style="font-size: 13px; color: var(--text-muted); cursor: pointer;">
                        Override / Allow emergency overlapping slot if schedule conflict exists
                    </label>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <a href="{{ route('appointments.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-calendar-check"></i> Confirm & Book Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('service_id').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const duration = parseInt(selected.getAttribute('data-duration') || '30', 10);
        const startTimeInput = document.getElementById('start_time');
        
        if (startTimeInput.value) {
            const [hours, minutes] = startTimeInput.value.split(':').map(Number);
            const date = new Date();
            date.setHours(hours, minutes + duration, 0, 0);
            
            const endHours = String(date.getHours()).padStart(2, '0');
            const endMins = String(date.getMinutes()).padStart(2, '0');
            document.getElementById('end_time').value = `${endHours}:${endMins}`;
        }
    });
</script>
@endsection
