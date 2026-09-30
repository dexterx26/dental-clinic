@extends('layouts.app')

@section('title', 'Create Purchase Order')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Create Purchase Order</h1>
        <p class="page-subtitle">Reorder clinical supplies, restorative materials, and consumables</p>
    </div>
    <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Back to Orders</span>
    </a>
</div>

<div class="card" style="max-width: 900px; margin: 0 auto; padding: 28px;">
    <form action="{{ route('purchase-orders.store') }}" method="POST" id="poForm">
        @csrf

        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px; margin-bottom: 20px;">
            <div>
                <label class="form-label">Dental Supplier / Vendor <span style="color: var(--danger);">*</span></label>
                <select name="supplier_id" class="form-control" required>
                    <option value="">-- Choose Supplier --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Order Date <span style="color: var(--danger);">*</span></label>
                <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div>
                <label class="form-label">Expected Delivery</label>
                <input type="date" name="expected_delivery_date" class="form-control" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
            </div>
        </div>

        <div style="margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="font-size: 16px; margin: 0;"><i class="fa-solid fa-list-check text-primary"></i> Order Items</h3>
                <button type="button" class="btn btn-secondary btn-sm" onclick="addItemRow()">
                    <i class="fa-solid fa-plus"></i> Add Line Item
                </button>
            </div>

            <div style="border: 1px solid var(--border); border-radius: var(--radius-md); overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--bg-main); text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">
                            <th style="padding: 10px 14px;">Supply Item</th>
                            <th style="padding: 10px 14px; width: 120px;">Quantity</th>
                            <th style="padding: 10px 14px; width: 140px;">Unit Cost (₱)</th>
                            <th style="padding: 10px 14px; width: 140px;">Subtotal (₱)</th>
                            <th style="padding: 10px 14px; width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        <tr class="item-row" style="border-top: 1px solid var(--border);">
                            <td style="padding: 10px 14px;">
                                <select name="items[0][inventory_item_id]" class="form-control item-select" required onchange="onItemSelect(this, 0)">
                                    <option value="">-- Choose Item --</option>
                                    @foreach($inventoryItems as $item)
                                        <option value="{{ $item->id }}" data-cost="{{ $item->cost_price }}" data-unit="{{ $item->unit }}">
                                            {{ $item->product_name }} ({{ $item->item_code }}) - Stock: {{ $item->stock_level }} {{ $item->unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="padding: 10px 14px;">
                                <input type="number" name="items[0][quantity_ordered]" class="form-control item-qty" min="1" value="1" required oninput="calculateTotals()">
                            </td>
                            <td style="padding: 10px 14px;">
                                <input type="number" step="0.01" name="items[0][unit_cost]" class="form-control item-cost" value="0.00" required oninput="calculateTotals()">
                            </td>
                            <td style="padding: 10px 14px; font-weight: 700; color: var(--text-main);" class="item-subtotal">
                                ₱0.00
                            </td>
                            <td style="padding: 10px 14px; text-align: center;">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="removeItemRow(this)" style="padding: 4px 8px; color: var(--danger);">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background: var(--bg-main); font-weight: 700;">
                            <td colspan="3" style="padding: 12px 14px; text-align: right;">Estimated Total:</td>
                            <td colspan="2" style="padding: 12px 14px; font-size: 16px; color: var(--primary);" id="orderGrandTotal">₱0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div style="margin-bottom: 24px;">
            <label class="form-label">Order Notes & Special Instructions</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="e.g. Urgent delivery needed, deliver to Ground Floor reception, Net 30 payment terms"></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Submit & Issue Purchase Order
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let rowIndex = 1;
const inventoryData = @json($inventoryItems);

function addItemRow() {
    const tbody = document.getElementById('itemsTableBody');
    const tr = document.createElement('tr');
    tr.className = 'item-row';
    tr.style.borderTop = '1px solid var(--border)';

    let options = '<option value="">-- Choose Item --</option>';
    inventoryData.forEach(item => {
        options += `<option value="${item.id}" data-cost="${item.cost_price}" data-unit="${item.unit}">${item.product_name} (${item.item_code}) - Stock: ${item.stock_level}</option>`;
    });

    tr.innerHTML = `
        <td style="padding: 10px 14px;">
            <select name="items[${rowIndex}][inventory_item_id]" class="form-control item-select" required onchange="onItemSelect(this, ${rowIndex})">
                ${options}
            </select>
        </td>
        <td style="padding: 10px 14px;">
            <input type="number" name="items[${rowIndex}][quantity_ordered]" class="form-control item-qty" min="1" value="1" required oninput="calculateTotals()">
        </td>
        <td style="padding: 10px 14px;">
            <input type="number" step="0.01" name="items[${rowIndex}][unit_cost]" class="form-control item-cost" value="0.00" required oninput="calculateTotals()">
        </td>
        <td style="padding: 10px 14px; font-weight: 700; color: var(--text-main);" class="item-subtotal">
            ₱0.00
        </td>
        <td style="padding: 10px 14px; text-align: center;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="removeItemRow(this)" style="padding: 4px 8px; color: var(--danger);">
                <i class="fa-solid fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    rowIndex++;
}

function removeItemRow(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length > 1) {
        btn.closest('tr').remove();
        calculateTotals();
    } else {
        alert('Purchase order must contain at least one item.');
    }
}

function onItemSelect(selectElement, index) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const cost = selectedOption.getAttribute('data-cost') || '0.00';
    const row = selectElement.closest('tr');
    row.querySelector('.item-cost').value = parseFloat(cost).toFixed(2);
    calculateTotals();
}

function calculateTotals() {
    let grandTotal = 0;
    const rows = document.querySelectorAll('.item-row');
    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const cost = parseFloat(row.querySelector('.item-cost').value) || 0;
        const subtotal = qty * cost;
        grandTotal += subtotal;
        row.querySelector('.item-subtotal').textContent = '₱' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    });
    document.getElementById('orderGrandTotal').textContent = '₱' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
@endpush
@endsection
