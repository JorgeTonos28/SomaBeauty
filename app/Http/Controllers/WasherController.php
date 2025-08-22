<?php

namespace App\Http\Controllers;

use App\Models\Washer;
use App\Models\WasherPayment;
use App\Models\WasherMovement;
use App\Models\TicketWash;
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
        $request->validate([
            'start' => ['nullable', 'date', 'before_or_equal:end'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
        ]);
        $filters = $request->only(['start', 'end']);
        $filters['start'] = $filters['start'] ?? now()->toDateString();
        $filters['end'] = $filters['end'] ?? now()->toDateString();

        $washers = Washer::orderBy('name')->get()->map(function ($washer) use ($filters) {
            $washQuery = $washer->ticketWashes()->whereHas('ticket', function($q) use ($filters) {
                if ($filters['start']) {
                    $q->whereDate('created_at', '>=', $filters['start']);
                }
                if ($filters['end']) {
                    $q->whereDate('created_at', '<=', $filters['end']);
                }
            });
            $ticketTotal = $washQuery->count() * 100;

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
        $request->validate([
            'start' => ['nullable', 'date', 'before_or_equal:end'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
        ]);

        $start = $request->input('start', $today);
        $end = $request->input('end', $today);

        $washesQuery = $washer->ticketWashes()->with(['ticket', 'ticket.vehicle', 'ticket.vehicleType', 'vehicle', 'vehicleType']);
        if ($start) {
            $washesQuery->whereHas('ticket', fn($q) => $q->whereDate('created_at', '>=', $start));
        }
        if ($end) {
            $washesQuery->whereHas('ticket', fn($q) => $q->whereDate('created_at', '<=', $end));
        }
        $washes = $washesQuery->get();

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
        foreach ($washes as $w) {
            $t = $w->ticket;
            $vehicle = $w->vehicle;
            $detailParts = [];
            if ($vehicle) {
                $detailParts[] = $vehicle->brand;
                $detailParts[] = $vehicle->model;
                $detailParts[] = $vehicle->color;
                $detailParts[] = $vehicle->year;
            }
            $detailParts[] = optional($w->vehicleType)->name;

            $events[] = [
                'date' => $t->created_at,
                'customer' => $t->customer_name,
                'description' => implode(' | ', array_filter($detailParts)),
                'gain' => 100,
                'payment' => null,
                'wash_id' => $w->id,
                'ticket_id' => $t->id,
                'paid_to_washer' => $w->washer_paid,
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
                'paid_to_washer' => false,
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
                'paid_to_washer' => false,
            ];
        }
        usort($events, fn($a, $b) => $a['date']->timestamp <=> $b['date']->timestamp);

        $totalGain = $washes->count() * 100 + $movements->sum('amount');
        $totalPaid = $payments->sum('amount_paid');
        $pending = $totalGain - $totalPaid;

        if ($request->ajax()) {
            return view('washers.partials.ledger', ['events' => $events, 'pending' => $pending]);
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
            'wash_ids' => 'required|string',
        ], [
            'wash_ids.required' => 'Debe seleccionar al menos un registro a pagar.',
        ]);

        $ids = array_filter(explode(',', $request->wash_ids));
        $washes = TicketWash::where('washer_id', $washer->id)
            ->whereIn('id', $ids)
            ->where('washer_paid', false)
            ->with('ticket')
            ->get();

        if ($washes->isEmpty()) {
            return back()->with('success', 'No hay monto pendiente.');
        }

        $washesByDate = $washes->groupBy(fn($w) => $w->ticket->created_at->toDateString());

        foreach ($washesByDate as $group) {
            $paymentDate = $group->first()->ticket->created_at;
            WasherPayment::create([
                'washer_id' => $washer->id,
                'payment_date' => $paymentDate,
                'total_washes' => $group->count(),
                'amount_paid' => $group->count() * 100,
                'created_at' => $paymentDate,
            ]);
        }

        $decrement = $washes->count() * 100;
        $washer->pending_amount = max(0, $washer->pending_amount - $decrement);
        $washer->save();

        TicketWash::whereIn('id', $washes->pluck('id'))->update(['washer_paid' => true]);

        return back()->with('success', 'Pago registrado correctamente.');
    }

    public function payAll()
    {
        $washers = Washer::where('pending_amount', '>', 0)->get();

        foreach ($washers as $washer) {
            $washes = TicketWash::where('washer_id', $washer->id)
                ->where('washer_paid', false)
                ->with('ticket')
                ->get();

            if ($washes->isEmpty()) {
                continue;
            }

            $washesByDate = $washes->groupBy(fn($w) => $w->ticket->created_at->toDateString());

            foreach ($washesByDate as $group) {
                $paymentDate = $group->first()->ticket->created_at;
                WasherPayment::create([
                    'washer_id' => $washer->id,
                    'payment_date' => $paymentDate,
                    'total_washes' => $group->count(),
                    'amount_paid' => $group->count() * 100,
                    'created_at' => $paymentDate,
                ]);
            }

            TicketWash::whereIn('id', $washes->pluck('id'))->update(['washer_paid' => true]);

            $washer->update(['pending_amount' => 0]);
        }

        return redirect()->route('washers.index')->with('success', 'Pagos registrados.');
    }
}
