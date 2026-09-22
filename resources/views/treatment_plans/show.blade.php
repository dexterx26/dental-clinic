@extends('layouts.app')

@section('title', 'Treatment Plan ' . $treatmentPlan->plan_number)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Treatment Plan: {{ $treatmentPlan->plan_number }}</h1>
        <div class="page-subtitle">{{ $treatmentPlan->title }} &bull; Patient: <strong>{{ $treatmentPlan->patient->full_name }}</strong></div>
    </div>
    <div style="display: flex; gap: 8px;">
        <button type="button" class="btn btn-secondary" onclick="window.print();">
            <i class="fa-solid fa-print"></i> Print Plan
        </button>
        <a href="{{ route('patients.show', ['patient' => $treatmentPlan->patient, 'tab' => 'plans']) }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back to Patient
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <div>
        <!-- Plan Details & Items -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-file-invoice" style="color: var(--primary);"></i>
                    <span>Plan Procedures & Phased Execution</span>
                </div>
                <div style="font-size: 16px; font-weight: 700; color: var(--primary);">
                    Total: ₱{{ number_format($treatmentPlan->total_estimated_cost, 2) }}
                </div>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Procedure Name</th>
                                <th>Tooth / Surface</th>
                                <th>Priority</th>
                                <th>Sessions</th>
                                <th>Est. Cost</th>
                                <th>Status</th>
                                <th style="text-align: right;" class="no-print">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($treatmentPlan->items as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    <div style="font-weight: 600;">{{ $item->procedure_name }}</div>
                                    @if($item->notes)
                                    <div style="font-size: 11px; color: var(--text-muted);">{{ $item->notes }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($item->tooth_number)
                                        <span class="badge badge-secondary">Tooth #{{ $item->tooth_number }} ({{ $item->surface ?? 'Whole' }})</span>
                                    @else
                                        <span style="color: var(--text-light); font-size: 12px;">General</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $priColor = ['high' => 'badge-danger', 'medium' => 'badge-warning', 'low' => 'badge-secondary'];
                                    @endphp
                                    <span class="badge {{ $priColor[$item->priority] ?? 'badge-secondary' }}">{{ ucfirst($item->priority) }}</span>
                                </td>
                                <td>{{ $item->sessions_required }}</td>
                                <td><strong>₱{{ number_format($item->estimated_cost, 2) }}</strong></td>
                                <td>
                                    <span class="badge {{ $item->status === 'completed' ? 'badge-success' : ($item->status === 'in_progress' ? 'badge-primary' : 'badge-secondary') }}">
                                        {{ ucwords(str_replace('_', ' ', $item->status)) }}
                                    </span>
                                </td>
                                <td style="text-align: right;" class="no-print">
                                    @if($item->status !== 'completed')
                                    <a href="{{ route('treatments.create', ['patient' => $treatmentPlan->patient, 'plan_item_id' => $item->id]) }}" class="btn btn-primary btn-sm" title="Execute and record procedure">
                                        <i class="fa-solid fa-play"></i> Execute
                                    </a>
                                    @else
                                    <span style="color: var(--success); font-size: 13px;"><i class="fa-solid fa-circle-check"></i> Done</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($treatmentPlan->diagnosis || $treatmentPlan->notes)
        <div class="card">
            <div class="card-header">
                <div class="card-title">Clinical Notes & Diagnosis</div>
            </div>
            <div class="card-body">
                @if($treatmentPlan->diagnosis)
                <div style="margin-bottom: 12px;">
                    <strong>Diagnosis:</strong>
                    <p style="color: var(--text-main); margin-top: 4px;">{{ $treatmentPlan->diagnosis }}</p>
                </div>
                @endif
                @if($treatmentPlan->notes)
                <div>
                    <strong>Notes:</strong>
                    <p style="color: var(--text-muted); margin-top: 4px; white-space: pre-line;">{{ $treatmentPlan->notes }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Right Column: Status & Acceptance -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-user-check" style="color: var(--primary);"></i>
                    <span>Patient Approval Status</span>
                </div>
            </div>
            <div class="card-body">
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 12px; color: var(--text-muted);">Current Approval State</div>
                    <div style="margin-top: 4px;">
                        @php
                            $tpBadge = [
                                'proposed' => 'badge-secondary',
                                'presented' => 'badge-info',
                                'accepted' => 'badge-success',
                                'declined' => 'badge-danger',
                                'completed' => 'badge-primary',
                                'partially_completed' => 'badge-warning',
                            ];
                        @endphp
                        <span class="badge {{ $tpBadge[$treatmentPlan->status] ?? 'badge-secondary' }}" style="font-size: 13px; padding: 4px 10px;">
                            {{ ucfirst(str_replace('_', ' ', $treatmentPlan->status)) }}
                        </span>
                    </div>
                </div>

                @if($treatmentPlan->patient_decision)
                <div style="margin-bottom: 16px; background: #f8fafc; padding: 10px; border-radius: 6px; font-size: 12.5px;">
                    <strong>Decision Notes:</strong>
                    <div style="color: var(--text-muted); margin-top: 2px;">{{ $treatmentPlan->patient_decision }}</div>
                    @if($treatmentPlan->decision_date)
                    <div style="font-size: 11px; color: var(--text-light); margin-top: 4px;">Decided on: {{ $treatmentPlan->decision_date->format('M d, Y') }}</div>
                    @endif
                </div>
                @endif

                <!-- Update Status Form -->
                <form action="{{ route('treatment-plans.update-status', $treatmentPlan) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label class="form-label" for="status">Update Plan Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="proposed" {{ $treatmentPlan->status === 'proposed' ? 'selected' : '' }}>Proposed to Dentist</option>
                            <option value="presented" {{ $treatmentPlan->status === 'presented' ? 'selected' : '' }}>Presented to Patient</option>
                            <option value="accepted" {{ $treatmentPlan->status === 'accepted' ? 'selected' : '' }}>Accepted by Patient</option>
                            <option value="partially_completed" {{ $treatmentPlan->status === 'partially_completed' ? 'selected' : '' }}>Partially Completed</option>
                            <option value="completed" {{ $treatmentPlan->status === 'completed' ? 'selected' : '' }}>All Steps Completed</option>
                            <option value="declined" {{ $treatmentPlan->status === 'declined' ? 'selected' : '' }}>Declined by Patient</option>
                            <option value="cancelled" {{ $treatmentPlan->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="patient_decision">Patient Decision Comments</label>
                        <textarea name="patient_decision" id="patient_decision" class="form-control" rows="2" placeholder="e.g. Patient accepted full plan, scheduled visit 1...">{{ $treatmentPlan->patient_decision }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fa-solid fa-floppy-disk"></i> Update Status
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
