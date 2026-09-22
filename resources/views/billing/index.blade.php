@extends('layouts.app')

@section('title', 'Invoices & Billing Management')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Invoices & Clinic Billing</h1>
        <div class="page-subtitle">Track patient billing, outstanding balances, collections, and receipts</div>
    </div>
</div>

<!-- Financial Summary KPIs -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon danger">
            <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Total Outstanding Receivables</div>
            <div class="stat-value">₱{{ number_format($totalReceivables, 2) }}</div>
            <div class="stat-sub">Unpaid & partial balances</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fa-solid fa-peso-sign"></i>
        </div>
        <div class="stat-content">
            <div class="stat-label">Collections This Month</div>
            <div class="stat-value">₱{{ number_format($totalCollectedMonth, 2) }}</div>
            <div class="stat-sub">{{ \Carbon\Carbon::now()->format('F Y') }}</div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px;">
        <form action="{{ route('invoices.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <div style="flex: 2; min-width: 250px;">
                <input type="text" name="search" class="form-control" placeholder="Search by invoice #, patient name, or patient ID..." value="{{ $search }}">
            </div>
            <div style="flex: 1; min-width: 160px;">
                <select name="status" class="form-control">
                    <option value="">All Payment Statuses</option>
                    <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partially_paid" {{ $status === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid in Full</option>
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-secondary">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if($search || $status)
                <a href="{{ route('invoices.index') }}" class="btn btn-secondary" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Invoices Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-receipt" style="color: var(--primary);"></i>
            <span>All Clinic Invoices</span>
        </div>
        <span class="badge badge-primary">{{ $invoices->total() }} Records</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Attending Dentist</th>
                        <th>Total Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                    <tr>
                        <td><strong style="color: var(--primary);">{{ $inv->invoice_number }}</strong></td>
                        <td>{{ $inv->invoice_date->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('patients.show', $inv->patient) }}" style="font-weight: 700;">
                                {{ $inv->patient->full_name }}
                            </a>
                            <div style="font-size: 11px; color: var(--text-light);">{{ $inv->patient->patient_number }}</div>
                        </td>
                        <td>{{ $inv->dentist ? 'Dr. ' . $inv->dentist->name : 'Clinic' }}</td>
                        <td>₱{{ number_format($inv->total_amount, 2) }}</td>
                        <td style="color: #15803d; font-weight: 600;">₱{{ number_format($inv->paid_amount, 2) }}</td>
                        <td style="color: {{ $inv->balance_amount > 0 ? '#b91c1c' : '#475569' }}; font-weight: 700;">
                            ₱{{ number_format($inv->balance_amount, 2) }}
                        </td>
                        <td>
                            @php
                                $invBadge = [
                                    'unpaid' => 'badge-danger',
                                    'partially_paid' => 'badge-warning',
                                    'paid' => 'badge-success',
                                ];
                            @endphp
                            <span class="badge {{ $invBadge[$inv->status] ?? 'badge-secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $inv->status)) }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('invoices.show', $inv) }}" class="btn btn-secondary btn-sm">
                                <i class="fa-solid fa-eye"></i> View & Pay
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            No invoices matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top: 16px;">
    {{ $invoices->links() }}
</div>
@endsection
