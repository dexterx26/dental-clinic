@extends('layouts.app')

@section('title', 'Edit Supply Item')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Edit Supply Item: {{ $item->product_name }}</h1>
        <p class="page-subtitle">Item Code: <strong style="color: var(--primary);">{{ $item->item_code }}</strong></p>
    </div>
    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Back to Inventory</span>
    </a>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto; padding: 28px;">
    <form action="{{ route('inventory.update', $item) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label class="form-label">Product / Material Name <span style="color: var(--danger);">*</span></label>
                <input type="text" name="product_name" class="form-control" value="{{ old('product_name', $item->product_name) }}" required>
            </div>
            <div>
                <label class="form-label">SKU / Item Code</label>
                <input type="text" class="form-control" value="{{ $item->item_code }}" disabled style="background: var(--bg-main);">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label class="form-label">Category <span style="color: var(--danger);">*</span></label>
                <select name="category" class="form-control" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category', $item->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Brand / Manufacturer</label>
                <input type="text" name="brand" class="form-control" value="{{ old('brand', $item->brand) }}">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label class="form-label">Primary Supplier</label>
                <select name="supplier_id" class="form-control">
                    <option value="">-- Select Supplier --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id', $item->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Unit of Measure <span style="color: var(--danger);">*</span></label>
                <select name="unit" class="form-control" required>
                    <option value="piece" {{ $item->unit == 'piece' ? 'selected' : '' }}>Piece (pc)</option>
                    <option value="box" {{ $item->unit == 'box' ? 'selected' : '' }}>Box (bx)</option>
                    <option value="bottle" {{ $item->unit == 'bottle' ? 'selected' : '' }}>Bottle (btl)</option>
                    <option value="pack" {{ $item->unit == 'pack' ? 'selected' : '' }}>Pack (pk)</option>
                    <option value="tube" {{ $item->unit == 'tube' ? 'selected' : '' }}>Tube (tb)</option>
                    <option value="syringe" {{ $item->unit == 'syringe' ? 'selected' : '' }}>Syringe (syr)</option>
                    <option value="kit" {{ $item->unit == 'kit' ? 'selected' : '' }}>Kit (kt)</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label class="form-label">Cost Price (₱) <span style="color: var(--danger);">*</span></label>
                <input type="number" step="0.01" name="cost_price" class="form-control" value="{{ old('cost_price', $item->cost_price) }}" required>
            </div>
            <div>
                <label class="form-label">Selling / Charging Price (₱)</label>
                <input type="number" step="0.01" name="selling_price" class="form-control" value="{{ old('selling_price', $item->selling_price) }}">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label class="form-label">Current Stock Level</label>
                <div style="padding: 10px 14px; background: var(--bg-main); border-radius: var(--radius-sm); font-weight: 700; font-size: 16px;">
                    {{ $item->stock_level }} {{ $item->unit }}(s)
                    <span style="font-size: 12px; font-weight: 400; color: var(--text-muted); margin-left: 8px;">(Use Stock Adjustment modal to modify stock)</span>
                </div>
            </div>
            <div>
                <label class="form-label">Minimum Stock Alert Threshold <span style="color: var(--danger);">*</span></label>
                <input type="number" name="min_stock_level" class="form-control" min="1" value="{{ old('min_stock_level', $item->min_stock_level) }}" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
            <div>
                <label class="form-label">Batch / Lot Number</label>
                <input type="text" name="batch_number" class="form-control" value="{{ old('batch_number', $item->batch_number) }}">
            </div>
            <div>
                <label class="form-label">Expiration Date</label>
                <input type="date" name="expiration_date" class="form-control" value="{{ old('expiration_date', $item->expiration_date ? $item->expiration_date->format('Y-m-d') : '') }}">
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-check"></i> Update Item
            </button>
        </div>
    </form>
</div>
@endsection
