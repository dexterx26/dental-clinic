@extends('layouts.app')

@section('title', 'Clinic Reports & Analytics')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Clinic Performance & Financial Reports</h1>
        <div class="page-subtitle">Detailed analytics on clinical revenue, dentist productivity, and procedure statistics</div>
    </div>
    <div class="no-print">
        <button type="button" class="btn btn-secondary" onclick="window.print();">
            <i class="fa-solid fa-print"></i> Print Report Summary
        </button>
    </div>
</div>

<!-- Date Range Picker -->
<div class="card no-print" style="margin-bottom: 24px;">
    <div class="card-body" style="padding: 16px;">
        <form action="{{ route('reports.index') }}" method="GET" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            <div>
                <label class="form-label" style="font-size: 11.5px; margin-bottom: 4px;">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div>
                <label class="form-label" style="font-size: 11.5px; margin-bottom: 4px;">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </div>
        </form>
    </div>
</div>

<!-- Financial Summary KPIs -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Invoiced</div>
            <div class="stat-value">₱{{ number_format($totalBilled, 2) }}</div>
            <div class="stat-sub">{{ \Carbon\Carbon::parse($startDate)->format('M d') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fa-solid fa-peso-sign"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Collected</div>
            <div class="stat-value">₱{{ number_format($totalCollected, 2) }}</div>
            <div class="stat-sub">Actual payments received</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon danger">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Outstanding Receivables</div>
            <div class="stat-value">₱{{ number_format($totalOutstanding, 2) }}</div>
            <div class="stat-sub">Uncollected balances</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon secondary">
            <i class="fa-solid fa-user-plus"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">New Patients Registered</div>
            <div class="stat-value">{{ $newPatientsCount }}</div>
            <div class="stat-sub">During selected period</div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Revenue by Dentist -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-user-doctor" style="color: var(--primary);"></i>
                <span>Clinician Productivity & Collections</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Attending Dentist</th>
                            <th>Total Billed</th>
                            <th>Total Collected</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($revenueByDentist as $rbd)
                        <tr>
                            <td>
                                <strong>Dr. {{ $rbd->dentist ? $rbd->dentist->name : 'General' }}</strong>
                                <div style="font-size: 11px; color: var(--text-light);">{{ $rbd->dentist ? $rbd->dentist->specialization : '' }}</div>
                            </td>
                            <td>₱{{ number_format($rbd->total_billed, 2) }}</td>
                            <td style="color: #15803d; font-weight: 700;">₱{{ number_format($rbd->total_collected, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 20px; color: var(--text-muted);">
                                No clinician billing records for this period.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Revenue by Payment Method -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-wallet" style="color: var(--primary);"></i>
                <span>Payment Collections by Channel</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Payment Channel</th>
                            <th>Transactions</th>
                            <th>Total Collected</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentsByMethod as $pbm)
                        <tr>
                            <td>
                                <strong style="text-transform: uppercase;">{{ str_replace('_', ' ', $pbm->payment_method) }}</strong>
                            </td>
                            <td>{{ $pbm->count }} txns</td>
                            <td style="color: #15803d; font-weight: 700;">₱{{ number_format($pbm->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 20px; color: var(--text-muted);">
                                No payment records for this period.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Top Clinical Procedures -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-tooth" style="color: var(--primary);"></i>
                <span>Top Clinical Procedures Performed</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Procedure Name</th>
                            <th>Count</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProcedures as $tp)
                        <tr>
                            <td><strong>{{ $tp->procedure_name }}</strong></td>
                            <td><span class="badge badge-secondary">{{ $tp->total_count }} times</span></td>
                            <td style="font-weight: 600;">₱{{ number_format($tp->total_revenue, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 20px; color: var(--text-muted);">
                                No procedure records in this timeframe.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Appointment Attendance Summary -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-calendar-check" style="color: var(--primary);"></i>
                <span>Appointment Attendance Breakdown</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Total Visits</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointmentsBreakdown as $ab)
                        <tr>
                            <td>
                                <span class="badge badge-secondary" style="font-size: 12.5px;">
                                    {{ ucwords(str_replace('_', ' ', $ab->status)) }}
                                </span>
                            </td>
                            <td><strong>{{ $ab->count }}</strong></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" style="text-align: center; padding: 20px; color: var(--text-muted);">
                                No appointments recorded in this timeframe.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
