<?php

namespace App\Http\Controllers;

use App\Models\Washer;
use App\Models\WasherPayment;
use Illuminate\Http\Request;

class WasherController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $washers = Washer::orderBy('name')->get();
        return view('washers.index', compact('washers'));
    }

    public function create()
    {
        return view('washers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'sometimes|boolean',
        ]);

        Washer::create([
            'name' => $request->name,
            'pending_amount' => 0,
            'active' => $request->boolean('active'),
        ]);

        return redirect()->route('washers.index')
            ->with('success', 'Lavador creado correctamente.');
    }

    public function edit(Washer $washer)
    {
        return view('washers.edit', compact('washer'));
    }

    public function update(Request $request, Washer $washer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'sometimes|boolean',
        ]);

        $washer->update([
            'name' => $request->name,
            'active' => $request->boolean('active'),
        ]);

        return redirect()->route('washers.index')
            ->with('success', 'Lavador actualizado correctamente.');
    }

    public function destroy(Washer $washer)
    {
        $washer->delete();

        return redirect()->route('washers.index')
            ->with('success', 'Lavador eliminado correctamente.');
    }

    public function show(Washer $washer)
    {
        $lastPayment = $washer->payments()->latest('payment_date')->first();
        $fromDate = $lastPayment ? $lastPayment->payment_date : null;

        $ticketsQuery = $washer->tickets()->with('vehicleType')->orderByDesc('created_at');
        if ($fromDate) {
            $ticketsQuery->whereDate('created_at', '>', $fromDate);
        }
        $tickets = $ticketsQuery->get();

        return view('washers.show', compact('washer', 'tickets', 'fromDate'));
    }

    public function pay(Washer $washer)
    {
        if ($washer->pending_amount <= 0) {
            return back()->with('success', 'No hay monto pendiente.');
        }

        WasherPayment::create([
            'washer_id' => $washer->id,
            'payment_date' => now()->toDateString(),
            'total_washes' => intval($washer->pending_amount / 100),
            'amount_paid' => $washer->pending_amount,
        ]);

        $washer->update(['pending_amount' => 0]);

        return back()->with('success', 'Pago registrado correctamente.');
    }

    public function payAll()
    {
        $washers = Washer::where('pending_amount', '>', 0)->get();

        foreach ($washers as $washer) {
            WasherPayment::create([
                'washer_id' => $washer->id,
                'payment_date' => now()->toDateString(),
                'total_washes' => intval($washer->pending_amount / 100),
                'amount_paid' => $washer->pending_amount,
            ]);

            $washer->update(['pending_amount' => 0]);
        }

        return redirect()->route('washers.index')->with('success', 'Pagos registrados.');
    }
}
