@extends('layouts.app')

@section('title', 'Supply Inventory & Materials')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Dental Supply Inventory</h1>
        <p class="page-subtitle">Track consumables, restorative materials, clinical supplies, and expiration dates</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('purchase-orders.create') }}" class="btn btn-secondary">
            <i class="fa-solid fa-cart-plus"></i>
            <span>Create Purchase Order</span>
        </a>
        <a href="{{ route('inventory.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i>
            <span>New Supply Item</span>
        </a>
    </div>
</div>

<!-- KPI Metric Cards -->
<div class="metric-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="metric-card card" style="padding: 18px; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">Total Active Items</div>
            <div style="font-size: 24px; font-weight: 700; color: var(--text-main);">{{ $totalItems }}</div>
        </div>
    </div>

    <a href="{{ route('inventory.index', ['filter' => 'low_stock']) }}" class="metric-card card" style="padding: 18px; display: flex; align-items: center; gap: 16px; border-left: 4px solid var(--danger);">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--danger-light); color: var(--danger); display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--danger); font-weight: 600;">Low Stock Alert</div>
            <div style="font-size: 24px; font-weight: 700; color: var(--danger);">{{ $lowStockCount }} Items</div>
        </div>
    </a>

    <a href="{{ route('inventory.index', ['filter' => 'expiring']) }}" class="metric-card card" style="padding: 18px; display: flex; align-items: center; gap: 16px; border-left: 4px solid var(--warning);">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--warning-light); color: var(--warning); display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-calendar-xmark"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--warning); font-weight: 600;">Expiring Soon (&le;60d)</div>
            <div style="font-size: 24px; font-weight: 700; color: var(--warning);">{{ $expiringCount }} Items</div>
        </div>
    </a>

    <div class="metric-card card" style="padding: 18px; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--secondary-light); color: var(--secondary); display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="fa-solid fa-peso-sign"></i>
        </div>
        <div>
            <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">Inventory Valuation</div>
            <div style="font-size: 22px; font-weight: 700; color: var(--text-main);">₱{{ number_format($totalValuation, 2) }}</div>
        </div>
    </div>
</div>

<!-- Filters & Search Toolbar -->
<div class="card" style="padding: 16px; margin-bottom: 20px;">
    <form action="{{ route('inventory.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
        <div style="flex: 1; min-width: 240px; position: relative;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 12px; color: var(--text-light);"></i>
            <input type="text" name="search" class="form-control" style="padding-left: 36px;" placeholder="Search SKU code, item name, brand..." value="{{ request('search') }}">
        </div>

        <div style="width: 180px;">
            <select name="category" class="form-control" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>

        <div style="width: 170px;">
            <select name="filter" class="form-control" onchange="this.form.submit()">
                <option value="">All Stock Status</option>
                <option value="low_stock" {{ request('filter') == 'low_stock' ? 'selected' : '' }}>⚠️ Low Stock</option>
                <option value="expiring" {{ request('filter') == 'expiring' ? 'selected' : '' }}>⏳ Expiring Soon</option>
                <option value="expired" {{ request('filter') == 'expired' ? 'selected' : '' }}>❌ Expired</option>
            </select>
        </div>

        <button type="submit" class="btn btn-secondary">Filter</button>
        @if(request()->anyFilled(['search', 'category', 'filter']))
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary" title="Clear filters">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        @endif
    </form>
</div>

