<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PettyCashSetting extends Model
{
    protected $fillable = ['amount', 'effective_date'];

    public static function amountForDate($date)
    {
        return static::where('effective_date', '<=', $date)
            ->orderByDesc('effective_date')
            ->value('amount') ?? 3200;
    }
}
