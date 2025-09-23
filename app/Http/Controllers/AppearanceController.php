<?php

namespace App\Http\Controllers;

use App\Models\AppearanceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AppearanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $settings = AppearanceSetting::first();

        return view('appearance.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|max:1024',
            'login_logo' => 'nullable|image|max:1024',
            'favicon' => 'nullable|file|max:512',
            'business_name' => 'nullable|string|max:255',
            'business_address' => 'nullable|string|max:500',
            'tax_id' => 'nullable|string|max:100',
        ]);

        $settings = AppearanceSetting::first() ?? new AppearanceSetting();

        if ($request->hasFile('logo')) {
            $dir = public_path('images');
            if (! file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            $request->file('logo')->move($dir, 'logo.png');
            $settings->logo_updated_at = now();
        }

        if ($request->hasFile('login_logo')) {
            $dir = public_path('images');
            if (! file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            $request->file('login_logo')->move($dir, 'login-logo.png');
            $settings->login_logo_updated_at = now();
        }

        if ($request->hasFile('favicon')) {
            $request->file('favicon')->move(public_path(), 'favicon.ico');
            $settings->favicon_updated_at = now();
        }

        if ($request->has('business_name')) {
            $settings->business_name = $request->filled('business_name')
                ? $request->input('business_name')
                : null;
        }

        if ($request->has('business_address')) {
            $settings->business_address = $request->filled('business_address')
                ? trim($request->input('business_address'))
                : null;
        }

        if ($request->has('tax_id')) {
            $settings->tax_id = $request->filled('tax_id')
                ? trim($request->input('tax_id'))
                : null;
        }

        $settings->save();

        Cache::forget('appearance_settings');

        return back()->with('success', 'Apariencia actualizada.');
    }
}
