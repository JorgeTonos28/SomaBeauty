<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\PettyCashExpense;
use App\Models\WasherPayment;
use App\Models\BankAccount;

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
            ->where('pending', false)
            ->whereDate('paid_at', '>=', $start)
            ->whereDate('paid_at', '<=', $end);

        $tickets = $ticketQuery->get();

        $serviceTotal = 0;
        $productTotal = 0;
        $drinkTotal = 0;

        $washCount = 0;

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
            $washCount += $ticket->details
                ->where('type', 'service')
                ->sum('quantity');
        }

        $cashTotal = Ticket::where('canceled', false)
            ->where('pending', false)
            ->whereDate('paid_at', '>=', $start)
            ->whereDate('paid_at', '<=', $end)
            ->where('payment_method', '!=', 'transferencia')
            ->sum('total_amount');

        $transferTotal = Ticket::where('canceled', false)
            ->where('pending', false)
            ->whereDate('paid_at', '>=', $start)
            ->whereDate('paid_at', '<=', $end)
            ->where('payment_method', 'transferencia')
            ->sum('total_amount');

        $bankAccountTotals = Ticket::selectRaw('bank_account_id, SUM(total_amount) as total')
            ->with('bankAccount')
            ->where('canceled', false)
            ->where('pending', false)
            ->whereDate('paid_at', '>=', $start)
            ->whereDate('paid_at', '<=', $end)
            ->where('payment_method', 'transferencia')
            ->groupBy('bank_account_id')
            ->get();

        $pettyCashInitial = 3200;
        $totalFacturado = $cashTotal + $transferTotal + $pettyCashInitial;

        $washerPayTotal = $washCount * 100;

        $pettyCashExpenses = PettyCashExpense::whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->latest()
            ->get();

        $pettyCashTotal = $pettyCashExpenses->sum('amount');

        $pendingTickets = Ticket::with('details')
            ->where('canceled', false)
            ->where('pending', true)
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->get();

        $accountsReceivable = $pendingTickets->sum('total_amount');

        $lastExpenses = $pettyCashExpenses->take(5);

        $movements = [];
        foreach ($tickets as $t) {
            $movements[] = [
                'description' => 'Ticket '.$t->id,
                'date' => $t->paid_at->format('d/m/Y H:i'),
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

        $washerPayDue = max(0, $washerPayTotal - $washerPayments);

        $grossProfit = $totalFacturado - $pettyCashInitial - $pettyCashTotal - $washerPayTotal;

        if ($request->ajax()) {
            return view('dashboard.partials.summary', compact(
                'totalFacturado',
                'cashTotal',
                'transferTotal',
                'bankAccountTotals',
                'washerPayDue',
                'serviceTotal',
                'productTotal',
                'drinkTotal',
                'grossProfit',
                'pettyCashTotal',
                'lastExpenses',
                'movements',
                'accountsReceivable',
                'pendingTickets'
            ));
        }

        return view('dashboard', [
            'filters' => ['start' => $start, 'end' => $end],
            'totalFacturado' => $totalFacturado,
            'cashTotal' => $cashTotal,
            'transferTotal' => $transferTotal,
            'bankAccountTotals' => $bankAccountTotals,
            'washerPayDue' => $washerPayDue,
            'serviceTotal' => $serviceTotal,
            'productTotal' => $productTotal,
            'drinkTotal' => $drinkTotal,
            'grossProfit' => $grossProfit,
            'lastExpenses' => $lastExpenses,
            'movements' => $movements,
            'pettyCashTotal' => $pettyCashTotal,
            'accountsReceivable' => $accountsReceivable,
            'pendingTickets' => $pendingTickets,
        ]);
    }

    public function download(Request $request)
    {
        $start = $request->input('start', now()->toDateString());
        $end = $request->input('end', now()->toDateString());

        return new \App\Exports\DashboardExport($start, $end);
    }
}
