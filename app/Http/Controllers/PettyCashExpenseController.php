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
        $query = PettyCashExpense::query();

        if ($request->filled('start')) {
            $query->whereDate('created_at', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $query->whereDate('created_at', '<=', $request->end);
        }

        $expenses = $query->latest()->paginate(20);

        return view('petty_cash.index', [
            'expenses' => $expenses,
            'filters' => $request->only(['start', 'end']),
        ]);
    }

    public function create()
    {
        return view('petty_cash.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
        ]);

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
