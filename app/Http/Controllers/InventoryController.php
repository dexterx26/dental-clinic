<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::with('supplier')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filter === 'low_stock') {
            $query->whereColumn('stock_level', '<=', 'min_stock_level');
        } elseif ($request->filter === 'expiring') {
            $query->whereNotNull('expiration_date')
                  ->where('expiration_date', '>=', now())
                  ->where('expiration_date', '<=', now()->addDays(60));
        } elseif ($request->filter === 'expired') {
            $query->whereNotNull('expiration_date')
                  ->where('expiration_date', '<', now());
        }

        $items = $query->paginate(15)->withQueryString();

        // Summary Statistics
        $totalItems = InventoryItem::where('is_active', true)->count();
        $lowStockCount = InventoryItem::where('is_active', true)->whereColumn('stock_level', '<=', 'min_stock_level')->count();
        $expiringCount = InventoryItem::where('is_active', true)
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '>=', now())
            ->where('expiration_date', '<=', now()->addDays(60))
            ->count();
        $totalValuation = InventoryItem::where('is_active', true)->sum(DB::raw('stock_level * cost_price'));

        $categories = [
            'Restorative', 'Preventive', 'Surgical', 'Orthodontic', 
            'PPE', 'Anesthetic', 'Impression', 'Endodontic', 'Laboratory', 'Other'
        ];

        return view('inventory.index', compact('items', 'totalItems', 'lowStockCount', 'expiringCount', 'totalValuation', 'categories'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $categories = [
            'Restorative', 'Preventive', 'Surgical', 'Orthodontic', 
            'PPE', 'Anesthetic', 'Impression', 'Endodontic', 'Laboratory', 'Other'
        ];
        return view('inventory.create', compact('suppliers', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'item_code' => 'nullable|string|max:50|unique:inventory_items,item_code',
            'category' => 'required|string',
            'brand' => 'nullable|string|max:100',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:50',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'stock_level' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:1',
            'expiration_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:100',
        ]);

        if (empty($validated['item_code'])) {
            $prefix = strtoupper(substr($validated['category'], 0, 3));
            $count = InventoryItem::count() + 1;
            $validated['item_code'] = sprintf("%s-%04d", $prefix, $count);
        }

        $validated['selling_price'] = $validated['selling_price'] ?? 0;

        $item = InventoryItem::create($validated);

        // Record Initial Stock Movement if stock > 0
        if ($item->stock_level > 0) {
            StockMovement::create([
                'inventory_item_id' => $item->id,
                'type' => 'received',
                'quantity' => $item->stock_level,
                'previous_stock' => 0,
                'new_stock' => $item->stock_level,
                'reference_type' => 'initial_setup',
                'notes' => 'Initial stock intake upon item creation',
                'user_id' => auth()->id(),
            ]);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'inventory_created',
            'model_type' => InventoryItem::class,
            'model_id' => $item->id,
            'description' => "Added dental supply item: {$item->product_name} ({$item->item_code}) with initial stock of {$item->stock_level}",
            'new_values' => $item->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('inventory.index')->with('success', "Item {$item->product_name} created successfully.");
    }

    public function edit(InventoryItem $inventory)
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $categories = [
            'Restorative', 'Preventive', 'Surgical', 'Orthodontic', 
            'PPE', 'Anesthetic', 'Impression', 'Endodontic', 'Laboratory', 'Other'
        ];
        return view('inventory.edit', ['item' => $inventory, 'suppliers' => $suppliers, 'categories' => $categories]);
    }

    public function update(Request $request, InventoryItem $inventory)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'category' => 'required|string',
            'brand' => 'nullable|string|max:100',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:50',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'min_stock_level' => 'required|integer|min:1',
            'expiration_date' => 'nullable|date',
            'batch_number' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $oldValues = $inventory->toArray();
        $inventory->update($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'inventory_updated',
            'model_type' => InventoryItem::class,
            'model_id' => $inventory->id,
            'description' => "Updated supply item {$inventory->product_name} ({$inventory->item_code})",
            'old_values' => $oldValues,
            'new_values' => $inventory->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('inventory.index')->with('success', "Item {$inventory->product_name} updated successfully.");
    }

    public function adjustStock(Request $request, InventoryItem $inventory)
    {
        $validated = $request->validate([
            'type' => 'required|in:received,used,adjusted,damaged,expired,returned',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $prevStock = $inventory->stock_level;
        $qty = $validated['quantity'];

        if (in_array($validated['type'], ['received', 'returned'])) {
            $newStock = $prevStock + $qty;
        } else {
            // used, adjusted deduction, damaged, expired
            if ($qty > $prevStock && $validated['type'] !== 'adjusted') {
                return back()->with('error', "Adjustment quantity ({$qty}) exceeds current stock ({$prevStock}).");
            }
            $newStock = max(0, $prevStock - $qty);
        }

        $inventory->update(['stock_level' => $newStock]);

        StockMovement::create([
            'inventory_item_id' => $inventory->id,
            'type' => $validated['type'],
            'quantity' => in_array($validated['type'], ['received', 'returned']) ? $qty : -$qty,
            'previous_stock' => $prevStock,
            'new_stock' => $newStock,
            'reference_type' => 'manual_adjustment',
            'notes' => $validated['notes'] ?? 'Manual stock adjustment by staff',
            'user_id' => auth()->id(),
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'stock_adjusted',
            'model_type' => InventoryItem::class,
            'model_id' => $inventory->id,
            'description' => "Stock adjusted for {$inventory->product_name}: {$validated['type']} {$qty} {$inventory->unit}(s). New stock: {$newStock}",
            'new_values' => ['stock_level' => $newStock, 'type' => $validated['type']],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('inventory.index')->with('success', "Stock updated for {$inventory->product_name}. New level: {$newStock} {$inventory->unit}(s).");
    }

    public function movements(InventoryItem $inventory)
    {
        $movements = $inventory->stockMovements()->with('user')->latest()->paginate(20);
        return view('inventory.movements', compact('inventory', 'movements'));
    }
}
