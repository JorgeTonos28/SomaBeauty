<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankAccount;

class BankAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = BankAccount::orderBy('bank')->get();
        return view('bank_accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('bank_accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bank' => 'required|string|max:255',
            'account' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'holder' => 'required|string|max:255',
            'holder_cedula' => 'required|string|max:255',
        ]);

        BankAccount::create($request->only('bank','account','type','holder','holder_cedula'));

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Cuenta creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function edit(BankAccount $bankAccount)
    {
        return view('bank_accounts.edit', compact('bankAccount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankAccount $bankAccount)
    {
        $request->validate([
            'bank' => 'required|string|max:255',
            'account' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'holder' => 'required|string|max:255',
            'holder_cedula' => 'required|string|max:255',
        ]);

        $bankAccount->update($request->only('bank','account','type','holder','holder_cedula'));

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Cuenta actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankAccount $bankAccount)
    {
        $bankAccount->delete();
        return redirect()->route('bank-accounts.index')
            ->with('success', 'Cuenta eliminada correctamente.');
    }
}
