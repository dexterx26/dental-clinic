@extends('layouts.app')

@section('title', 'Execute Digital Consent Form')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Execute Digital Informed Consent</h1>
        <p class="page-subtitle">Interactive touchscreen/mouse digital signature pad compliant with Philippine RA 10173</p>
    </div>
    <a href="{{ route('consent-forms.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Back to Consents</span>
    </a>
</div>

<div class="card" style="max-width: 850px; margin: 0 auto; padding: 32px;">
    <form action="{{ route('consent-forms.store') }}" method="POST" id="consentForm">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px;">
            <div>
                <label class="form-label">Patient <span style="color: var(--danger);">*</span></label>
                <select name="patient_id" class="form-control" required id="patientSelect">
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

        <div style="margin-bottom: 18px;">
            <label class="form-label">Procedure / Consent Category <span style="color: var(--danger);">*</span></label>
            <select name="consent_type" class="form-control" id="consentTypeSelect" required onchange="onConsentTypeChange()">
                <option value="tooth_extraction">Tooth Extraction & Oral Surgery</option>
                <option value="root_canal">Endodontic Therapy (Root Canal)</option>
                <option value="implant_surgery">Dental Implant Placement</option>
                <option value="orthodontic_treatment">Orthodontic Treatment</option>
                <option value="anesthesia">Local Anesthesia Administration</option>
                <option value="general_treatment">General Dental Treatment & Fillings</option>
                <option value="xray">Dental Radiography / X-Ray</option>
                <option value="other">Custom Dental Procedure</option>
            </select>
        </div>

        <div style="margin-bottom: 18px;">
            <label class="form-label">Consent Document Title <span style="color: var(--danger);">*</span></label>
            <input type="text" name="title" id="consentTitle" class="form-control" required value="Informed Consent for Tooth Extraction & Oral Surgery">
        </div>

        <div style="margin-bottom: 22px;">
            <label class="form-label">Clinical Disclosure, Alternatives & Risks Explained <span style="color: var(--danger);">*</span></label>
            <textarea name="description_and_risks" id="consentDescription" class="form-control" rows="6" required style="font-size: 13px; line-height: 1.6;"></textarea>
        </div>

        <!-- Digital Signature Canvas Section -->
        <div style="margin-bottom: 24px; padding: 20px; background: var(--bg-main); border: 1px solid var(--border); border-radius: var(--radius-md);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <div>
                    <label class="form-label" style="margin: 0; font-size: 14px; font-weight: 700; color: var(--primary);">
                        <i class="fa-solid fa-pen-nib"></i> Patient Digital Signature <span style="color: var(--danger);">*</span>
                    </label>
                    <div style="font-size: 12px; color: var(--text-muted);">Sign with finger (touchscreen) or mouse pointer in the box below</div>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="clearSignaturePad()">
                    <i class="fa-solid fa-eraser"></i> Clear Pad
                </button>
            </div>

            <div style="border: 2px dashed var(--border); border-radius: var(--radius-sm); background: #ffffff; position: relative; text-align: center; overflow: hidden;">
                <canvas id="signaturePad" width="700" height="200" style="display: block; width: 100%; height: 200px; cursor: crosshair; touch-action: none;"></canvas>
                <div id="sigPlaceholder" style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; pointer-events: none; color: #cbd5e1; font-size: 15px; font-style: italic;">
                    Patient signs here &bull; Lagda ng Pasyente
                </div>
            </div>
            <input type="hidden" name="patient_signature" id="patientSignatureInput">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
            <div>
                <label class="form-label">Witness Name (Clinic Assistant / Staff)</label>
                <input type="text" name="witness_name" class="form-control" placeholder="e.g., Nurse Anna Dela Cruz" value="{{ old('witness_name', auth()->user()->isAssistant() ? auth()->user()->name : '') }}">
            </div>
            <div>
                <label class="form-label">Additional Clinic Notes</label>
                <input type="text" name="notes" class="form-control" placeholder="e.g. Consent explained in Filipino / English" value="{{ old('notes') }}">
            </div>
        </div>

        <div style="margin-bottom: 24px; padding: 14px; background: var(--primary-light); border-radius: var(--radius-sm); border: 1px solid var(--primary-border); display: flex; gap: 12px; align-items: flex-start;">
            <input type="checkbox" id="legalConfirmation" required style="margin-top: 4px; accent-color: var(--primary);">
            <label for="legalConfirmation" style="font-size: 12px; color: var(--text-main); line-height: 1.5; cursor: pointer;">
                <strong>Patient Acknowledgement:</strong> I confirm that I have read (or had read to me) the information in this consent form. The potential risks, benefits, and alternatives of the procedure have been explained by the attending dental professional, and I give my full voluntary consent. I consent to the lawful processing of this document pursuant to Republic Act No. 10173 (Philippine Data Privacy Act).
            </label>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <a href="{{ route('consent-forms.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" id="submitConsentBtn">
                <i class="fa-solid fa-stamp"></i> Record Signed Informed Consent
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
const templates = @json($templates);

