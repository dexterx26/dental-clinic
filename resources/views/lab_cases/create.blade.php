@extends('layouts.app')

@section('title', 'Send New Dental Lab Case')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Send Dental Laboratory Case</h1>
        <p class="page-subtitle">Prescribe crowns, bridges, dentures, or orthodontic appliances to a dental laboratory</p>
    </div>
    <a href="{{ route('lab-cases.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Back to Cases</span>
    </a>
</div>

<div class="card" style="max-width: 850px; margin: 0 auto; padding: 28px;">
    <form action="{{ route('lab-cases.store') }}" method="POST">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label class="form-label">Patient <span style="color: var(--danger);">*</span></label>
                <select name="patient_id" class="form-control" required>
                    <option value="">-- Choose Patient --</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" {{ (old('patient_id', optional($selectedPatient)->id) == $p->id) ? 'selected' : '' }}>
                            {{ $p->full_name }} ({{ $p->patient_number }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Attending Dentist <span style="color: var(--danger);">*</span></label>
                <select name="dentist_id" class="form-control" required>
                    <option value="">-- Choose Dentist --</option>
                    @foreach($dentists as $d)
                        <option value="{{ $d->id }}" {{ old('dentist_id', auth()->id()) == $d->id ? 'selected' : '' }}>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label class="form-label">Laboratory Name <span style="color: var(--danger);">*</span></label>
                <input type="text" name="lab_name" class="form-control" placeholder="e.g., Apex Dental Studio Manila, CrownCraft Lab" value="{{ old('lab_name') }}" required>
            </div>
            <div>
                <label class="form-label">Dental Technician Name</label>
                <input type="text" name="technician_name" class="form-control" placeholder="e.g., Tech. Robert Ramos" value="{{ old('technician_name') }}">
            </div>
        </div>

        <div style="margin-bottom: 16px;">
            <label class="form-label">Appliance / Prosthesis Type <span style="color: var(--danger);">*</span></label>
            <select name="appliance_type" class="form-control" required>
                @foreach($applianceTypes as $type)
                    <option value="{{ $type }}" {{ old('appliance_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label class="form-label">Tooth Number(s) / Arch</label>
                <input type="text" name="tooth_number" class="form-control" placeholder="e.g., 11, 21, or Maxillary Arch" value="{{ old('tooth_number') }}">
            </div>
            <div>
                <label class="form-label">Tooth Shade / Color Guide</label>
                <input type="text" name="shade" class="form-control" placeholder="e.g., Vita A2, Bleach B1, 2M2" value="{{ old('shade') }}">
            </div>
            <div>
                <label class="form-label">Estimated Lab Fee (₱)</label>
                <input type="number" step="0.01" name="cost" class="form-control" placeholder="0.00" value="{{ old('cost', '0.00') }}">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label class="form-label">Sent Date <span style="color: var(--danger);">*</span></label>
                <input type="date" name="sent_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div>
                <label class="form-label">Expected Delivery Date <span style="color: var(--danger);">*</span></label>
                <input type="date" name="expected_delivery_date" class="form-control" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required>
            </div>
        </div>

        <div style="margin-bottom: 16px;">
            <label class="form-label">Laboratory Prescription & Instructions</label>
            <textarea name="instructions" class="form-control" rows="3" placeholder="e.g., Metal-free margin on buccal, light contact on occlusion, match shade photo sent via Viber. Return for framework try-in first.">{{ old('instructions') }}</textarea>
        </div>

        <div style="margin-bottom: 24px;">
            <label class="form-label">Internal Clinical Notes</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="e.g., Patient informed of 1-week turnaround, booked tentative fitting appointment for next Tuesday.">{{ old('notes') }}</textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <a href="{{ route('lab-cases.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Log & Dispatch Lab Case
            </button>
        </div>
    </form>
</div>
@endsection
