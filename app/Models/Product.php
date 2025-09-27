<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'stock', 'low_stock_threshold'];

    protected $casts = [
        'stock' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function scopeLowStock($query)
    {
        return $query->whereNotNull('low_stock_threshold')
            ->where('low_stock_threshold', '>', 0)
            ->whereColumn('stock', '<=', 'low_stock_threshold');
    }

    public static function lowStockItems()
    {
        return static::lowStock()->orderBy('name')->get();
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function details()
    {
        return $this->hasMany(TicketDetail::class);
    }
}

