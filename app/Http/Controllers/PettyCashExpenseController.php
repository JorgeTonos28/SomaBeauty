<?php

namespace App\Http\Controllers;

use App\Models\PettyCashExpense;
use Illuminate\Http\Request;

class PettyCashExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,cajero']);
    }

    public function index(Request $request)
    {
        $filters = $request->only(['start', 'end']);
        $filters['start'] = $filters['start'] ?? now()->toDateString();
        $filters['end'] = $filters['end'] ?? now()->toDateString();

        $query = PettyCashExpense::query();

        if ($filters['start']) {
            $query->whereDate('created_at', '>=', $filters['start']);
        }

        if ($filters['end']) {
            $query->whereDate('created_at', '<=', $filters['end']);
        }

        $expenses = $query->latest()->get();

        $todayTotal = PettyCashExpense::whereDate('created_at', now()->toDateString())->sum('amount');
        $remaining = max(0, 3200 - $todayTotal);

        if ($request->ajax()) {
            return view('petty_cash.partials.table', [
                'expenses' => $expenses,
            ]);
        }

        return view('petty_cash.index', [
            'expenses' => $expenses,
            'filters' => $filters,
            'todayTotal' => $todayTotal,
            'remaining' => $remaining,
        ]);
    }

    public function create()
    {
        $todayTotal = PettyCashExpense::whereDate('created_at', now()->toDateString())->sum('amount');
        $remaining = max(0, 3200 - $todayTotal);
        return view('petty_cash.create', compact('remaining'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $todayTotal = PettyCashExpense::whereDate('created_at', now()->toDateString())->sum('amount');
        if ($todayTotal + $request->amount > 3200) {
            return back()->withErrors(['amount' => 'Fondo insuficiente en caja chica para hoy.'])->withInput();
        }

        PettyCashExpense::create([
            'user_id' => auth()->id(),
            'description' => $request->description,
            'amount' => $request->amount,
        ]);

        return redirect()->route('petty-cash.index')
            ->with('success', 'Gasto registrado correctamente.');
    }

    public function destroy(PettyCashExpense $pettyCash)
    {
        $pettyCash->delete();
        return redirect()->route('petty-cash.index')
            ->with('success', 'Gasto eliminado correctamente.');
    }
}
