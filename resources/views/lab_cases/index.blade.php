@extends('layouts.app')

@section('title', 'Dental Laboratory Cases')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Dental Laboratory Cases</h1>
        <p class="page-subtitle">Track prosthetics, crowns, bridges, dentures, and orthodontic appliance fabrication</p>
    </div>
    <a href="{{ route('lab-cases.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i>
        <span>Send New Lab Case</span>
    </a>
</div>

<!-- Metrics -->
<div class="metric-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="metric-card card" style="padding: 18px; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-flask-vial"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">Total Lab Cases</div>
            <div style="font-size: 24px; font-weight: 700; color: var(--text-main);">{{ $totalCases }}</div>
        </div>
    </div>

    <div class="metric-card card" style="padding: 18px; display: flex; align-items: center; gap: 16px; border-left: 4px solid var(--warning);">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--warning-light); color: var(--warning); display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--warning); font-weight: 600;">In Fabrication / Sent</div>
            <div style="font-size: 24px; font-weight: 700; color: var(--warning);">{{ $inProgressCount }}</div>
        </div>
    </div>

    <div class="metric-card card" style="padding: 18px; display: flex; align-items: center; gap: 16px; border-left: 4px solid var(--info);">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--info-light); color: var(--info); display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-truck-ramp-box"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--info); font-weight: 600;">Delivered (Awaiting Fit)</div>
            <div style="font-size: 24px; font-weight: 700; color: var(--info);">{{ $deliveredCount }}</div>
        </div>
    </div>

    <div class="metric-card card" style="padding: 18px; display: flex; align-items: center; gap: 16px; border-left: 4px solid var(--success);">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--success-light); color: var(--success); display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--success); font-weight: 600;">Successfully Fitted</div>
            <div style="font-size: 24px; font-weight: 700; color: var(--success);">{{ $fittedCount }}</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="padding: 16px; margin-bottom: 20px;">
    <form action="{{ route('lab-cases.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
        <div style="flex: 1; min-width: 240px; position: relative;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 12px; color: var(--text-light);"></i>
            <input type="text" name="search" class="form-control" style="padding-left: 36px;" placeholder="Search case #, patient, lab name, appliance..." value="{{ request('search') }}">
        </div>

        <div style="width: 170px;">
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent to Lab</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Fabrication</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered to Clinic</option>
                <option value="fitted" {{ request('status') == 'fitted' ? 'selected' : '' }}>Fitted to Patient</option>
                <option value="adjustment_needed" {{ request('status') == 'adjustment_needed' ? 'selected' : '' }}>Adjustment Needed</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected / Remake</option>
            </select>
        </div>

        <div style="width: 170px;">
            <select name="dentist_id" class="form-control" onchange="this.form.submit()">
                <option value="">All Dentists</option>
                @foreach($dentists as $d)
                    <option value="{{ $d->id }}" {{ request('dentist_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-secondary">Filter</button>
        @if(request()->anyFilled(['search', 'status', 'dentist_id']))
            <a href="{{ route('lab-cases.index') }}" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i></a>
        @endif
    </form>
</div>

<!-- Lab Cases Table -->
<div class="card" style="overflow: hidden;">
    <table class="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: var(--bg-main); text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">
                <th style="padding: 14px 18px;">Case # / Patient</th>
                <th style="padding: 14px 18px;">Appliance / Specs</th>
                <th style="padding: 14px 18px;">Laboratory</th>
                <th style="padding: 14px 18px;">Dentist</th>
                <th style="padding: 14px 18px;">Sent & Expected</th>
                <th style="padding: 14px 18px;">Status</th>
                <th style="padding: 14px 18px; text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($labCases as $case)
            @php
                $statusBadges = [
                    'sent' => ['bg' => 'var(--warning-light)', 'color' => 'var(--warning)', 'label' => 'Sent to Lab'],
                    'in_progress' => ['bg' => 'var(--info-light)', 'color' => 'var(--info)', 'label' => 'In Fabrication'],
                    'delivered' => ['bg' => 'var(--primary-light)', 'color' => 'var(--primary)', 'label' => 'Delivered'],
                    'fitted' => ['bg' => 'var(--success-light)', 'color' => 'var(--success)', 'label' => 'Fitted'],
                    'adjustment_needed' => ['bg' => '#fef3c7', 'color' => '#d97706', 'label' => 'Adjustment Needed'],
                    'rejected' => ['bg' => 'var(--danger-light)', 'color' => 'var(--danger)', 'label' => 'Rejected'],
                    'cancelled' => ['bg' => 'var(--bg-main)', 'color' => 'var(--text-muted)', 'label' => 'Cancelled'],
                ];
                $sb = $statusBadges[$case->status] ?? ['bg' => 'var(--bg-main)', 'color' => 'var(--text-muted)', 'label' => $case->status];
            @endphp
            <tr style="border-top: 1px solid var(--border);">
                <td style="padding: 14px 18px;">
                    <a href="{{ route('lab-cases.show', $case) }}" style="font-weight: 700; font-family: monospace; color: var(--primary);">
                        {{ $case->case_number }}
                    </a>
                    <div style="font-weight: 600; color: var(--text-main); margin-top: 2px;">
                        <a href="{{ route('patients.show', $case->patient) }}">{{ $case->patient->full_name }}</a>
                    </div>
                </td>
                <td style="padding: 14px 18px;">
                    <div style="font-weight: 600; font-size: 13px;">{{ $case->appliance_type }}</div>
                    <div style="font-size: 12px; color: var(--text-muted);">
                        @if($case->tooth_number) Tooth: <strong>{{ $case->tooth_number }}</strong> @endif
                        @if($case->shade) &bull; Shade: <strong>{{ $case->shade }}</strong> @endif
                    </div>
                </td>
                <td style="padding: 14px 18px;">
                    <div style="font-weight: 600; font-size: 13px;">{{ $case->lab_name }}</div>
                    @if($case->technician_name)
                        <div style="font-size: 11px; color: var(--text-muted);">Tech: {{ $case->technician_name }}</div>
                    @endif
                </td>
                <td style="padding: 14px 18px; font-size: 13px;">
                    {{ $case->dentist->name ?? 'Dr. Unassigned' }}
                </td>
                <td style="padding: 14px 18px; font-size: 13px;">
                    <div>Sent: {{ $case->sent_date->format('M d, Y') }}</div>
                    <div style="font-size: 12px; color: {{ $case->expected_delivery_date->isPast() && !in_array($case->status, ['delivered', 'fitted']) ? 'var(--danger)' : 'var(--text-muted)' }};">
                        Due: <strong>{{ $case->expected_delivery_date->format('M d, Y') }}</strong>
                    </div>
                </td>
                <td style="padding: 14px 18px;">
                    <span class="badge" style="background: {{ $sb['bg'] }}; color: {{ $sb['color'] }}; font-size: 11px;">
                        {{ $sb['label'] }}
                    </span>
                </td>
                <td style="padding: 14px 18px; text-align: right;">
                    <a href="{{ route('lab-cases.show', $case) }}" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-eye"></i> View Case
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding: 40px; text-align: center; color: var(--text-muted);">
                    No laboratory cases logged. Click "Send New Lab Case" to start tracking.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($labCases->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
            {{ $labCases->links() }}
        </div>
    @endif
</div>
@endsection
