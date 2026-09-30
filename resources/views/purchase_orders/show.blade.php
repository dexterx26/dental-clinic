@extends('layouts.app')

@section('title', 'Purchase Order ' . $purchaseOrder->po_number)

@section('content')
<div class="content-header no-print">
    <div>
        <h1 class="page-title">Purchase Order: {{ $purchaseOrder->po_number }}</h1>
        <p class="page-subtitle">Issued to <strong style="color: var(--primary);">{{ $purchaseOrder->supplier->name }}</strong> on {{ $purchaseOrder->order_date->format('F d, Y') }}</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <button type="button" class="btn btn-secondary" onclick="window.print()">
            <i class="fa-solid fa-print"></i>
            <span>Print PO</span>
        </button>

        @if($purchaseOrder->status !== 'received')
        <form action="{{ route('purchase-orders.receive', $purchaseOrder) }}" method="POST" onsubmit="return confirm('Are you sure you want to mark this order as received? This will automatically replenish inventory stock levels for all items.')">
            @csrf
            <button type="submit" class="btn btn-primary" style="background: var(--success); border-color: var(--success);">
                <i class="fa-solid fa-boxes-packing"></i>
                <span>Receive & Stock Items</span>
            </button>
        </form>
        @endif

        <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Orders</span>
        </a>
    </div>
</div>