<!-- Inventory Table Card -->
<div class="card" style="overflow: hidden;">
    <table class="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: var(--bg-main); text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">
                <th style="padding: 14px 18px;">Code / Item</th>
                <th style="padding: 14px 18px;">Category / Brand</th>
                <th style="padding: 14px 18px;">Supplier</th>
                <th style="padding: 14px 18px;">Unit Cost</th>
                <th style="padding: 14px 18px;">Stock Level</th>
                <th style="padding: 14px 18px;">Batch / Expiry</th>
                <th style="padding: 14px 18px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            @php
                $isLow = $item->isLowStock();
                $isExpired = $item->isExpired();
                $isExpiring = $item->isExpiringSoon(60);
            @endphp
            <tr style="border-top: 1px solid var(--border); transition: background 0.15s ease;" onmouseover="this.style.background='var(--bg-main)'" onmouseout="this.style.background='transparent'">
                <td style="padding: 14px 18px;">
                    <div style="font-weight: 600; color: var(--text-main);">{{ $item->product_name }}</div>
                    <span style="font-family: monospace; font-size: 12px; background: var(--bg-main); padding: 2px 6px; border-radius: 4px; color: var(--primary);">
                        {{ $item->item_code }}
                    </span>
                </td>
                <td style="padding: 14px 18px;">
                    <span class="badge" style="background: var(--primary-light); color: var(--primary); font-size: 11px;">{{ $item->category }}</span>
                    @if($item->brand)
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ $item->brand }}</div>
                    @endif
                </td>
                <td style="padding: 14px 18px; font-size: 13px; color: var(--text-muted);">
                    {{ $item->supplier->name ?? 'None' }}
                </td>
                <td style="padding: 14px 18px;">
                    <div style="font-weight: 600; font-size: 13px;">₱{{ number_format($item->cost_price, 2) }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">per {{ $item->unit }}</div>
                </td>
                <td style="padding: 14px 18px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 16px; font-weight: 700; color: {{ $isLow ? 'var(--danger)' : 'var(--text-main)' }};">
                            {{ $item->stock_level }}
                        </span>
                        <span style="font-size: 12px; color: var(--text-muted);">{{ $item->unit }}(s)</span>
                    </div>
                    @if($isLow)
                        <span class="badge" style="background: var(--danger-light); color: var(--danger); font-size: 10px; padding: 2px 6px; margin-top: 3px;">
                            Min: {{ $item->min_stock_level }} (Reorder)
                        </span>
                    @endif
                </td>
                <td style="padding: 14px 18px; font-size: 12px;">
                    @if($item->batch_number)
                        <div><strong style="color: var(--text-muted);">Lot:</strong> {{ $item->batch_number }}</div>
                    @endif
                    @if($item->expiration_date)
                        @if($isExpired)
                            <span class="badge" style="background: var(--danger-light); color: var(--danger);">
                                Expired {{ $item->expiration_date->format('M d, Y') }}
                            </span>
                        @elseif($isExpiring)
                            <span class="badge" style="background: var(--warning-light); color: var(--warning);">
                                Exp: {{ $item->expiration_date->format('M d, Y') }}
                            </span>
                        @else
                            <span style="color: var(--text-muted);">Exp: {{ $item->expiration_date->format('M d, Y') }}</span>
                        @endif
                    @else
                        <span style="color: var(--text-light);">No expiry</span>
                    @endif
                </td>
                <td style="padding: 14px 18px; text-align: right;">
                    <div style="display: inline-flex; gap: 6px;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="openStockModal({{ $item->id }}, '{{ addslashes($item->product_name) }}', {{ $item->stock_level }}, '{{ $item->unit }}')" title="Adjust Stock">
                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                        </button>
                        <a href="{{ route('inventory.movements', $item) }}" class="btn btn-secondary btn-sm" title="Movement Audit Trail">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </a>
                        <a href="{{ route('inventory.edit', $item) }}" class="btn btn-secondary btn-sm" title="Edit Item">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding: 40px; text-align: center; color: var(--text-muted);">
                    <i class="fa-solid fa-boxes-packing" style="font-size: 40px; color: var(--border); margin-bottom: 12px; display: block;"></i>
                    No inventory supplies found matching your criteria.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($items->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
            {{ $items->links() }}
        </div>
    @endif
</div>

<!-- Modal: Quick Stock Adjustment -->
<div id="stockModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 480px; padding: 24px; box-shadow: var(--shadow-lg);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <h3 style="font-size: 18px; margin: 0;"><i class="fa-solid fa-sliders text-primary"></i> Adjust Inventory Stock</h3>
            <button type="button" onclick="closeStockModal()" style="border: none; background: transparent; font-size: 18px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form id="stockAdjustForm" method="POST">
            @csrf
            <div style="margin-bottom: 14px;">
                <label style="font-size: 13px; font-weight: 600; color: var(--text-muted);">Item</label>
                <div id="modalItemName" style="font-size: 15px; font-weight: 700; color: var(--text-main); margin-top: 2px;"></div>
                <div style="font-size: 13px; color: var(--text-muted);">Current level: <strong id="modalCurrentStock">0</strong></div>
            </div>

            <div style="margin-bottom: 14px;">
                <label class="form-label">Adjustment Type <span style="color: var(--danger);">*</span></label>
                <select name="type" class="form-control" required>
                    <option value="received">➕ Stock Received / Restocked</option>
                    <option value="used">➖ Clinical Procedure Consumption</option>
                    <option value="damaged">⚠️ Damaged / Contaminated</option>
                    <option value="expired">⌛ Expired Disposal</option>
                    <option value="returned">↩️ Returned to Supplier</option>
                    <option value="adjusted">🔄 Physical Audit Count Correction</option>
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label class="form-label">Quantity to Adjust <span style="color: var(--danger);">*</span></label>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="number" name="quantity" class="form-control" min="1" value="1" required style="width: 120px;">
                    <span id="modalUnit" style="font-size: 13px; color: var(--text-muted);">pieces</span>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label">Reason / Clinical Notes</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="e.g., Used in endodontic procedures, physical count discrepancy, batch expiry"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeStockModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Adjustment</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openStockModal(itemId, itemName, currentStock, unit) {
    const modal = document.getElementById('stockModal');
    const form = document.getElementById('stockAdjustForm');
    form.action = `/inventory/${itemId}/adjust`;
    document.getElementById('modalItemName').textContent = itemName;
    document.getElementById('modalCurrentStock').textContent = `${currentStock} ${unit}(s)`;
    document.getElementById('modalUnit').textContent = unit + '(s)';
    modal.style.display = 'flex';
}

function closeStockModal() {
    document.getElementById('stockModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('stockModal');
    if (event.target === modal) {
        closeStockModal();
    }
}
</script>
@endpush
@endsection
