<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AppSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function edit(): View
    {
        $settings = Schema::hasTable('app_settings')
            ? AppSetting::query()->first()
            : null;

        return view('settings.index', [
            'settings' => $settings,
            'blockMobile' => optional($settings)->block_mobile_devices ?? true,
            'defaultMinimumStock' => optional($settings)->default_minimum_stock
                ?? AppSetting::defaultMinimumStock(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'block_mobile_devices' => ['required', 'boolean'],
            'default_minimum_stock' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        AppSetting::updateSettings([
            'block_mobile_devices' => (bool) $data['block_mobile_devices'],
            'default_minimum_stock' => (int) $data['default_minimum_stock'],
        ]);

        return redirect()->route('settings.edit')
            ->with('success', 'Las configuraciones generales se actualizaron correctamente.');
    }
}
