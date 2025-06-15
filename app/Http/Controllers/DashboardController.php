<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\PettyCashExpense;
use App\Models\WasherPayment;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    public function index(Request $request)
    {
        $start = $request->input('start', now()->toDateString());
        $end = $request->input('end', now()->toDateString());

        $ticketQuery = Ticket::with('details')
            ->where('canceled', false)
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end);

        $tickets = $ticketQuery->get();

        $serviceTotal = 0;
        $productTotal = 0;
        $drinkTotal = 0;
        $washersCount = 0;

        foreach ($tickets as $ticket) {
            foreach ($ticket->details as $detail) {
                $subtotal = $detail->subtotal;
                switch ($detail->type) {
                    case 'service':
                        $serviceTotal += $subtotal;
                        break;
                    case 'product':
                        $productTotal += $subtotal;
                        break;
                    case 'drink':
                        $drinkTotal += $subtotal;
                        break;
                }
            }
            if ($ticket->washer_id) {
                $washersCount++;
            }
        }

        $washerPayTotal = $washersCount * 100;

        $pettyCashExpenses = PettyCashExpense::whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->latest()
            ->get();

        $pettyCashTotal = $pettyCashExpenses->sum('amount');

        $lastExpenses = $pettyCashExpenses->take(5);

        $movements = [];
        foreach ($tickets as $t) {
            $movements[] = [
                'description' => 'Ticket '.$t->id,
                'date' => $t->created_at->format('d/m/Y H:i'),
                'amount' => $t->total_amount,
            ];
        }
        foreach ($pettyCashExpenses as $e) {
            $movements[] = [
                'description' => 'Gasto: '.$e->description,
                'date' => $e->created_at->format('d/m/Y H:i'),
                'amount' => -$e->amount,
            ];
        }
        foreach (WasherPayment::whereDate('payment_date', '>=', $start)->whereDate('payment_date', '<=', $end)->get() as $p) {
            $movements[] = [
                'description' => 'Pago Lavador '.$p->washer->name,
                'date' => $p->payment_date,
                'amount' => -$p->amount_paid,
            ];
        }
        usort($movements, fn($a,$b)=>strcmp($b['date'],$a['date']));

        $washerPayments = WasherPayment::whereDate('payment_date', '>=', $start)
            ->whereDate('payment_date', '<=', $end)
            ->sum('amount_paid');

        $generalCash = 3200 + $serviceTotal + $productTotal + $drinkTotal - $pettyCashTotal - $washerPayments;

        $washerPayDue = $washerPayTotal - $washerPayments;

        $grossProfit = $generalCash - 3200 - $washerPayDue;

        if ($request->ajax()) {
            return view('dashboard.partials.summary', compact(
                'generalCash',
                'washerPayDue',
                'serviceTotal',
                'productTotal',
                'drinkTotal',
                'grossProfit',
                'lastExpenses',
                'movements'
            ));
        }

        return view('dashboard', [
            'filters' => ['start' => $start, 'end' => $end],
            'generalCash' => $generalCash,
            'washerPayDue' => $washerPayDue,
            'serviceTotal' => $serviceTotal,
            'productTotal' => $productTotal,
            'drinkTotal' => $drinkTotal,
            'grossProfit' => $grossProfit,
            'lastExpenses' => $lastExpenses,
            'movements' => $movements,
        ]);
    }

    public function download(Request $request)
    {
        $start = $request->input('start', now()->toDateString());
        $end = $request->input('end', now()->toDateString());

        return new \App\Exports\DashboardExport($start, $end);
    }
}
