<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionSetting extends Model
{
    use HasFactory;

    protected $fillable = ['percentage'];

    protected $casts = [
        'percentage' => 'float',
    ];

    public static function currentPercentage(): float
    {
        return optional(static::query()->latest('updated_at')->first())->percentage ?? 10.0;
    }

    public static function updatePercentage(float $percentage): void
    {
        $setting = static::query()->latest('updated_at')->first();

        if ($setting) {
            $setting->update(['percentage' => $percentage]);
        } else {
            static::create(['percentage' => $percentage]);
        }
    }
}
