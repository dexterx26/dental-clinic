@extends('layouts.app')

@section('title', 'Dental Supply Vendors & Suppliers')

@section('content')
<div class="content-header">
    <div>
        <h1 class="page-title">Dental Supply Vendors</h1>
        <p class="page-subtitle">Manage medical distributors, dental depots, and material manufacturers</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="openSupplierModal()">
        <i class="fa-solid fa-plus"></i>
        <span>Add Supplier</span>
    </button>
</div>

<!-- Search Bar -->
<div class="card" style="padding: 16px; margin-bottom: 20px;">
    <form action="{{ route('suppliers.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center;">
        <div style="flex: 1; position: relative;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 12px; color: var(--text-light);"></i>
            <input type="text" name="search" class="form-control" style="padding-left: 36px;" placeholder="Search vendor name, contact person, phone, or email..." value="{{ request('search') }}">
        </div>
        <button type="submit" class="btn btn-secondary">Search</button>
        @if(request('search'))
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i></a>
        @endif
    </form>
</div>

<!-- Suppliers Table -->
<div class="card" style="overflow: hidden;">
    <table class="table" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: var(--bg-main); text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">
                <th style="padding: 14px 18px;">Supplier / Company</th>
                <th style="padding: 14px 18px;">Contact Person</th>
                <th style="padding: 14px 18px;">Phone & Email</th>
                <th style="padding: 14px 18px;">Catalog Items</th>
                <th style="padding: 14px 18px;">Purchase Orders</th>
                <th style="padding: 14px 18px;">Status</th>
                <th style="padding: 14px 18px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suppliers as $supplier)
            <tr style="border-top: 1px solid var(--border);">
                <td style="padding: 14px 18px;">
                    <div style="font-weight: 600; color: var(--text-main);">{{ $supplier->name }}</div>
                    @if($supplier->address)
                        <div style="font-size: 12px; color: var(--text-muted);">{{ Str::limit($supplier->address, 45) }}</div>
                    @endif
                </td>
                <td style="padding: 14px 18px; font-size: 13px;">
                    {{ $supplier->contact_person ?: '—' }}
                </td>
                <td style="padding: 14px 18px; font-size: 13px;">
                    @if($supplier->phone)
                        <div><i class="fa-solid fa-phone" style="font-size: 11px; color: var(--text-light); margin-right: 4px;"></i> {{ $supplier->phone }}</div>
                    @endif
                    @if($supplier->email)
                        <div style="color: var(--text-muted); font-size: 12px;"><i class="fa-solid fa-envelope" style="font-size: 11px; color: var(--text-light); margin-right: 4px;"></i> {{ $supplier->email }}</div>
                    @endif
                </td>
                <td style="padding: 14px 18px;">
                    <span class="badge" style="background: var(--primary-light); color: var(--primary);">
                        {{ $supplier->inventory_items_count }} products
                    </span>
                </td>
                <td style="padding: 14px 18px;">
                    <span class="badge" style="background: var(--secondary-light); color: var(--secondary);">
                        {{ $supplier->purchase_orders_count }} POs
                    </span>
                </td>
                <td style="padding: 14px 18px;">
                    <span class="badge" style="background: {{ $supplier->is_active ? 'var(--success-light)' : 'var(--danger-light)' }}; color: {{ $supplier->is_active ? 'var(--success)' : 'var(--danger)' }};">
                        {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td style="padding: 14px 18px; text-align: right;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="editSupplier({{ json_encode($supplier) }})">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="padding: 40px; text-align: center; color: var(--text-muted);">
                    No suppliers found. Click "Add Supplier" to register your clinic's vendors.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($suppliers->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
            {{ $suppliers->links() }}
        </div>
    @endif
</div>

<!-- Modal: Add / Edit Supplier -->
<div id="supplierModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 520px; padding: 24px; box-shadow: var(--shadow-lg);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <h3 id="supplierModalTitle" style="font-size: 18px; margin: 0;">Add Supplier</h3>
            <button type="button" onclick="closeSupplierModal()" style="border: none; background: transparent; font-size: 18px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form id="supplierForm" action="{{ route('suppliers.store') }}" method="POST">
            @csrf
            <div id="methodSpoof"></div>

            <div style="margin-bottom: 14px;">
                <label class="form-label">Company / Supplier Name <span style="color: var(--danger);">*</span></label>
                <input type="text" name="name" id="supplierName" class="form-control" required placeholder="e.g., Metro Dental Supply Manila">
            </div>

            <div style="margin-bottom: 14px;">
                <label class="form-label">Contact Person</label>
                <input type="text" name="contact_person" id="supplierContactPerson" class="form-control" placeholder="e.g., Maria Santos (Sales Executive)">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" id="supplierPhone" class="form-control" placeholder="0917-000-0000">
                </div>
                <div>
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" id="supplierEmail" class="form-control" placeholder="orders@supplier.com">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label class="form-label">Address</label>
                <textarea name="address" id="supplierAddress" class="form-control" rows="2" placeholder="Office / Warehouse address"></textarea>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label">Notes & Terms</label>
                <textarea name="notes" id="supplierNotes" class="form-control" rows="2" placeholder="Payment terms (e.g. Net 30), minimum order requirements"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeSupplierModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="supplierSubmitBtn">Save Supplier</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openSupplierModal() {
    const modal = document.getElementById('supplierModal');
    const form = document.getElementById('supplierForm');
    form.action = "{{ route('suppliers.store') }}";
    document.getElementById('methodSpoof').innerHTML = '';
    document.getElementById('supplierModalTitle').textContent = 'Add Dental Supply Vendor';
    document.getElementById('supplierSubmitBtn').textContent = 'Save Supplier';
    document.getElementById('supplierName').value = '';
    document.getElementById('supplierContactPerson').value = '';
    document.getElementById('supplierPhone').value = '';
    document.getElementById('supplierEmail').value = '';
    document.getElementById('supplierAddress').value = '';
    document.getElementById('supplierNotes').value = '';
    modal.style.display = 'flex';
}

function editSupplier(supplier) {
    const modal = document.getElementById('supplierModal');
    const form = document.getElementById('supplierForm');
    form.action = `/suppliers/${supplier.id}`;
    document.getElementById('methodSpoof').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('supplierModalTitle').textContent = 'Edit Supplier: ' + supplier.name;
    document.getElementById('supplierSubmitBtn').textContent = 'Update Supplier';
    document.getElementById('supplierName').value = supplier.name || '';
    document.getElementById('supplierContactPerson').value = supplier.contact_person || '';
    document.getElementById('supplierPhone').value = supplier.phone || '';
    document.getElementById('supplierEmail').value = supplier.email || '';
    document.getElementById('supplierAddress').value = supplier.address || '';
    document.getElementById('supplierNotes').value = supplier.notes || '';
    modal.style.display = 'flex';
}

function closeSupplierModal() {
    document.getElementById('supplierModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('supplierModal');
    if (event.target === modal) {
        closeSupplierModal();
    }
}
</script>
@endpush
@endsection
