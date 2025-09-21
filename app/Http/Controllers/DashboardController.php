<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketWash;
use App\Models\PettyCashExpense;
use App\Models\PettyCashSetting;
use App\Models\WasherPayment;
use App\Models\BankAccount;
use App\Models\Washer;
use App\Models\WasherMovement;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    public function index(Request $request)
    {
        $request->validate([
            'start' => ['nullable', 'date', 'before_or_equal:end'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
        ]);
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
        }

        $cashPayments = Ticket::where('canceled', false)
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

        $pettyCashAmount = PettyCashSetting::amountForDate($start);
        $pettyCashExpenses = PettyCashExpense::whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->latest()
            ->get();
        $pettyCashTotal = $pettyCashExpenses->sum('amount');

        $totalFacturado = $cashPayments + $transferTotal + $pettyCashAmount - $pettyCashTotal;
        $invoicedTotal = $cashPayments + $transferTotal;

        $washerPayments = WasherPayment::whereDate('payment_date', '>=', $start)
            ->whereDate('payment_date', '<=', $end)
            ->sum('amount_paid');

        $cashTotal = $cashPayments - $washerPayments - $pettyCashTotal;

        $bankAccountTotals = Ticket::selectRaw('bank_account_id, SUM(total_amount) as total')
            ->with('bankAccount')
            ->where('canceled', false)
            ->where('pending', false)
            ->whereDate('paid_at', '>=', $start)
            ->whereDate('paid_at', '<=', $end)
            ->where('payment_method', 'transferencia')
            ->groupBy('bank_account_id')
            ->get();

        $tipTotal = TicketWash::whereHas('ticket', function($q) use ($start, $end) {
                $q->where('canceled', false)
                  ->where('pending', false)
                  ->whereDate('paid_at', '>=', $start)
                  ->whereDate('paid_at', '<=', $end);
            })
            ->sum('tip');

        $commissionBase = TicketWash::whereHas('ticket', function($q) use ($start, $end) {
                $q->where('canceled', false)
                  ->where('pending', false)
                  ->whereDate('paid_at', '>=', $start)
                  ->whereDate('paid_at', '<=', $end);
            })
            ->sum('commission_amount');

        $extraCommission = TicketWash::whereHas('ticket', function($q) use ($start, $end) {
                $q->where('canceled', true)
                  ->where('keep_commission_on_cancel', true)
                  ->where('pending', false)
                  ->whereDate('paid_at', '>=', $start)
                  ->whereDate('paid_at', '<=', $end);
            })
            ->whereNotNull('washer_id')
            ->sum('commission_amount');

        $extraTip = TicketWash::whereHas('ticket', function($q) use ($start, $end) {
                $q->where('canceled', true)
                  ->where('keep_tip_on_cancel', true)
                  ->where('pending', false)
                  ->whereDate('paid_at', '>=', $start)
                  ->whereDate('paid_at', '<=', $end);
            })
            ->sum('tip');

        $washerPayTotal = $commissionBase + $tipTotal + $extraCommission + $extraTip;

        $pendingTickets = Ticket::with('details')
            ->where('canceled', false)
            ->where('pending', true)
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->get();

        $accountsReceivable = $pendingTickets->sum('total_amount');

        $washerDebts = WasherMovement::with('washer')
            ->where('amount', '<', 0)
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->get();
        $accountsReceivable += $washerDebts->sum(fn($m) => abs($m->amount));

        $unassignedCommission = Ticket::where('canceled', false)
            ->where('washer_pending_amount', '>', 0)
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->sum('washer_pending_amount');

        $assignedPendingCommission = TicketWash::whereHas('ticket', function($q) use ($start, $end) {
                $q->where('pending', true)
                  ->where('canceled', false)
                  ->whereDate('created_at', '>=', $start)
                  ->whereDate('created_at', '<=', $end);
            })
            ->whereNotNull('washer_id')
            ->selectRaw('SUM(commission_amount + tip) as total')
            ->value('total') ?? 0;

        $lastExpenses = $pettyCashExpenses->take(5);

        $movements = [];
        foreach ($tickets as $t) {
            $movements[] = [
                'description' => 'Ticket '.$t->id,
                'date' => $t->paid_at->format('d/m/Y h:i A'),
                'amount' => $t->total_amount,
            ];
        }
        foreach ($pettyCashExpenses as $e) {
            $movements[] = [
                'description' => 'Gasto: '.$e->description,
                'date' => $e->created_at->format('d/m/Y h:i A'),
                'amount' => -$e->amount,
            ];
        }
        foreach (WasherPayment::whereDate('payment_date', '>=', $start)->whereDate('payment_date', '<=', $end)->get() as $p) {
            $movements[] = [
                'description' => 'Pago Estilista '.$p->washer->name,
                'date' => $p->payment_date->format('d/m/Y h:i A'),
                'amount' => -$p->amount_paid,
            ];
        }
        usort($movements, fn($a,$b)=>strcmp($b['date'],$a['date']));

        $washerPayDue = 0;
        foreach (Washer::all() as $w) {
            $wq = $w->ticketWashes()->whereHas('ticket', function($q) use ($start, $end) {
                $q->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);
            });
            $ticketsTotal = $wq->sum('commission_amount');

            $mq = $w->movements();
            $mq->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end);
            $movementTotal = $mq->sum('amount');

            $pq = $w->payments();
            $pq->whereDate('payment_date', '>=', $start)->whereDate('payment_date', '<=', $end);
            $paymentTotal = $pq->sum('amount_paid');

            $washerPayDue += $ticketsTotal + $movementTotal - $paymentTotal;
        }
        $washerPayDue += $unassignedCommission;

        $washerDebtAmount = $washerDebts->sum(fn($m) => abs($m->amount));

        $grossProfit = $totalFacturado - $pettyCashAmount - $washerPayTotal;
        $grossProfit -= $washerDebtAmount + $assignedPendingCommission;

        if ($request->ajax()) {
            return view('dashboard.partials.summary', compact(
                'totalFacturado',
                'invoicedTotal',
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
                'pendingTickets',
                'washerDebts',
                'pettyCashAmount'
            ));
        }

        return view('dashboard', [
            'filters' => ['start' => $start, 'end' => $end],
            'totalFacturado' => $totalFacturado,
            'invoicedTotal' => $invoicedTotal,
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
            'washerDebts' => $washerDebts,
            'pettyCashAmount' => $pettyCashAmount,
        ]);
    }

    public function download(Request $request)
    {
        $request->validate([
            'start' => ['nullable', 'date', 'before_or_equal:end'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
        ]);
        $start = $request->input('start', now()->toDateString());
        $end = $request->input('end', now()->toDateString());

        return new \App\Exports\DashboardExport($start, $end);
    }
}
