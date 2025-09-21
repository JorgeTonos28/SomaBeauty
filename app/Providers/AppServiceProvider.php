<?php

namespace App\Providers;

use App\Models\AppearanceSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $settings = Cache::remember('appearance_settings', 300, function () {
                if (! Schema::hasTable('appearance_settings')) {
                    return null;
                }

                return AppearanceSetting::first();
            });

            $view->with('appearanceSettings', $settings);
        });
    }
}
