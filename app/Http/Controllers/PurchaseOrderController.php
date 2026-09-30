<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'createdBy'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchaseOrders = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('purchase_orders.index', compact('purchaseOrders', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $inventoryItems = InventoryItem::where('is_active', true)->orderBy('product_name')->get();
        return view('purchase_orders.create', compact('suppliers', 'inventoryItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $poNumber = 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        DB::transaction(function () use ($validated, $poNumber, &$po) {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity_ordered'] * $item['unit_cost'];
            }

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $validated['supplier_id'],
                'created_by_id' => auth()->id(),
                'order_date' => $validated['order_date'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'status' => 'ordered',
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'inventory_item_id' => $itemData['inventory_item_id'],
                    'quantity_ordered' => $itemData['quantity_ordered'],
                    'unit_cost' => $itemData['unit_cost'],
                    'total_cost' => $itemData['quantity_ordered'] * $itemData['unit_cost'],
                ]);
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'purchase_order_created',
                'model_type' => PurchaseOrder::class,
                'model_id' => $po->id,
                'description' => "Created purchase order {$po->po_number} with total ₱" . number_format($totalAmount, 2),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        return redirect()->route('purchase-orders.show', $po)->with('success', "Purchase order {$po->po_number} issued successfully.");
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'createdBy', 'items.inventoryItem']);
        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function markReceived(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status === 'received') {
            return back()->with('error', 'This purchase order has already been marked as received.');
        }

        DB::transaction(function () use ($purchaseOrder) {
            $purchaseOrder->update([
                'status' => 'received',
                'received_date' => Carbon::today(),
            ]);

            // Automatically increment stock levels and record movements
            foreach ($purchaseOrder->items as $item) {
                $inv = $item->inventoryItem;
                $prevStock = $inv->stock_level;
                $newStock = $prevStock + $item->quantity_ordered;

                $inv->update(['stock_level' => $newStock]);
                $item->update(['quantity_received' => $item->quantity_ordered]);

                StockMovement::create([
                    'inventory_item_id' => $inv->id,
                    'type' => 'received',
                    'quantity' => $item->quantity_ordered,
                    'previous_stock' => $prevStock,
                    'new_stock' => $newStock,
                    'reference_type' => 'purchase_order',
                    'reference_id' => $purchaseOrder->id,
                    'notes' => "Received from PO {$purchaseOrder->po_number}",
                    'user_id' => auth()->id(),
                ]);
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'purchase_order_received',
                'model_type' => PurchaseOrder::class,
                'model_id' => $purchaseOrder->id,
                'description' => "Received and stocked purchase order {$purchaseOrder->po_number}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        return back()->with('success', "Purchase order {$purchaseOrder->po_number} received and inventory stock automatically replenished.");
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,submitted,ordered,received,cancelled',
        ]);

        if ($validated['status'] === 'received') {
            return $this->markReceived($request, $purchaseOrder);
        }

        $purchaseOrder->update(['status' => $validated['status']]);

        return back()->with('success', "Purchase order status updated to {$validated['status']}.");
    }
}
