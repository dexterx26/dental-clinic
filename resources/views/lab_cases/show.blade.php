@extends('layouts.app')

@section('title', 'Lab Case ' . $labCase->case_number)

@section('content')
<div class="content-header no-print">
    <div>
        <h1 class="page-title">Lab Case: {{ $labCase->case_number }}</h1>
        <p class="page-subtitle">{{ $labCase->appliance_type }} &bull; Patient: <strong style="color: var(--primary);">{{ $labCase->patient->full_name }}</strong></p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" class="btn btn-secondary" onclick="window.print()">
            <i class="fa-solid fa-print"></i>
            <span>Print Work Ticket</span>
        </button>
        <a href="{{ route('lab-cases.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Lab Cases</span>
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Left Column: Case Details & Work Order -->
    <div class="card" style="padding: 32px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 2px solid var(--border-light); margin-bottom: 24px;">
            <div>
                <span class="badge" style="background: var(--primary-light); color: var(--primary); font-size: 12px; margin-bottom: 6px;">
                    DENTAL LABORATORY WORK TICKET
                </span>
                <h2 style="font-size: 22px; margin: 0;">{{ $labCase->appliance_type }}</h2>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
                    Case Reference: <strong style="font-family: monospace; color: var(--text-main);">{{ $labCase->case_number }}</strong>
                </div>
            </div>

            @php
                $statusBadges = [
                    'sent' => ['bg' => 'var(--warning-light)', 'color' => 'var(--warning)', 'label' => 'Sent to Lab'],
                    'in_progress' => ['bg' => 'var(--info-light)', 'color' => 'var(--info)', 'label' => 'In Fabrication'],
                    'delivered' => ['bg' => 'var(--primary-light)', 'color' => 'var(--primary)', 'label' => 'Delivered to Clinic'],
                    'fitted' => ['bg' => 'var(--success-light)', 'color' => 'var(--success)', 'label' => 'Fitted to Patient'],
                    'adjustment_needed' => ['bg' => '#fef3c7', 'color' => '#d97706', 'label' => 'Adjustment Needed'],
                    'rejected' => ['bg' => 'var(--danger-light)', 'color' => 'var(--danger)', 'label' => 'Rejected / Remake'],
                    'cancelled' => ['bg' => 'var(--bg-main)', 'color' => 'var(--text-muted)', 'label' => 'Cancelled'],
                ];
                $sb = $statusBadges[$labCase->status] ?? ['bg' => 'var(--bg-main)', 'color' => 'var(--text-muted)', 'label' => $labCase->status];
            @endphp
            <div style="text-align: right;">
                <span class="badge" style="background: {{ $sb['bg'] }}; color: {{ $sb['color'] }}; font-size: 13px; padding: 6px 12px; font-weight: 700;">
                    {{ $sb['label'] }}
                </span>
            </div>
        </div>

        <!-- Specifications Grid -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; background: var(--bg-main); padding: 18px; border-radius: var(--radius-md);">
            <div>
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-light);">TOOTH / SITE</div>
                <div style="font-size: 16px; font-weight: 700; color: var(--text-main); margin-top: 4px;">
                    {{ $labCase->tooth_number ?: 'Not specified' }}
                </div>
            </div>

            <div>
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-light);">SHADE / COLOR GUIDE</div>
                <div style="font-size: 16px; font-weight: 700; color: var(--primary); margin-top: 4px;">
                    {{ $labCase->shade ?: 'Standard' }}
                </div>
            </div>

            <div>
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-light);">ESTIMATED LAB FEE</div>
                <div style="font-size: 16px; font-weight: 700; color: var(--text-main); margin-top: 4px;">
                    ₱{{ number_format($labCase->cost, 2) }}
                </div>
            </div>
        </div>

        <!-- Lab Instructions -->
        <div style="margin-bottom: 24px;">
            <h3 style="font-size: 15px; margin-bottom: 8px; color: var(--text-muted);"><i class="fa-solid fa-clipboard-list text-primary"></i> Laboratory Instructions / Rx</h3>
            <div style="background: var(--bg-main); border-left: 4px solid var(--primary); padding: 16px; border-radius: 4px; font-size: 14px; line-height: 1.6;">
                {{ $labCase->instructions ?: 'No specific instructions provided.' }}
            </div>
        </div>

        <!-- Timeline Dates -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
            <div style="border: 1px solid var(--border); padding: 14px; border-radius: var(--radius-sm);">
                <div style="font-size: 12px; color: var(--text-muted);">Sent Date</div>
                <div style="font-size: 15px; font-weight: 700; color: var(--text-main); margin-top: 4px;">
                    {{ $labCase->sent_date->format('M d, Y') }}
                </div>
            </div>
            <div style="border: 1px solid var(--border); padding: 14px; border-radius: var(--radius-sm);">
                <div style="font-size: 12px; color: var(--text-muted);">Expected Delivery</div>
                <div style="font-size: 15px; font-weight: 700; color: var(--primary); margin-top: 4px;">
                    {{ $labCase->expected_delivery_date->format('M d, Y') }}
                </div>
            </div>
            <div style="border: 1px solid var(--border); padding: 14px; border-radius: var(--radius-sm);">
                <div style="font-size: 12px; color: var(--text-muted);">Actual Delivered</div>
                <div style="font-size: 15px; font-weight: 700; color: {{ $labCase->actual_delivery_date ? 'var(--success)' : 'var(--text-light)' }}; margin-top: 4px;">
                    {{ $labCase->actual_delivery_date ? $labCase->actual_delivery_date->format('M d, Y') : 'Pending Arrival' }}
                </div>
            </div>
        </div>

        @if($labCase->notes)
        <div>
            <h3 style="font-size: 15px; margin-bottom: 8px; color: var(--text-muted);"><i class="fa-solid fa-notes-medical"></i> Clinical Activity & Remarks</h3>
            <div style="white-space: pre-wrap; font-size: 13px; color: var(--text-main); background: var(--bg-main); padding: 14px; border-radius: var(--radius-sm);">{{ $labCase->notes }}</div>
        </div>
        @endif
    </div>

    <!-- Right Column: Status Transition & Patient Card -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Status Updater Card -->
        <div class="card no-print" style="padding: 24px;">
            <h3 style="font-size: 16px; margin-bottom: 16px;"><i class="fa-solid fa-arrows-spin text-primary"></i> Update Case Status</h3>
            <form action="{{ route('lab-cases.update-status', $labCase) }}" method="POST">
                @csrf
                @method('PATCH')

                <div style="margin-bottom: 14px;">
                    <label class="form-label">New Status</label>
                    <select name="status" class="form-control" required>
                        <option value="sent" {{ $labCase->status == 'sent' ? 'selected' : '' }}>Sent to Lab</option>
                        <option value="in_progress" {{ $labCase->status == 'in_progress' ? 'selected' : '' }}>In Fabrication</option>
                        <option value="delivered" {{ $labCase->status == 'delivered' ? 'selected' : '' }}>Delivered to Clinic</option>
                        <option value="fitted" {{ $labCase->status == 'fitted' ? 'selected' : '' }}>Fitted to Patient</option>
                        <option value="adjustment_needed" {{ $labCase->status == 'adjustment_needed' ? 'selected' : '' }}>Adjustment Needed</option>
                        <option value="rejected" {{ $labCase->status == 'rejected' ? 'selected' : '' }}>Rejected / Remake</option>
                        <option value="cancelled" {{ $labCase->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div style="margin-bottom: 14px;">
                    <label class="form-label">Delivery Date (if delivered)</label>
                    <input type="date" name="actual_delivery_date" class="form-control" value="{{ $labCase->actual_delivery_date ? $labCase->actual_delivery_date->format('Y-m-d') : date('Y-m-d') }}">
                </div>

                <div style="margin-bottom: 16px;">
                    <label class="form-label">Progress Note</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Try-in completed with good marginal fit, sent back for final glazing"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fa-solid fa-check"></i> Update Status
                </button>
            </form>
        </div>

        <!-- Patient Info Card -->
        <div class="card" style="padding: 24px;">
            <h3 style="font-size: 16px; margin-bottom: 14px;"><i class="fa-solid fa-user text-primary"></i> Patient Information</h3>
            <div style="font-weight: 700; font-size: 16px;">
                <a href="{{ route('patients.show', $labCase->patient) }}">{{ $labCase->patient->full_name }}</a>
            </div>
            <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
                <div>Patient ID: <strong>{{ $labCase->patient->patient_number }}</strong></div>
                <div>Phone: {{ $labCase->patient->phone }}</div>
                <div>Age/Gender: {{ $labCase->patient->age }} y/o &bull; {{ $labCase->patient->gender }}</div>
            </div>
            <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--border);">
                <div style="font-size: 12px; color: var(--text-muted);">Attending Dentist:</div>
                <div style="font-weight: 600; font-size: 14px;">{{ $labCase->dentist->name ?? 'Dr. Unassigned' }}</div>
            </div>
            <div style="margin-top: 10px;">
                <div style="font-size: 12px; color: var(--text-muted);">Laboratory:</div>
                <div style="font-weight: 600; font-size: 14px; color: var(--text-main);">{{ $labCase->lab_name }}</div>
                @if($labCase->technician_name)
                    <div style="font-size: 12px; color: var(--text-muted);">Technician: {{ $labCase->technician_name }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
