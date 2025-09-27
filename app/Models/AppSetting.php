<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AppSetting extends Model
{
    use HasFactory;

    public const DEFAULT_MINIMUM_STOCK = 5;

    protected $fillable = ['block_mobile_devices', 'default_minimum_stock'];

    protected $casts = [
        'block_mobile_devices' => 'boolean',
        'default_minimum_stock' => 'integer',
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
        static::updateSettings([
            'block_mobile_devices' => $enabled,
        ]);
    }

    public static function defaultMinimumStock(): int
    {
        if (! Schema::hasTable('app_settings')) {
            return self::DEFAULT_MINIMUM_STOCK;
        }

        return Cache::remember('app_settings.default_minimum_stock', 300, function () {
            return optional(static::query()->first())->default_minimum_stock
                ?? self::DEFAULT_MINIMUM_STOCK;
        });
    }

    public static function updateSettings(array $attributes): void
    {
        $settings = static::query()->first();

        $payload = array_merge([
            'block_mobile_devices' => optional($settings)->block_mobile_devices ?? true,
            'default_minimum_stock' => optional($settings)->default_minimum_stock ?? self::DEFAULT_MINIMUM_STOCK,
        ], $attributes);

        if ($settings) {
            $settings->fill($payload)->save();
        } else {
            static::query()->create($payload);
        }

        Cache::forget('app_settings');
        Cache::forget('app_settings.block_mobile');
        Cache::forget('app_settings.default_minimum_stock');
    }
}
