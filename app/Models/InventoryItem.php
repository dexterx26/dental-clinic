<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'product_name',
        'category',
        'brand',
        'supplier_id',
        'unit',
        'cost_price',
        'selling_price',
        'stock_level',
        'min_stock_level',
        'expiration_date',
        'batch_number',
        'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock_level' => 'integer',
        'min_stock_level' => 'integer',
        'expiration_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_level <= $this->min_stock_level;
    }

    public function isExpiringSoon(int $days = 60): bool
    {
        if (!$this->expiration_date) {
            return false;
        }
        return $this->expiration_date->isFuture() && $this->expiration_date->diffInDays(now()) <= $days;
    }

    public function isExpired(): bool
    {
        if (!$this->expiration_date) {
            return false;
        }
        return $this->expiration_date->isPast();
    }
}
