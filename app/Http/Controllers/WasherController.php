<?php

namespace App\Http\Controllers;

use App\Models\Washer;
use App\Models\WasherPayment;
use App\Models\WasherMovement;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WasherController extends Controller
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

        $washers = Washer::orderBy('name')->get()->map(function ($washer) use ($filters) {
            $ticketQuery = $washer->tickets();
            if ($filters['start']) {
                $ticketQuery->whereDate('created_at', '>=', $filters['start']);
            }
            if ($filters['end']) {
                $ticketQuery->whereDate('created_at', '<=', $filters['end']);
            }
            $ticketTotal = $ticketQuery->count() * 100;

            $movementQuery = $washer->movements();
            if ($filters['start']) {
                $movementQuery->whereDate('created_at', '>=', $filters['start']);
            }
            if ($filters['end']) {
                $movementQuery->whereDate('created_at', '<=', $filters['end']);
            }
            $movementTotal = $movementQuery->sum('amount');

            $paymentQuery = $washer->payments();
            if ($filters['start']) {
                $paymentQuery->whereDate('payment_date', '>=', $filters['start']);
            }
            if ($filters['end']) {
                $paymentQuery->whereDate('payment_date', '<=', $filters['end']);
            }
            $paymentTotal = $paymentQuery->sum('amount_paid');

            $washer->range_pending = $ticketTotal + $movementTotal - $paymentTotal;
            return $washer;
        });

        $pendingTotal = $washers->sum('range_pending');

        if ($request->ajax()) {
            return view('washers.partials.table', [
                'washers' => $washers,
                'pendingTotal' => $pendingTotal,
                'filters' => $filters,
            ]);
        }

        return view('washers.index', [
            'washers' => $washers,
            'pendingTotal' => $pendingTotal,
            'filters' => $filters,
        ]);
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

        $movementsQuery = $washer->movements();
        if ($start) {
            $movementsQuery->whereDate('created_at', '>=', $start);
        }
        if ($end) {
            $movementsQuery->whereDate('created_at', '<=', $end);
        }
        $movements = $movementsQuery->get();

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
                'ticket_id' => $t->id,
            ];
        }
        foreach ($payments as $p) {
            $events[] = [
                'date' => \Carbon\Carbon::parse($p->payment_date),
                'customer' => null,
                'description' => 'Pago',
                'gain' => null,
                'payment' => $p->amount_paid,
                'ticket_id' => null,
            ];
        }

        foreach ($movements as $m) {
            $events[] = [
                'date' => $m->created_at,
                'customer' => null,
                'description' => $m->description,
                'gain' => $m->amount,
                'payment' => null,
                'ticket_id' => $m->ticket_id,
            ];
        }
        usort($events, fn($a, $b) => $a['date']->timestamp <=> $b['date']->timestamp);

        $totalGain = $tickets->count() * 100 + $movements->sum('amount');
        $totalPaid = $payments->sum('amount_paid');
        $pending = $totalGain - $totalPaid;

        if ($request->ajax()) {
            return view('washers.partials.ledger', ['events' => $events]);
        }

        return view('washers.show', [
            'washer' => $washer,
            'events' => $events,
            'filters' => ['start' => $start, 'end' => $end],
            'pending' => $pending,
        ]);
    }

    public function pay(Request $request, Washer $washer)
    {
        $request->validate([
            'payment_date' => 'required|date|before_or_equal:today',
            'amount' => 'required|numeric|min:0.01',
            'total_washes' => 'required|integer|min:1',
        ], [
            'payment_date.required' => 'La fecha del pago es obligatoria.',
            'payment_date.date' => 'La fecha del pago no es válida.',
            'payment_date.before_or_equal' => 'La fecha del pago no puede ser futura.',
            'amount.required' => 'Debe seleccionar al menos un registro a pagar.',
        ]);

        $amount = min($request->amount, $washer->pending_amount);
        if ($amount <= 0) {
            return back()->with('success', 'No hay monto pendiente.');
        }

        $paymentDate = Carbon::parse($request->payment_date)->setTimeFrom(now());

        WasherPayment::create([
            'washer_id' => $washer->id,
            'payment_date' => $paymentDate,
            'total_washes' => $request->total_washes,
            'amount_paid' => $amount,
            'created_at' => $paymentDate,
        ]);

        $washer->decrement('pending_amount', $amount);

        return back()->with('success', 'Pago registrado correctamente.');
    }

    public function payAll(Request $request)
    {
        $request->validate([
            'payment_date' => 'required|date|before_or_equal:today',
        ], [
            'payment_date.required' => 'La fecha del pago es obligatoria.',
            'payment_date.date' => 'La fecha del pago no es válida.',
            'payment_date.before_or_equal' => 'La fecha del pago no puede ser futura.',
        ]);

        $paymentDate = Carbon::parse($request->payment_date)->setTimeFrom(now());

        $washers = Washer::where('pending_amount', '>', 0)->get();

        foreach ($washers as $washer) {
            WasherPayment::create([
                'washer_id' => $washer->id,
                'payment_date' => $paymentDate,
                'total_washes' => intval($washer->pending_amount / 100),
                'amount_paid' => $washer->pending_amount,
                'created_at' => $paymentDate,
            ]);

            $washer->update(['pending_amount' => 0]);
        }

        return redirect()->route('washers.index')->with('success', 'Pagos registrados.');
    }
}
