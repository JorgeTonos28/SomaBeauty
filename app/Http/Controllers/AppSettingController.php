<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function updateMobileAccess(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'block_mobile_devices' => ['required', 'in:0,1'],
        ]);

        AppSetting::updateBlockMobileDevices((bool) (int) $data['block_mobile_devices']);

        return back()->with(
            'success',
            $data['block_mobile_devices'] === '1'
                ? 'El acceso desde dispositivos móviles ha sido restringido.'
                : 'El acceso desde dispositivos móviles ha sido habilitado.'
        );
    }
}
