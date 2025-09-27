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
        $defaultThreshold = AppSetting::defaultMinimumStock();

        return $query->where(function ($query) use ($defaultThreshold) {
            $query->where(function ($query) {
                $query->whereNotNull('low_stock_threshold')
                    ->where('low_stock_threshold', '>', 0)
                    ->whereColumn('stock', '<=', 'low_stock_threshold');
            });

            if ($defaultThreshold > 0) {
                $query->orWhere(function ($query) use ($defaultThreshold) {
                    $query->whereNull('low_stock_threshold')
                        ->where('stock', '<=', $defaultThreshold);
                });
            }
        });
    }

    public static function lowStockItems()
    {
        return static::lowStock()->orderBy('name')->get();
    }

    public function getEffectiveLowStockThresholdAttribute(): ?int
    {
        if ($this->low_stock_threshold !== null) {
            return $this->low_stock_threshold > 0
                ? $this->low_stock_threshold
                : null;
        }

        $default = AppSetting::defaultMinimumStock();

        return $default > 0 ? $default : null;
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

