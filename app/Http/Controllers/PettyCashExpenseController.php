<?php

namespace App\Http\Controllers;

use App\Models\PettyCashExpense;
use App\Models\PettyCashSetting;
use Illuminate\Http\Request;

class PettyCashExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,cajero']);
    }

    public function index(Request $request)
    {
        $request->validate([
            'start' => ['nullable', 'date', 'before_or_equal:end'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
        ]);

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

        $pettyCashAmount = PettyCashSetting::amountForDate(now()->toDateString());
        $todayTotal = PettyCashExpense::whereDate('created_at', now()->toDateString())->sum('amount');
        $remaining = max(0, $pettyCashAmount - $todayTotal);

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
            'pettyCashAmount' => $pettyCashAmount,
        ]);
    }

    public function create()
    {
        $pettyCashAmount = PettyCashSetting::amountForDate(now()->toDateString());
        $todayTotal = PettyCashExpense::whereDate('created_at', now()->toDateString())->sum('amount');
        $remaining = max(0, $pettyCashAmount - $todayTotal);
        return view('petty_cash.create', compact('remaining', 'pettyCashAmount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date|before_or_equal:today',
        ]);
        $date = $request->date;
        $dateTotal = PettyCashExpense::whereDate('created_at', $date)->sum('amount');
        $limit = PettyCashSetting::amountForDate($date);
        if ($dateTotal + $request->amount > $limit) {
            return back()->withErrors(['amount' => 'Fondo insuficiente en caja chica para esa fecha.'])->withInput();
        }

        $timestamp = $date . ' ' . now()->format('H:i:s');
        PettyCashExpense::create([
            'user_id' => auth()->id(),
            'description' => $request->description,
            'amount' => $request->amount,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        return redirect()->route('petty-cash.index')
            ->with('success', 'Gasto registrado correctamente.');
    }

    public function updateFund(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'apply_today' => 'nullable|boolean',
        ]);

        $amount = $request->amount;
        $applyToday = $request->boolean('apply_today');
        $today = now()->toDateString();

        if ($applyToday) {
            $todayTotal = PettyCashExpense::whereDate('created_at', $today)->sum('amount');
            if ($todayTotal > $amount) {
                return back()->withErrors(['amount' => 'Los gastos de hoy exceden el nuevo monto.'])->withInput();
            }
            PettyCashSetting::create([
                'amount' => $amount,
                'effective_date' => $today,
            ]);
        } else {
            PettyCashSetting::create([
                'amount' => $amount,
                'effective_date' => now()->addDay()->toDateString(),
            ]);
        }

        return back()->with('success', 'Monto de caja chica actualizado.');
    }

    public function destroy(PettyCashExpense $pettyCash)
    {
        $pettyCash->delete();
        return redirect()->route('petty-cash.index')
            ->with('success', 'Gasto eliminado correctamente.');
    }
}
