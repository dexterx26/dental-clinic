@extends('layouts.app')

@section('title', 'Stock Movements - ' . $inventory->product_name)

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Stock Movement History</h1>
        <p class="page-subtitle">{{ $inventory->product_name }} ({{ $inventory->item_code }}) &bull; Current Stock: <strong style="color: var(--primary);">{{ $inventory->stock_level }} {{ $inventory->unit }}(s)</strong></p>
    </div>
    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Back to Inventory</span>
    </a>
</div>

<div class="card" style="overflow: hidden;">
    <table class="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: var(--bg-main); text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">
                <th style="padding: 14px 18px;">Date / Time</th>
                <th style="padding: 14px 18px;">Type</th>
                <th style="padding: 14px 18px;">Quantity Change</th>
                <th style="padding: 14px 18px;">Previous &rarr; New Stock</th>
                <th style="padding: 14px 18px;">Notes & Reference</th>
                <th style="padding: 14px 18px;">Recorded By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $m)
            @php
                $typeColors = [
                    'received' => ['bg' => 'var(--success-light)', 'color' => 'var(--success)', 'icon' => 'fa-arrow-down'],
                    'used' => ['bg' => 'var(--primary-light)', 'color' => 'var(--primary)', 'icon' => 'fa-tooth'],
                    'damaged' => ['bg' => 'var(--warning-light)', 'color' => 'var(--warning)', 'icon' => 'fa-triangle-exclamation'],
                    'expired' => ['bg' => 'var(--danger-light)', 'color' => 'var(--danger)', 'icon' => 'fa-calendar-xmark'],
                    'returned' => ['bg' => 'var(--info-light)', 'color' => 'var(--info)', 'icon' => 'fa-reply'],
                    'adjusted' => ['bg' => 'var(--bg-main)', 'color' => 'var(--text-muted)', 'icon' => 'fa-sliders'],
                ];
                $c = $typeColors[$m->type] ?? ['bg' => 'var(--bg-main)', 'color' => 'var(--text-muted)', 'icon' => 'fa-circle'];
            @endphp
            <tr style="border-top: 1px solid var(--border);">
                <td style="padding: 14px 18px; font-size: 13px;">
                    <div>{{ $m->created_at->format('M d, Y') }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ $m->created_at->format('h:i A') }}</div>
                </td>
                <td style="padding: 14px 18px;">
                    <span class="badge" style="background: {{ $c['bg'] }}; color: {{ $c['color'] }}; font-size: 11px;">
                        <i class="fa-solid {{ $c['icon'] }}"></i> {{ ucfirst($m->type) }}
                    </span>
                </td>
                <td style="padding: 14px 18px; font-weight: 700; font-size: 14px; color: {{ $m->quantity > 0 ? 'var(--success)' : 'var(--danger)' }};">
                    {{ $m->quantity > 0 ? '+' . $m->quantity : $m->quantity }} {{ $inventory->unit }}(s)
                </td>
                <td style="padding: 14px 18px; font-size: 13px;">
                    <span style="color: var(--text-muted);">{{ $m->previous_stock }}</span>
                    <i class="fa-solid fa-arrow-right" style="font-size: 10px; margin: 0 4px; color: var(--text-light);"></i>
                    <strong style="color: var(--text-main);">{{ $m->new_stock }}</strong>
                </td>
                <td style="padding: 14px 18px; font-size: 13px; color: var(--text-muted);">
                    <div>{{ $m->notes ?: 'No additional notes' }}</div>
                    @if($m->reference_type)
                        <span style="font-size: 11px; font-family: monospace; background: var(--bg-main); padding: 2px 6px; border-radius: 4px;">
                            Ref: {{ $m->reference_type }} #{{ $m->reference_id ?? 'N/A' }}
                        </span>
                    @endif
                </td>
                <td style="padding: 14px 18px; font-size: 13px;">
                    {{ $m->user->name ?? 'System' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding: 40px; text-align: center; color: var(--text-muted);">
                    No stock movements recorded for this item yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($movements->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
            {{ $movements->links() }}
        </div>
    @endif
</div>
@endsection
