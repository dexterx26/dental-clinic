@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Dental Supply Purchase Orders</h1>
        <p class="page-subtitle">Procurement orders, material replenishment, and delivery tracking</p>
    </div>
    <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i>
        <span>Create Purchase Order</span>
    </a>
</div>

<!-- Filters -->
<div class="card" style="padding: 16px; margin-bottom: 20px;">
    <form action="{{ route('purchase-orders.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
        <div style="flex: 1; min-width: 200px;">
            <select name="supplier_id" class="form-control" onchange="this.form.submit()">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="width: 180px;">
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>Ordered / In Transit</option>
                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received & Stocked</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <button type="submit" class="btn btn-secondary">Filter</button>
        @if(request()->anyFilled(['supplier_id', 'status']))
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i></a>
        @endif
    </form>
</div>

<!-- PO Table -->
<div class="card" style="overflow: hidden;">
    <table class="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: var(--bg-main); text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">
                <th style="padding: 14px 18px;">PO Number</th>
                <th style="padding: 14px 18px;">Supplier</th>
                <th style="padding: 14px 18px;">Order Date</th>
                <th style="padding: 14px 18px;">Expected Delivery</th>
                <th style="padding: 14px 18px;">Total Cost</th>
                <th style="padding: 14px 18px;">Status</th>
                <th style="padding: 14px 18px; text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchaseOrders as $po)
            @php
                $statusColors = [
                    'draft' => ['bg' => 'var(--bg-main)', 'color' => 'var(--text-muted)'],
                    'ordered' => ['bg' => 'var(--warning-light)', 'color' => 'var(--warning)'],
                    'received' => ['bg' => 'var(--success-light)', 'color' => 'var(--success)'],
                    'cancelled' => ['bg' => 'var(--danger-light)', 'color' => 'var(--danger)'],
                ];
                $sc = $statusColors[$po->status] ?? ['bg' => 'var(--bg-main)', 'color' => 'var(--text-muted)'];
            @endphp
            <tr style="border-top: 1px solid var(--border);">
                <td style="padding: 14px 18px;">
                    <a href="{{ route('purchase-orders.show', $po) }}" style="font-weight: 700; font-family: monospace; color: var(--primary);">
                        {{ $po->po_number }}
                    </a>
                </td>
                <td style="padding: 14px 18px; font-weight: 600;">
                    {{ $po->supplier->name ?? 'Unknown' }}
                </td>
                <td style="padding: 14px 18px; font-size: 13px;">
                    {{ $po->order_date->format('M d, Y') }}
                </td>
                <td style="padding: 14px 18px; font-size: 13px; color: var(--text-muted);">
                    {{ $po->expected_delivery_date ? $po->expected_delivery_date->format('M d, Y') : '—' }}
                </td>
                <td style="padding: 14px 18px; font-weight: 700; font-size: 14px;">
                    ₱{{ number_format($po->total_amount, 2) }}
                </td>
                <td style="padding: 14px 18px;">
                    <span class="badge" style="background: {{ $sc['bg'] }}; color: {{ $sc['color'] }}; text-transform: uppercase; font-size: 11px;">
                        {{ $po->status }}
                    </span>
                </td>
                <td style="padding: 14px 18px; text-align: right;">
                    <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-eye"></i> View PO
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding: 40px; text-align: center; color: var(--text-muted);">
                    No purchase orders found. Click "Create Purchase Order" to replenish clinic supplies.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($purchaseOrders->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
            {{ $purchaseOrders->links() }}
        </div>
    @endif
</div>
@endsection
