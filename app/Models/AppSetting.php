<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = ['block_mobile_devices'];

    protected $casts = [
        'block_mobile_devices' => 'boolean',
    ];

    public static function blockMobileDevicesEnabled(): bool
    {
        if (! Schema::hasTable('app_settings')) {
            return true;
        }

        return Cache::remember('app_settings.block_mobile', 300, function () {
            return optional(static::query()->first())->block_mobile_devices ?? true;
        });
    }

    public static function updateBlockMobileDevices(bool $enabled): void
    {
        $settings = static::query()->first();

        if ($settings) {
            $settings->update(['block_mobile_devices' => $enabled]);
        } else {
            static::query()->create(['block_mobile_devices' => $enabled]);
        }

        Cache::forget('app_settings');
        Cache::forget('app_settings.block_mobile');
    }
}
