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

    public function show(Request $request, Washer $washer)
    {
        $today = now()->toDateString();

        $start = $request->input('start', $today);
        $end = $request->input('end', $today);

        $ticketsQuery = $washer->tickets()->with(['vehicleType', 'vehicle']);
        if ($start) {
            $ticketsQuery->whereDate('created_at', '>=', $start);
        }
        if ($end) {
            $ticketsQuery->whereDate('created_at', '<=', $end);
        }
        $tickets = $ticketsQuery->get();

        $paymentsQuery = $washer->payments();
        if ($start) {
            $paymentsQuery->whereDate('payment_date', '>=', $start);
        }
        if ($end) {
            $paymentsQuery->whereDate('payment_date', '<=', $end);
        }
        $payments = $paymentsQuery->get();

        $events = [];
        foreach ($tickets as $t) {
            $vehicle = $t->vehicle;
            $detailParts = [];
            if ($vehicle) {
                $detailParts[] = $vehicle->brand;
                $detailParts[] = $vehicle->model;
                $detailParts[] = $vehicle->color;
                $detailParts[] = $vehicle->year;
            }
            $detailParts[] = optional($t->vehicleType)->name;

            $events[] = [
                'date' => $t->created_at,
                'customer' => $t->customer_name,
                'description' => implode(' | ', array_filter($detailParts)),
                'gain' => 100,
                'payment' => null,
            ];
        }
        foreach ($payments as $p) {
            $events[] = [
                'date' => \Carbon\Carbon::parse($p->payment_date),
                'customer' => null,
                'description' => 'Pago',
                'gain' => null,
                'payment' => $p->amount_paid,
            ];
        }
        usort($events, fn($a, $b) => $b['date']->timestamp <=> $a['date']->timestamp);

        if ($request->ajax()) {
            return view('washers.partials.ledger', ['events' => $events]);
        }

        return view('washers.show', [
            'washer' => $washer,
            'events' => $events,
            'filters' => ['start' => $start, 'end' => $end],
        ]);
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
