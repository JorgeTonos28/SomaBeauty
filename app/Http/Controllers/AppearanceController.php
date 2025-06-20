<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppearanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        return view('appearance.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|max:1024',
            'favicon' => 'nullable|file|max:512',
        ]);

        if ($request->hasFile('logo')) {
            $dir = public_path('images');
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            $request->file('logo')->move($dir, 'logo.png');
        }

        if ($request->hasFile('favicon')) {
            $request->file('favicon')->move(public_path(), 'favicon.ico');
        }

        return back()->with('success', 'Apariencia actualizada.');
    }
}
