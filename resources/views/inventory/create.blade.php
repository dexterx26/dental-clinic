@extends('layouts.app')

@section('title', 'Add Supply Item')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Add Dental Supply Item</h1>
        <p class="page-subtitle">Register a new dental consumable, instrument, or material into inventory</p>
    </div>
    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Back to Inventory</span>
    </a>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto; padding: 28px;">
    <form action="{{ route('inventory.store') }}" method="POST">
        @csrf

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label class="form-label">Product / Material Name <span style="color: var(--danger);">*</span></label>
                <input type="text" name="product_name" class="form-control" placeholder="e.g., Filtek Z250 Universal Restorative Composite" value="{{ old('product_name') }}" required>
            </div>
            <div>
                <label class="form-label">SKU / Item Code</label>
                <input type="text" name="item_code" class="form-control" placeholder="Auto-generated if blank" value="{{ old('item_code') }}">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label class="form-label">Category <span style="color: var(--danger);">*</span></label>
                <select name="category" class="form-control" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Brand / Manufacturer</label>
                <input type="text" name="brand" class="form-control" placeholder="e.g., 3M ESPE, Dentsply Sirona, GC" value="{{ old('brand') }}">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label class="form-label">Primary Supplier</label>
                <select name="supplier_id" class="form-control">
                    <option value="">-- Select Supplier --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Unit of Measure <span style="color: var(--danger);">*</span></label>
                <select name="unit" class="form-control" required>
                    <option value="piece">Piece (pc)</option>
                    <option value="box">Box (bx)</option>
                    <option value="bottle">Bottle (btl)</option>
                    <option value="pack">Pack (pk)</option>
                    <option value="tube">Tube (tb)</option>
                    <option value="syringe">Syringe (syr)</option>
                    <option value="kit">Kit (kt)</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label class="form-label">Cost Price (₱) <span style="color: var(--danger);">*</span></label>
                <input type="number" step="0.01" name="cost_price" class="form-control" placeholder="0.00" value="{{ old('cost_price', '0.00') }}" required>
            </div>
            <div>
                <label class="form-label">Selling / Charging Price (₱)</label>
                <input type="number" step="0.01" name="selling_price" class="form-control" placeholder="0.00" value="{{ old('selling_price', '0.00') }}">
                <small style="color: var(--text-muted); font-size: 11px;">If charged separately to patient on billing</small>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label class="form-label">Initial Stock Level <span style="color: var(--danger);">*</span></label>
                <input type="number" name="stock_level" class="form-control" min="0" value="{{ old('stock_level', 10) }}" required>
            </div>
            <div>
                <label class="form-label">Minimum Stock Alert Threshold <span style="color: var(--danger);">*</span></label>
                <input type="number" name="min_stock_level" class="form-control" min="1" value="{{ old('min_stock_level', 5) }}" required>
                <small style="color: var(--text-muted); font-size: 11px;">Triggers low-stock warning when reached</small>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
            <div>
                <label class="form-label">Batch / Lot Number</label>
                <input type="text" name="batch_number" class="form-control" placeholder="e.g., LOT-2026-904" value="{{ old('batch_number') }}">
            </div>
            <div>
                <label class="form-label">Expiration Date</label>
                <input type="date" name="expiration_date" class="form-control" value="{{ old('expiration_date') }}">
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-check"></i> Save Supply Item
            </button>
        </div>
    </form>
</div>
@endsection
