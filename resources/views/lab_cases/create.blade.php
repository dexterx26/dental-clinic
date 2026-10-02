@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.min.css" rel="stylesheet">
<style>
    .ts-wrapper.form-control {
        padding: 0 !important;
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
    }
    .ts-control {
        background-color: #ffffff !important;
        border: 1px solid var(--border, #e2e8f0) !important;
        border-radius: var(--radius-sm, 6px) !important;
        padding: 9px 12px !important;
        min-height: 40px !important;
        font-size: 13.5px !important;
        color: var(--text-main, #0f172a) !important;
        box-shadow: var(--shadow-sm, 0 1px 2px 0 rgb(0 0 0 / 0.05));
        transition: var(--transition, all 0.2s ease);
        display: flex;
        align-items: center;
    }
    .ts-wrapper.focus .ts-control {
        border-color: var(--primary, #0f766e) !important;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.15) !important;
        outline: none !important;
    }
    .ts-control input {
        font-size: 13.5px !important;
        color: var(--text-main, #0f172a) !important;
    }
    .ts-dropdown,
    .ts-dropdown .ts-dropdown-content {
        background-color: #ffffff !important;
    }
    .ts-dropdown {
        border: 1px solid var(--border, #e2e8f0) !important;
        border-radius: var(--radius-sm, 6px) !important;
        box-shadow: var(--shadow-lg, 0 10px 15px -3px rgb(0 0 0 / 0.08)) !important;
        z-index: 9999 !important;
        font-size: 13.5px !important;
        margin-top: 4px !important;
        overflow: hidden !important;
    }
    .ts-dropdown .option {
        padding: 10px 14px !important;
        color: var(--text-main, #0f172a) !important;
        cursor: pointer !important;
        border-bottom: 1px solid var(--border-light, #f1f5f9);
    }
    .ts-dropdown .option:last-child {
        border-bottom: none;
    }
    .ts-dropdown .active,
    .ts-dropdown .option:hover {
        background-color: var(--primary-light, #f0fdfa) !important;
        color: var(--primary, #0f766e) !important;
    }
    .ts-dropdown .highlight {
        background: rgba(15, 118, 110, 0.15) !important;
        color: var(--primary, #0f766e) !important;
        font-weight: 600;
        border-radius: 2px;
        padding: 1px 3px;
    }
</style>
@endpush

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
                <select id="patient_select" name="patient_id" class="form-control" required>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new TomSelect('#patient_select', {
            placeholder: 'Search patient by name...',
            searchField: ['text'],
            maxOptions: 150,
            highlight: true,
        });
    });
</script>
@endpush