function onConsentTypeChange() {
    const select = document.getElementById('consentTypeSelect');
    const key = select.value;
    const titleInput = document.getElementById('consentTitle');
    const descInput = document.getElementById('consentDescription');

    if (templates[key]) {
        titleInput.value = templates[key].title;
        descInput.value = templates[key].description;
    } else {
        titleInput.value = 'Informed Consent for ' + select.options[select.selectedIndex].text;
        descInput.value = 'I hereby authorize the attending dentist to perform the indicated dental procedures. The nature, purpose, and potential clinical risks of the treatment have been fully explained to me.';
    }
}

// Initial template fill
onConsentTypeChange();

// Canvas Signature Pad Logic
const canvas = document.getElementById('signaturePad');
const ctx = canvas.getContext('2d');
const placeholder = document.getElementById('sigPlaceholder');
let isDrawing = false;
let hasDrawn = false;

// Ensure proper canvas pixel ratio
function resizeCanvas() {
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width;
    canvas.height = rect.height;
    ctx.lineWidth = 2.5;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.strokeStyle = '#0f172a';
}
window.addEventListener('resize', resizeCanvas);
setTimeout(resizeCanvas, 50);

function getPos(e) {
    const rect = canvas.getBoundingClientRect();
    if (e.touches && e.touches.length > 0) {
        return {
            x: e.touches[0].clientX - rect.left,
            y: e.touches[0].clientY - rect.top
        };
    }
    return {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
    };
}

function startDrawing(e) {
    isDrawing = true;
    hasDrawn = true;
    placeholder.style.display = 'none';
    const pos = getPos(e);
    ctx.beginPath();
    ctx.moveTo(pos.x, pos.y);
    e.preventDefault();
}

function draw(e) {
    if (!isDrawing) return;
    const pos = getPos(e);
    ctx.lineTo(pos.x, pos.y);
    ctx.stroke();
    e.preventDefault();
}

function stopDrawing(e) {
    if (isDrawing) {
        isDrawing = false;
    }
}

canvas.addEventListener('mousedown', startDrawing);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDrawing);
canvas.addEventListener('mouseleave', stopDrawing);

canvas.addEventListener('touchstart', startDrawing, { passive: false });
canvas.addEventListener('touchmove', draw, { passive: false });
canvas.addEventListener('touchend', stopDrawing);

function clearSignaturePad() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    hasDrawn = false;
    placeholder.style.display = 'flex';
    document.getElementById('patientSignatureInput').value = '';
}

document.getElementById('consentForm').addEventListener('submit', function(e) {
    if (!hasDrawn) {
        e.preventDefault();
        alert('Please have the patient sign in the digital signature box before submitting.');
        return false;
    }
    const dataUrl = canvas.toDataURL('image/png');
    document.getElementById('patientSignatureInput').value = dataUrl;
});
</script>
@endpush
@endsection