<div class="card" style="max-width: 900px; margin: 0 auto; padding: 36px; box-shadow: var(--shadow-md);">
    <!-- Document Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 24px; border-bottom: 2px solid var(--border-light); margin-bottom: 24px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <div style="width: 40px; height: 40px; background: var(--primary); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 20px;">
                    <i class="fa-solid fa-tooth"></i>
                </div>
                <div>
                    <h2 style="font-size: 20px; margin: 0;">BrightSmile Dental Clinic</h2>
                    <span style="font-size: 12px; color: var(--text-muted);">Dental Materials & Procurement</span>
                </div>
            </div>
            <div style="font-size: 13px; color: var(--text-muted); line-height: 1.4;">
                123 Healthcare Blvd, Medical Plaza Suite 400<br>
                Metro Manila, Philippines &bull; (02) 8123-4567<br>
                procurement@brightsmileclinic.ph
            </div>
        </div>

        <div style="text-align: right;">
            <div style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-light); font-weight: 700;">PURCHASE ORDER</div>
            <div style="font-size: 20px; font-weight: 800; font-family: monospace; color: var(--primary); margin: 4px 0;">
                {{ $purchaseOrder->po_number }}
            </div>
            @php
                $statusColors = [
                    'draft' => ['bg' => 'var(--bg-main)', 'color' => 'var(--text-muted)'],
                    'ordered' => ['bg' => 'var(--warning-light)', 'color' => 'var(--warning)'],
                    'received' => ['bg' => 'var(--success-light)', 'color' => 'var(--success)'],
                    'cancelled' => ['bg' => 'var(--danger-light)', 'color' => 'var(--danger)'],
                ];
                $sc = $statusColors[$purchaseOrder->status] ?? ['bg' => 'var(--bg-main)', 'color' => 'var(--text-muted)'];
            @endphp
            <span class="badge" style="background: {{ $sc['bg'] }}; color: {{ $sc['color'] }}; text-transform: uppercase; font-size: 11px; padding: 4px 8px;">
                Status: {{ $purchaseOrder->status }}
            </span>
        </div>
    </div>

    <!-- Vendor and Order Meta Info -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px; background: var(--bg-main); padding: 18px; border-radius: var(--radius-md);">
        <div>
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-light); margin-bottom: 6px;">VENDOR / SUPPLIER</div>
            <div style="font-size: 16px; font-weight: 700; color: var(--text-main);">{{ $purchaseOrder->supplier->name }}</div>
            <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
                @if($purchaseOrder->supplier->contact_person)
                    <div>Attn: {{ $purchaseOrder->supplier->contact_person }}</div>
                @endif
                @if($purchaseOrder->supplier->phone)
                    <div>Phone: {{ $purchaseOrder->supplier->phone }}</div>
                @endif
                @if($purchaseOrder->supplier->email)
                    <div>Email: {{ $purchaseOrder->supplier->email }}</div>
                @endif
                @if($purchaseOrder->supplier->address)
                    <div>Address: {{ $purchaseOrder->supplier->address }}</div>
                @endif
            </div>
        </div>

        <div>
            <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-light); margin-bottom: 6px;">ORDER DETAILS</div>
            <div style="font-size: 13px; color: var(--text-main); line-height: 1.6;">
                <div><strong>Order Date:</strong> {{ $purchaseOrder->order_date->format('F d, Y') }}</div>
                <div><strong>Expected Delivery:</strong> {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('F d, Y') : 'Standard Delivery' }}</div>
                @if($purchaseOrder->received_date)
                    <div><strong style="color: var(--success);">Received Date:</strong> {{ $purchaseOrder->received_date->format('F d, Y') }}</div>
                @endif
                <div><strong>Issued By:</strong> {{ $purchaseOrder->createdBy->name ?? 'Clinical Admin' }}</div>
            </div>
        </div>
    </div>

    <!-- Order Items Table -->
    <div style="margin-bottom: 28px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); text-align: left; font-size: 12px; text-transform: uppercase; color: var(--text-muted);">
                    <th style="padding: 10px 12px;">#</th>
                    <th style="padding: 10px 12px;">Item Description</th>
                    <th style="padding: 10px 12px;">SKU Code</th>
                    <th style="padding: 10px 12px; text-align: center;">Qty Ordered</th>
                    <th style="padding: 10px 12px; text-align: center;">Qty Received</th>
                    <th style="padding: 10px 12px; text-align: right;">Unit Cost</th>
                    <th style="padding: 10px 12px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $idx => $item)
                <tr style="border-bottom: 1px solid var(--border-light);">
                    <td style="padding: 12px; font-size: 13px; color: var(--text-light);">{{ $idx + 1 }}</td>
                    <td style="padding: 12px;">
                        <div style="font-weight: 600; color: var(--text-main);">{{ $item->inventoryItem->product_name ?? 'Item' }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $item->inventoryItem->brand ?? '' }} &bull; {{ $item->inventoryItem->category ?? '' }}</div>
                    </td>
                    <td style="padding: 12px; font-family: monospace; font-size: 12px; color: var(--primary);">
                        {{ $item->inventoryItem->item_code ?? '—' }}
                    </td>
                    <td style="padding: 12px; text-align: center; font-weight: 600;">
                        {{ $item->quantity_ordered }} {{ $item->inventoryItem->unit ?? '' }}
                    </td>
                    <td style="padding: 12px; text-align: center; font-weight: 600; color: {{ $item->quantity_received >= $item->quantity_ordered ? 'var(--success)' : 'var(--text-muted)' }};">
                        {{ $item->quantity_received }}
                    </td>
                    <td style="padding: 12px; text-align: right; font-size: 13px;">
                        ₱{{ number_format($item->unit_cost, 2) }}
                    </td>
                    <td style="padding: 12px; text-align: right; font-weight: 700; font-size: 14px; color: var(--text-main);">
                        ₱{{ number_format($item->total_cost, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="border-top: 2px solid var(--border);">
                    <td colspan="6" style="padding: 14px 12px; text-align: right; font-size: 14px; font-weight: 600;">Grand Total Cost:</td>
                    <td style="padding: 14px 12px; text-align: right; font-size: 18px; font-weight: 800; color: var(--primary);">₱{{ number_format($purchaseOrder->total_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($purchaseOrder->notes)
    <div style="margin-bottom: 24px; padding: 14px; background: var(--bg-main); border-radius: var(--radius-sm); font-size: 13px;">
        <strong style="color: var(--text-muted); display: block; margin-bottom: 4px;">Order Notes / Special Instructions:</strong>
        {{ $purchaseOrder->notes }}
    </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; padding-top: 20px; border-top: 1px solid var(--border-light);">
        <div>
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 40px;">Authorized Signature (Clinic Procurement):</div>
            <div style="border-bottom: 1px solid var(--text-light); width: 220px;"></div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">{{ $purchaseOrder->createdBy->name ?? 'Clinic Administrator' }}</div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 40px;">Received & Verified By:</div>
            <div style="border-bottom: 1px solid var(--text-light); width: 220px; margin-left: auto;"></div>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Dental Nurse / Stock Custodian</div>
        </div>
    </div>
</div>
@endsection
