<?php

namespace App\Http\Controllers;

use App\Models\CommissionSetting;
use App\Models\Product;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TicketDetail;
use App\Models\TicketWash;
use App\Models\InventoryMovement;
use App\Models\VehicleType;
use App\Models\Vehicle;
use App\Models\Washer;
use App\Models\WasherMovement;
use App\Models\WasherPayment;
use App\Models\Drink;
use App\Models\Discount;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TicketController extends Controller
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
        $filters = $request->only(['start', 'end', 'pending']);
        $filters['start'] = $filters['start'] ?? now()->toDateString();
        $filters['end'] = $filters['end'] ?? now()->toDateString();

        $query = Ticket::with(['details', 'bankAccount', 'washes.vehicleType', 'washes.washer', 'washes.details.service'])->where('canceled', false);

        if ($request->boolean('pending')) {
            $query->where('pending', true);
        }

        if ($filters['start']) {
            $query->whereDate('created_at', '>=', $filters['start']);
        }

        if ($filters['end']) {
            $query->whereDate('created_at', '<=', $filters['end']);
        }

        $tickets = $query->latest()->get();

        $invQuery = Ticket::where('canceled', false)->where('pending', false);
        if ($filters['start']) {
            $invQuery->whereDate('paid_at', '>=', $filters['start']);
        }
        if ($filters['end']) {
            $invQuery->whereDate('paid_at', '<=', $filters['end']);
        }
        $invoicedTotal = $invQuery->sum('total_amount');

        $bankAccounts = BankAccount::all();
        $washers = Washer::where('active', true)->orderBy('name')->get();

        if ($request->ajax()) {
            return view('tickets.partials.table', [
                'tickets' => $tickets,
                'bankAccounts' => $bankAccounts,
                'washers' => $washers,
                'invoicedTotal' => $invoicedTotal,
            ]);
        }

        return view('tickets.index', [
            'tickets' => $tickets,
            'filters' => $filters,
            'bankAccounts' => $bankAccounts,
            'washers' => $washers,
            'invoicedTotal' => $invoicedTotal,
        ]);
    }

    public function canceled(Request $request)
    {
        $request->validate([
            'start' => ['nullable', 'date', 'before_or_equal:end'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
        ]);
        $query = Ticket::with(['details', 'bankAccount', 'washes.details.service', 'washes.vehicleType'])->where('canceled', true);

        if ($request->filled('start')) {
            $query->whereDate('created_at', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $query->whereDate('created_at', '<=', $request->end);
        }

        $tickets = $query->latest()->get();

        if ($request->ajax()) {
            return view('tickets.partials.canceled-table', [
                'tickets' => $tickets,
            ]);
        }

        return view('tickets.canceled', [
            'tickets' => $tickets,
            'filters' => $request->only(['start', 'end']),
        ]);
    }

    public function pending(Request $request)
    {
        $request->validate([
            'start' => ['nullable', 'date', 'before_or_equal:end'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
        ]);
        $filters = $request->only(['start', 'end']);
        $filters['start'] = $filters['start'] ?? now()->toDateString();
        $filters['end'] = $filters['end'] ?? now()->toDateString();

        $query = Ticket::with(['details', 'bankAccount', 'washes.vehicleType', 'washes.washer', 'washes.details.service'])
            ->where('canceled', false)
            ->where('pending', true);

        if ($filters['start']) {
            $query->whereDate('created_at', '>=', $filters['start']);
        }

        if ($filters['end']) {
            $query->whereDate('created_at', '<=', $filters['end']);
        }

        $tickets = $query->latest()->get();
        $bankAccounts = BankAccount::all();
        $washers = Washer::where('active', true)->orderBy('name')->get();

        $invQuery = Ticket::where('canceled', false)->where('pending', false);
        if ($filters['start']) {
            $invQuery->whereDate('paid_at', '>=', $filters['start']);
        }
        if ($filters['end']) {
            $invQuery->whereDate('paid_at', '<=', $filters['end']);
        }
        $invoicedTotal = $invQuery->sum('total_amount');

        if ($request->ajax()) {
            return view('tickets.partials.table', [
                'tickets' => $tickets,
                'bankAccounts' => $bankAccounts,
                'washers' => $washers,
                'invoicedTotal' => $invoicedTotal,
            ]);
        }

        return view('tickets.pending', [
            'tickets' => $tickets,
            'filters' => $filters,
            'bankAccounts' => $bankAccounts,
            'washers' => $washers,
            'invoicedTotal' => $invoicedTotal,
        ]);
    }


    public function create()
    {
        $services = Service::where('active', true)
            ->with('prices.vehicleType')
            ->orderBy('name')
            ->get();

        $servicePrices = [];
        foreach ($services as $service) {
            $servicePrices[$service->id] = $service->prices->map(function ($price) {
                return [
                    'id' => $price->id,
                    'label' => $price->label ?? optional($price->vehicleType)->name ?? 'General',
                    'price' => $price->price,
                    'vehicle_type_id' => $price->vehicle_type_id,
                ];
            })->values();
        }

        $products = Product::where('stock', '>', 0)->orderBy('name')->get();
        $productPrices = $products->pluck('price', 'id');
        $productStocks = $products->pluck('stock', 'id');

        $drinks = Drink::where('active', true)->orderBy('name')->get();
        $drinkPrices = $drinks->pluck('price', 'id');

        $serviceDiscounts = Discount::where('discountable_type', Service::class)
            ->where('active', true)
            ->where(function($q){
                $q->whereNull('start_at')->orWhere('start_at','<=', now());
            })
            ->where(function($q){
                $q->whereNull('end_at')->orWhere('end_at','>', now());
            })
            ->get()->mapWithKeys(fn($d)=>[
                $d->discountable_id => ['type'=>$d->amount_type,'amount'=>$d->amount]
            ]);

        $productDiscounts = Discount::where('discountable_type', Product::class)
            ->where('active', true)
            ->where(function($q){
                $q->whereNull('start_at')->orWhere('start_at','<=', now());
            })
            ->where(function($q){
                $q->whereNull('end_at')->orWhere('end_at','>', now());
            })
            ->get()->mapWithKeys(fn($d)=>[
                $d->discountable_id => ['type'=>$d->amount_type,'amount'=>$d->amount]
            ]);

        $drinkDiscounts = Discount::where('discountable_type', Drink::class)
            ->where('active', true)
            ->where(function($q){
                $q->whereNull('start_at')->orWhere('start_at','<=', now());
            })
            ->where(function($q){
                $q->whereNull('end_at')->orWhere('end_at','>', now());
            })
            ->get()->mapWithKeys(fn($d)=>[
                $d->discountable_id => ['type'=>$d->amount_type,'amount'=>$d->amount]
            ]);

        return view('tickets.create', [
            'services' => $services,
            'products' => $products,
            'washers' => Washer::where('active', true)->orderBy('name')->get(),
            'bankAccounts' => BankAccount::all(),
            'servicePrices' => $servicePrices,
            'productPrices' => $productPrices,
            'productStocks' => $productStocks,
            'drinks' => $drinks,
            'drinkPrices' => $drinkPrices,
            'serviceDiscounts' => $serviceDiscounts,
            'productDiscounts' => $productDiscounts,
            'drinkDiscounts' => $drinkDiscounts,
        ]);
    }

    public function store(Request $request)
    {
        return $this->storeMultiple($request);
    }

    public function edit(Ticket $ticket)
    {
        if (!$ticket->pending) {
            abort(403);
        }

        if ($ticket->created_at->lt(now()->subHours(6)) && auth()->user()->role === 'cajero') {
            return redirect()->route('tickets.index')->with('error', 'No se puede editar un ticket con más de 6 horas de creado.');
        }

        $ticket->load(['washes.details.service']);

        $services = Service::where('active', true)
            ->with('prices.vehicleType')
            ->orderBy('name')
            ->get();
        $servicePrices = [];
        foreach ($services as $service) {
            $servicePrices[$service->id] = $service->prices->map(function ($price) {
                return [
                    'id' => $price->id,
                    'label' => $price->label ?? optional($price->vehicleType)->name ?? 'General',
                    'price' => $price->price,
                    'vehicle_type_id' => $price->vehicle_type_id,
                ];
            })->values();
        }

        $products = Product::where('stock', '>', 0)->orderBy('name')->get();
        $productPrices = $products->pluck('price', 'id');
        $productStocks = $products->pluck('stock', 'id');

        $drinks = Drink::where('active', true)->orderBy('name')->get();
        $drinkPrices = $drinks->pluck('price', 'id');

        $serviceDiscounts = Discount::where('discountable_type', Service::class)
            ->where('active', true)
            ->where(function($q){ $q->whereNull('start_at')->orWhere('start_at','<=', now()); })
            ->where(function($q){ $q->whereNull('end_at')->orWhere('end_at','>', now()); })
            ->get()->mapWithKeys(fn($d)=>[
                $d->discountable_id => ['type'=>$d->amount_type,'amount'=>$d->amount]
            ]);

        $productDiscounts = Discount::where('discountable_type', Product::class)
            ->where('active', true)
            ->where(function($q){ $q->whereNull('start_at')->orWhere('start_at','<=', now()); })
            ->where(function($q){ $q->whereNull('end_at')->orWhere('end_at','>', now()); })
            ->get()->mapWithKeys(fn($d)=>[
                $d->discountable_id => ['type'=>$d->amount_type,'amount'=>$d->amount]
            ]);

        $drinkDiscounts = Discount::where('discountable_type', Drink::class)
            ->where('active', true)
            ->where(function($q){ $q->whereNull('start_at')->orWhere('start_at','<=', now()); })
            ->where(function($q){ $q->whereNull('end_at')->orWhere('end_at','>', now()); })
            ->get()->mapWithKeys(fn($d)=>[
                $d->discountable_id => ['type'=>$d->amount_type,'amount'=>$d->amount]
            ]);

        $ticketProducts = $ticket->details->where('type','product')->map(fn($d)=>['id'=>$d->product_id,'qty'=>$d->quantity]);
        $ticketDrinks = $ticket->details->where('type','drink')->map(fn($d)=>['id'=>$d->drink_id,'qty'=>$d->quantity]);

        $ticketWashes = $ticket->washes->flatMap(function ($w) use ($servicePrices, $serviceDiscounts) {
            $serviceDetails = $w->details->where('type', 'service');
            if ($serviceDetails->isEmpty()) {
                return collect();
            }

            return $serviceDetails->values()->map(function ($detail, $index) use ($w, $servicePrices, $serviceDiscounts) {
                $serviceId = $detail->service_id;
                $options = $servicePrices[$serviceId] ?? collect();
                $priceOption = $options->firstWhere('vehicle_type_id', $w->vehicle_type_id);
                if (!$priceOption && $options instanceof \Illuminate\Support\Collection) {
                    $priceOption = $options->first();
                }

                $price = $priceOption['price'] ?? $detail->unit_price;
                $discount = 0;
                if ($disc = ($serviceDiscounts[$serviceId] ?? null)) {
                    $discount = $disc['type'] === 'fixed'
                        ? $disc['amount']
                        : ($price * $disc['amount'] / 100);
                }

                return [
                    'wash' => $w,
                    'service_id' => $serviceId,
                    'service_price_id' => $priceOption['id'] ?? null,
                    'service_name' => optional($detail->service)->name,
                    'price_label' => $priceOption['label'] ?? null,
                    'total' => $detail->subtotal + ($index === 0 ? $w->tip : 0),
                    'discount' => $discount,
                    'tip' => $index === 0 ? $w->tip : 0,
                    'commission_percentage' => $w->commission_percentage,
                ];
            });
        });

        $ticketExtras = $ticket->details->where('type','extra')->map(fn($d)=>[
            'description'=>$d->description,
            'amount'=>$d->unit_price,
        ]);

        return view('tickets.edit', [
            'ticket' => $ticket,
            'services' => $services,
            'products' => $products,
            'washers' => Washer::where('active', true)->orderBy('name')->get(),
            'bankAccounts' => BankAccount::all(),
            'servicePrices' => $servicePrices,
            'productPrices' => $productPrices,
            'productStocks' => $productStocks,
            'drinks' => $drinks,
            'drinkPrices' => $drinkPrices,
            'serviceDiscounts' => $serviceDiscounts,
            'productDiscounts' => $productDiscounts,
            'drinkDiscounts' => $drinkDiscounts,
            'ticketProducts' => $ticketProducts,
            'ticketDrinks' => $ticketDrinks,
            'ticketWashes' => $ticketWashes,
            'ticketExtras' => $ticketExtras,
        ]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        if ($ticket->pending || $request->has('washes')) {
            return $this->updateMultiple($request, $ticket);
        }

        return $this->updateWasherAssignments($request, $ticket);
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket eliminado');
    }

    public function pay(Request $request, Ticket $ticket)
    {
        if (!$ticket->pending) {
            return redirect()->route('tickets.index');
        }

        $request->validate([
            'payment_method' => 'required|in:efectivo,tarjeta,transferencia,mixto',
            'bank_account_id' => 'required_if:payment_method,transferencia|nullable|exists:bank_accounts,id',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        if ($request->paid_amount < $ticket->total_amount) {
            return back()->withErrors(['paid_amount' => 'El monto pagado es menor al total a pagar'])->withInput();
        }

        $ticket->update([
            'payment_method' => $request->payment_method,
            'bank_account_id' => $request->bank_account_id,
            'paid_amount' => $request->paid_amount,
            'change' => $request->paid_amount - $ticket->total_amount,
            'pending' => false,
            'paid_at' => $ticket->created_at,
        ]);

        $printUrl = route('tickets.print', $ticket);

        if ($request->expectsJson()) {
            session()->flash('success', 'Ticket pagado correctamente.');
            session()->flash('print_ticket_url', $printUrl);

            return response()->json([
                'message' => 'Ticket pagado correctamente.',
                'redirect' => route('tickets.index'),
                'print_url' => $printUrl,
            ]);
        }

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket pagado correctamente.')
            ->with('print_ticket_url', $printUrl);
    }

    public function print(Ticket $ticket)
    {
        if ($ticket->pending || $ticket->canceled) {
            abort(404);
        }

        $ticket->load([
            'user',
            'details.service',
            'details.product',
            'details.drink',
            'details.wash.vehicleType',
            'washes.details.service',
            'washes.washer',
            'washes.vehicleType',
            'bankAccount',
        ]);

        $items = [];

        foreach ($ticket->details as $detail) {
            $amount = $detail->subtotal;
            $quantity = $detail->quantity ?? 1;

            switch ($detail->type) {
                case 'service':
                    $serviceName = optional($detail->service)->name ?? 'Servicio';
                    $priceLabel = optional(optional($detail->wash)->vehicleType)->name;
                    $description = $priceLabel
                        ? sprintf('%s (%s)', $serviceName, $priceLabel)
                        : $serviceName;
                    $items[] = [
                        'qty' => $quantity,
                        'description' => $description,
                        'amount' => $amount,
                    ];
                    break;
                case 'product':
                    $items[] = [
                        'qty' => $quantity,
                        'description' => optional($detail->product)->name ?? 'Producto',
                        'amount' => $amount,
                    ];
                    break;
                case 'drink':
                    $items[] = [
                        'qty' => $quantity,
                        'description' => optional($detail->drink)->name ?? 'Bebida',
                        'amount' => $amount,
                    ];
                    break;
                case 'extra':
                    $items[] = [
                        'qty' => $quantity,
                        'description' => $detail->description ?: 'Cargo adicional',
                        'amount' => $amount,
                    ];
                    break;
            }
        }

        foreach ($ticket->washes as $wash) {
            if ($wash->tip > 0) {
                $serviceNames = $wash->details
                    ->where('type', 'service')
                    ->map(fn($d) => optional($d->service)->name ?? 'Servicio')
                    ->implode(', ');
                $washerName = optional($wash->washer)->name;

                $parts = array_filter([
                    'Propina',
                    $serviceNames ?: null,
                    $washerName ? 'para ' . $washerName : null,
                ]);

                $items[] = [
                    'qty' => 1,
                    'description' => implode(' ', $parts),
                    'amount' => $wash->tip,
                ];
            }
        }

        $subtotalBeforeDiscount = $ticket->total_amount + $ticket->discount_total;
        $paidAt = $ticket->paid_at ?? $ticket->created_at;

        return view('tickets.print', [
            'ticket' => $ticket,
            'items' => $items,
            'subtotalBeforeDiscount' => $subtotalBeforeDiscount,
            'paidAt' => $paidAt,
            'totalDiscount' => $ticket->discount_total,
        ]);
    }

    public function cancel(Request $request, Ticket $ticket)
    {
        if ($ticket->canceled) {
            return redirect()->route('tickets.index');
        }

        if ($ticket->created_at->lt(now()->subHours(6)) && auth()->user()->role === 'cajero') {
            return back()->with('error', 'No se puede cancelar un ticket con más de 6 horas de creado.');
        }

        $hasCommission = $ticket->washes->whereNotNull('washer_id')->isNotEmpty();
        $hasTip = $ticket->washes->sum('tip') > 0;
        $rules = ['cancel_reason' => 'required|string|max:255'];
        if ($hasCommission || $hasTip) {
            $rules['pay_washer'] = 'required|in:yes,no';
        }
        $request->validate($rules);

        $payCommission = $hasCommission ? $request->input('pay_washer') === 'yes' : true;
        $payTip = $hasTip ? $request->input('pay_washer') === 'yes' : true;
        $cancelReason = $request->cancel_reason;

        DB::transaction(function() use ($ticket, $payCommission, $payTip, $hasCommission, $hasTip, $cancelReason) {
            foreach ($ticket->details as $detail) {
                if ($detail->type === 'product' && $detail->product) {
                    $detail->product->increment('stock', $detail->quantity);
                    InventoryMovement::create([
                        'product_id' => $detail->product_id,
                        'ticket_id' => $ticket->id,
                        'user_id' => auth()->id(),
                        'movement_type' => 'entrada',
                        'quantity' => $detail->quantity,
                        'concept' => 'Cancelación',
                    ]);
                }
            }

            foreach ($ticket->washes as $wash) {
                $hasService = $wash->details()->where('type', 'service')->exists();
                if (! $hasService) {
                    continue;
                }
                $tip = $wash->tip;
                $commissionAmount = $wash->commission_amount ?? 0;
                if ($wash->washer_id) {
                    $washer = Washer::find($wash->washer_id);
                    $tipMovement = WasherMovement::where('ticket_id', $ticket->id)
                        ->where('washer_id', $wash->washer_id)
                        ->where('description', 'like', '[P]%')
                        ->first();
                    $tipPaid = $tipMovement && $tipMovement->paid;
                    $commissionPaid = $wash->washer_paid;

                    if (! $commissionPaid) {
                        if (! $payCommission) {
                            $washer->decrement('pending_amount', $commissionAmount);
                            $ticket->washer_pending_amount = max(0, $ticket->washer_pending_amount - $commissionAmount);
                            $wash->update(['washer_id' => null]);
                        }
                    } else {
                        if ($payCommission) {
                            WasherPayment::where('washer_id', $wash->washer_id)
                                ->whereDate('payment_date', $ticket->created_at->toDateString())
                                ->update([
                                    'canceled_ticket' => true,
                                    'payment_date' => DB::raw('payment_date'),
                                ]);
                        } else {
                            WasherMovement::create([
                                'washer_id' => $wash->washer_id,
                                'ticket_id' => $ticket->id,
                                'amount' => -$commissionAmount,
                                'description' => 'Cuenta por cobrar - Ganancia de ticket cancelado',
                                'created_at' => $ticket->created_at,
                                'updated_at' => $ticket->created_at,
                            ]);
                        }
                    }

                    if ($tip > 0 && $tipMovement) {
                        if (! $tipPaid) {
                            if (! $payTip) {
                                $washer->decrement('pending_amount', $tip);
                                $ticket->washer_pending_amount = max(0, $ticket->washer_pending_amount - $tip);
                                $tipMovement->delete();
                            }
                        } else {
                            if ($payTip) {
                                WasherPayment::where('washer_id', $wash->washer_id)
                                    ->whereDate('payment_date', $ticket->created_at->toDateString())
                                    ->update([
                                        'canceled_ticket' => true,
                                        'payment_date' => DB::raw('payment_date'),
                                    ]);
                            } else {
                                WasherMovement::create([
                                    'washer_id' => $wash->washer_id,
                                    'ticket_id' => $ticket->id,
                                    'amount' => -$tipMovement->amount,
                                    'description' => 'Cuenta por cobrar - Propina de ticket cancelado',
                                    'created_at' => $ticket->created_at,
                                    'updated_at' => $ticket->created_at,
                                ]);
                            }
                        }
                    }
                } else {
                    if (! $payCommission) {
                        $ticket->washer_pending_amount = max(0, $ticket->washer_pending_amount - $commissionAmount);
                    }
                    if ($tip > 0 && ! $payTip) {
                        $ticket->washer_pending_amount = max(0, $ticket->washer_pending_amount - $tip);
                    }
                }
            }

            if ($ticket->washer_pending_amount > 0) {
                $ticket->washer_pending_amount = 0;
            }

            $ticket->update([
                'canceled' => true,
                'cancel_reason' => $cancelReason,
                'washer_pending_amount' => $ticket->washer_pending_amount,
                'keep_commission_on_cancel' => $hasCommission ? $payCommission : null,
                'keep_tip_on_cancel' => $hasTip ? $payTip : null,
            ]);
        });

        return redirect()->route('tickets.index')->with('success', 'Ticket cancelado');
    }

    private function updateWasherAssignments(Request $request, Ticket $ticket)
    {
        if ($ticket->pending) {
            return $this->updateMultiple($request, $ticket);
        }

        if ($request->has('washers') && is_array($request->input('washers'))) {
            $normalized = [];
            foreach ($request->input('washers') as $key => $value) {
                $normalized[$key] = $value === '' ? null : $value;
            }
            $request->merge(['washers' => $normalized]);
        }

        $request->validate([
            'washers' => ['nullable', 'array'],
            'washers.*' => ['nullable', 'exists:washers,id'],
            'payment_method' => ['required', 'in:efectivo,tarjeta,transferencia,mixto'],
            'bank_account_id' => ['required_if:payment_method,transferencia', 'nullable', 'exists:bank_accounts,id'],
        ]);

        $ticket->load(['washes.details.service', 'washes.vehicleType']);

        $washersInput = $request->input('washers', []);
        $changes = [];
        $errors = [];

        foreach ($ticket->washes as $wash) {
            $newWasherId = $washersInput[$wash->id] ?? $wash->washer_id;
            if ($newWasherId === '') {
                $newWasherId = null;
            }
            $newWasherId = $newWasherId !== null ? (int) $newWasherId : null;

            if ($wash->washer_id === $newWasherId) {
                continue;
            }

            $serviceDetail = $wash->details->firstWhere('type', 'service');
            $serviceModel = $serviceDetail ? $serviceDetail->service : null;
            $serviceName = $serviceModel?->name ?? 'Servicio';
            $priceLabel = optional($wash->vehicleType)->name;
            $tipDescription = '[P] ' . $serviceName . ($priceLabel ? ' | ' . $priceLabel : '');

            $tipMovement = null;
            $tipPaid = false;

            if ($wash->washer_id && $wash->tip > 0) {
                $tipMovement = WasherMovement::where('ticket_id', $ticket->id)
                    ->where('washer_id', $wash->washer_id)
                    ->where('description', $tipDescription)
                    ->first();
                $tipPaid = $tipMovement?->paid ?? false;
            }

            if ($wash->washer_paid || $tipPaid) {
                $errors[] = 'No se puede cambiar el estilista del servicio ' . $serviceName . ' porque ya se registró su pago.';
                continue;
            }

            $changes[] = [
                'wash' => $wash,
                'newWasherId' => $newWasherId,
                'tipMovement' => $tipMovement,
                'tipDescription' => $tipDescription,
            ];
        }

        if (!empty($errors)) {
            return back()->withErrors(['washers' => implode(' ', $errors)])->withInput();
        }

        $ticketPendingAmount = $ticket->washer_pending_amount ?? 0;

        DB::beginTransaction();
        try {
            foreach ($changes as $change) {
                /** @var TicketWash $wash */
                $wash = $change['wash'];
                $oldWasherId = $wash->washer_id;
                $newWasherId = $change['newWasherId'];
                $commissionAmount = $wash->commission_amount ?? 0;
                $tipAmount = $wash->tip ?? 0;
                $totalAmount = $commissionAmount + $tipAmount;

                if ($oldWasherId) {
                    $oldWasher = Washer::find($oldWasherId);
                    if ($oldWasher) {
                        $oldWasher->pending_amount = max(0, $oldWasher->pending_amount - $totalAmount);
                        $oldWasher->save();
                    }
                    if ($tipAmount > 0 && $change['tipMovement']) {
                        $change['tipMovement']->delete();
                    }
                } else {
                    $ticketPendingAmount = max(0, $ticketPendingAmount - $totalAmount);
                }

                if ($newWasherId) {
                    $newWasher = Washer::find($newWasherId);
                    if ($newWasher) {
                        $newWasher->pending_amount += $totalAmount;
                        $newWasher->save();
                    }
                    if ($tipAmount > 0) {
                        WasherMovement::create([
                            'washer_id' => $newWasherId,
                            'ticket_id' => $ticket->id,
                            'amount' => $tipAmount,
                            'description' => $change['tipDescription'],
                            'paid' => false,
                            'created_at' => $ticket->created_at,
                            'updated_at' => $ticket->created_at,
                        ]);
                    }
                } else {
                    $ticketPendingAmount += $totalAmount;
                }

                $wash->update([
                    'washer_id' => $newWasherId,
                    'washer_paid' => false,
                ]);
            }

            $bankAccountId = $request->payment_method === 'transferencia'
                ? $request->bank_account_id
                : null;

            $ticket->update([
                'payment_method' => $request->payment_method,
                'bank_account_id' => $bankAccountId,
                'washer_pending_amount' => max(0, $ticketPendingAmount),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar el ticket: ' . $e->getMessage());
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket actualizado.');
    }

    private function updateMultiple(Request $request, Ticket $ticket)
    {
        if ($ticket->created_at->lt(now()->subHours(6)) && auth()->user()->role === 'cajero') {
            return back()->with('error', 'No se puede editar un ticket con más de 6 horas de creado.');
        }
        $pending = $request->input('ticket_action') === 'pending';

        $rules = [
            'customer_name' => ['required', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/', 'max:255'],
            'customer_phone' => ['nullable','regex:/^[0-9+()\s-]+$/','max:20'],
            'ticket_date' => 'required|date|before_or_equal:today',
            'washes' => ['nullable','array'],
            'washes.*.service_id' => ['required','exists:services,id'],
            'washes.*.service_price_id' => ['required','exists:service_prices,id'],
            'washes.*.washer_id' => 'nullable|exists:washers,id',
            'washes.*.tip' => ['nullable','numeric','min:0'],
            'washes.*.commission_percentage' => ['nullable','numeric','min:0'],
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'quantities' => 'nullable|array',
            'quantities.*' => 'integer|min:1',
            'drink_ids' => 'nullable|array',
            'drink_ids.*' => 'exists:drinks,id',
            'drink_quantities' => 'nullable|array',
            'drink_quantities.*' => 'integer|min:1',
            'charge_descriptions' => 'nullable|array',
            'charge_descriptions.*' => 'nullable|string|max:255',
            'charge_amounts' => 'nullable|array',
            'charge_amounts.*' => 'numeric|min:0',
        ];
        if(!$pending){
            $rules['payment_method'] = 'required|in:efectivo,tarjeta,transferencia,mixto';
            $rules['bank_account_id'] = 'required_if:payment_method,transferencia|nullable|exists:bank_accounts,id';
            $rules['paid_amount'] = 'required|numeric|min:0';
        }
        $messages = [
            'washes.*.service_price_id.required' => 'Debe seleccionar una opción de precio.',
            'washes.*.service_price_id.exists' => 'La opción de precio seleccionada no es válida.',
        ];
        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {
            $ticketDate = Carbon::parse($request->ticket_date)->setTimeFrom($ticket->created_at);
            $total = 0; $discountTotal = 0; $details = []; $productMovements = [];
            $washerPendingAmount = 0; $washInfo = [];
            $defaultCommission = CommissionSetting::currentPercentage();

            foreach ($ticket->washes as $oldWash) {
                $serviceDetail = $oldWash->details->firstWhere('type', 'service');
                $hasService = $serviceDetail !== null;
                $tipOld = $oldWash->tip;
                $oldCommission = $oldWash->commission_amount ?? 0;

                if ($hasService && $oldWash->washer_id) {
                    Washer::whereId($oldWash->washer_id)->decrement('pending_amount', $oldCommission + $tipOld);
                  
                    if ($tipOld > 0) {
                        $serviceName = optional($serviceDetail->service)->name;
                        $label = optional($oldWash->vehicleType)->name;
                        $description = '[P] '.$serviceName.($label ? ' | '.$label : '');

                        WasherMovement::where('ticket_id', $ticket->id)
                            ->where('washer_id', $oldWash->washer_id)
                            ->where('description', $description)
                            ->delete();
                    }
                } elseif ($hasService) {
                    $ticket->washer_pending_amount = max(0, $ticket->washer_pending_amount - ($oldCommission + $tipOld));
                }
            }
            foreach ($ticket->details as $det) {
                if ($det->type === 'product' && $det->product) {
                    $det->product->increment('stock',$det->quantity);
                    InventoryMovement::where('ticket_id',$ticket->id)
                        ->where('product_id',$det->product_id)
                        ->where('movement_type','salida')->delete();
                }
            }
            $ticket->details()->delete();
            $ticket->washes()->delete();

            $washes = $request->input('washes', []);

            foreach ($washes as $wash) {
                $service = Service::where('active', true)
                    ->with('prices.vehicleType')
                    ->find($wash['service_id']);

                if (!$service) {
                    continue;
                }

                $prices = $service->prices;
              
                if ($prices->isEmpty()) {
                    DB::rollBack();
                    $message = ['washes' => ['El servicio seleccionado no tiene opciones de precio configuradas.']];
                    if ($request->expectsJson()) {
                        return response()->json(['errors' => $message], 422);
                    }
                    return back()->withErrors($message)->withInput();
                }

                $priceOption = $prices->firstWhere('id', (int) $wash['service_price_id']);
                if (!$priceOption) {
                    DB::rollBack();
                    $message = ['washes' => ['Debe seleccionar una opción de precio válida para cada servicio.']];
                    if ($request->expectsJson()) {
                        return response()->json(['errors' => $message], 422);
                    }
                    return back()->withErrors($message)->withInput();
                }

                $price = $priceOption->price;
                $discount = Discount::where('discountable_type', Service::class)
                    ->where('discountable_id', $service->id)
                    ->where('active', true)
                    ->where(function ($q) {
                        $q->whereNull('start_at')->orWhere('start_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_at')->orWhere('end_at', '>', now());
                    })
                    ->first();

                if ($discount && $discount->end_at && $discount->end_at->isPast()) {
                    $discount->update(['active' => false]);
                    $discount = null;
                }

                $discValue = 0;
                if ($discount) {
                    $discValue = $discount->amount_type === 'fixed'
                        ? $discount->amount
                        : ($price * $discount->amount / 100);
                    $price = max(0, $price - $discValue);
                }

                $tip = isset($wash['tip']) ? floatval($wash['tip']) : 0;
                $commissionPercentage = (isset($wash['commission_percentage']) && $wash['commission_percentage'] !== '')
                    ? max(0, floatval($wash['commission_percentage']))
                    : $defaultCommission;
                $commissionAmount = round($price * $commissionPercentage / 100, 2);

                $washDetails = [[
                    'type' => 'service',
                    'service_id' => $service->id,
                    'product_id' => null,
                    'quantity' => 1,
                    'unit_price' => $price,
                    'discount_amount' => $discValue,
                    'subtotal' => $price,
                ]];

                $total += $price + $tip;
                $discountTotal += $discValue;

                if (empty($wash['washer_id'])) {
                    $washerPendingAmount += $commissionAmount + $tip;
                }

                $washInfo[] = [
                    'data' => [
                        'service_id' => $service->id,
                        'service_price_id' => $priceOption->id,
                        'vehicle_type_id' => $priceOption->vehicle_type_id,
                        'price_label' => $priceOption->label ?? optional($priceOption->vehicleType)->name,
                        'service_name' => $service->name,
                        'washer_id' => $wash['washer_id'] ?? null,
                        'tip' => $tip,
                        'commission_percentage' => $commissionPercentage,
                        'commission_amount' => $commissionAmount,
                    ],
                    'details' => $washDetails,
                    'has_service' => true,
                ];
            }

            if($request->product_ids){
                foreach($request->product_ids as $index=>$productId){
                    $product=Product::find($productId);
                    $qty=$request->quantities[$index];
                    if(!$product || $product->stock < $qty){
                        DB::rollBack();
                        return back()->withErrors(['quantities'=>['Stock insuficiente para '.($product->name ?? 'producto')]])->withInput();
                    }
                    $price=$product->price;
                    $discount=Discount::where('discountable_type',Product::class)
                        ->where('discountable_id',$productId)
                        ->where('active',true)
                        ->where(function($q){$q->whereNull('start_at')->orWhere('start_at','<=',now());})
                        ->where(function($q){$q->whereNull('end_at')->orWhere('end_at','>',now());})
                        ->first();
                    $discValue=0;
                    if($discount){
                        if($discount->end_at && $discount->end_at->isPast()){
                            $discount->update(['active'=>false]);
                        }else{
                            $discValue=$discount->amount_type==='fixed'? $discount->amount : ($price*$discount->amount/100);
                            $price=max(0,$price-$discValue);
                        }
                    }
                    $subtotal=$price*$qty;
                    $details[]=['type'=>'product','service_id'=>null,'product_id'=>$productId,'quantity'=>$qty,'unit_price'=>$price,'discount_amount'=>$discValue,'subtotal'=>$subtotal];
                    $total+=$subtotal; $discountTotal+=$discValue*$qty;
                    $product->decrement('stock',$qty);
                    $productMovements[]=['product_id'=>$productId,'user_id'=>auth()->id(),'movement_type'=>'salida','quantity'=>$qty,'concept'=>'Venta'];
                }
            }

            if($request->drink_ids){
                foreach($request->drink_ids as $index=>$drinkId){
                    $drink=Drink::where('active',true)->find($drinkId);
                    $qty=$request->drink_quantities[$index];
                    if(!$drink){
                        DB::rollBack();
                        return back()->withErrors(['drink_ids'=>['Trago no disponible']])->withInput();
                    }
                    $price=$drink->price;
                    $discount=Discount::where('discountable_type',Drink::class)
                        ->where('discountable_id',$drinkId)
                        ->where('active',true)
                        ->where(function($q){$q->whereNull('start_at')->orWhere('start_at','<=',now());})
                        ->where(function($q){$q->whereNull('end_at')->orWhere('end_at','>',now());})
                        ->first();
                    $discValue=0;
                    if($discount){
                        if($discount->end_at && $discount->end_at->isPast()){
                            $discount->update(['active'=>false]);
                        }else{
                            $discValue=$discount->amount_type==='fixed'? $discount->amount : ($price*$discount->amount/100);
                            $price=max(0,$price-$discValue);
                        }
                    }
                    $subtotal=$price*$qty;
                    $details[]=['type'=>'drink','service_id'=>null,'product_id'=>null,'drink_id'=>$drinkId,'quantity'=>$qty,'unit_price'=>$price,'discount_amount'=>$discValue,'subtotal'=>$subtotal];
                    $total+=$subtotal; $discountTotal+=$discValue*$qty;
                }
            }

            if($request->charge_descriptions){
                foreach($request->charge_descriptions as $i=>$desc){
                    $amount = floatval($request->charge_amounts[$i] ?? 0);
                    if($desc && $amount > 0){
                        $details[]=[
                            'type'=>'extra','service_id'=>null,'product_id'=>null,'drink_id'=>null,
                            'quantity'=>1,'unit_price'=>$amount,'discount_amount'=>0,'subtotal'=>$amount,'description'=>$desc
                        ];
                        $total+=$amount;
                    }
                }
            }

            if(empty($washInfo) && empty($details)){
                DB::rollBack();
                return back()->withErrors(['washes'=>['Debe agregar al menos un servicio, producto, trago o cargo adicional']])->withInput();
            }
            if(!$pending && $request->paid_amount < $total){
                DB::rollBack();
                return back()->withErrors(['paid_amount'=>['El monto pagado es menor al total a pagar']])->withInput();
            }

            $ticket->update([
                'customer_name'=>$request->customer_name,
                'customer_phone'=>$request->customer_phone,
                'total_amount'=>$total,
                'paid_amount'=>$pending ? 0 : $request->paid_amount,
                'change'=>$pending ? 0 : ($request->paid_amount - $total),
                'discount_total'=>$discountTotal,
                'payment_method'=>$pending ? null : $request->payment_method,
                'bank_account_id'=>$pending ? null : $request->bank_account_id,
                'washer_pending_amount'=>$washerPendingAmount,
                'pending'=>$pending,
                'paid_at'=>$pending ? null : $ticketDate,
                'created_at'=>$ticketDate,
            ]);

            foreach($washInfo as $info){
                $washData=$info['data'];
                $wash = TicketWash::create([
                    'ticket_id'=>$ticket->id,
                    'vehicle_id'=>null,
                    'vehicle_type_id'=>$washData['vehicle_type_id'],
                    'washer_id'=>$washData['washer_id'] ?: null,
                    'washer_paid'=>false,
                    'tip'=>$washData['tip'],
                    'commission_percentage' => $washData['commission_percentage'],
                    'commission_amount' => $washData['commission_amount'],
                ]);
                foreach($info['details'] as $d){
                    $d['ticket_id']=$ticket->id;
                    $d['ticket_wash_id']=$wash->id;
                    TicketDetail::create($d);
                }
                if(!empty($washData['washer_id']) && $info['has_service']){
                    $increment = $washData['commission_amount'] + $washData['tip'];
                    Washer::whereId($washData['washer_id'])->increment('pending_amount',$increment);
                    if($washData['tip'] > 0){
                        WasherMovement::create([
                            'washer_id'=>$washData['washer_id'],
                            'ticket_id'=>$ticket->id,
                            'amount'=>$washData['tip'],
                            'description'=>'[P] '.$washData['service_name'].($washData['price_label'] ? ' | '.$washData['price_label'] : ''),
                            'created_at'=>$ticketDate,
                            'updated_at'=>$ticketDate,
                        ]);
                    }
                }
            }
            foreach($details as $d){
                if(!isset($d['ticket_id'])){
                    $d['ticket_id']=$ticket->id;
                    TicketDetail::create($d);
                }
            }
            foreach($productMovements as $mov){
                $mov['ticket_id']=$ticket->id;
                InventoryMovement::create($mov);
            }
            DB::commit();

            $message = 'Ticket actualizado.';
            $printUrl = $pending ? null : route('tickets.print', $ticket);

            if ($request->expectsJson()) {
                session()->flash('success', $message);
                if ($printUrl) {
                    session()->flash('print_ticket_url', $printUrl);
                }

                return response()->json([
                    'message' => $message,
                    'redirect' => route('tickets.index'),
                    'print_url' => $printUrl,
                ]);
            }

            $redirect = redirect()->route('tickets.index')->with('success', $message);
            if ($printUrl) {
                $redirect = $redirect->with('print_ticket_url', $printUrl);
            }

            return $redirect;
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error','Error actualizando ticket: '.$e->getMessage());
        }
    }
    private function storeMultiple(\Illuminate\Http\Request $request)
    {
        $pending = $request->input('ticket_action') === 'pending';

        $rules = [
            'customer_name' => ['required', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/', 'max:255'],
            'customer_phone' => ['nullable','regex:/^[0-9+()\s-]+$/','max:20'],
            'ticket_date' => 'required|date|before_or_equal:today',
            'washes' => ['nullable','array'],
            'washes.*.service_id' => ['required','exists:services,id'],
            'washes.*.service_price_id' => ['required','exists:service_prices,id'],
            'washes.*.washer_id' => 'nullable|exists:washers,id',
            'washes.*.tip' => ['nullable','numeric','min:0'],
            'washes.*.commission_percentage' => ['nullable','numeric','min:0'],
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'quantities' => 'nullable|array',
            'quantities.*' => 'integer|min:1',
            'drink_ids' => 'nullable|array',
            'drink_ids.*' => 'exists:drinks,id',
            'drink_quantities' => 'nullable|array',
            'drink_quantities.*' => 'integer|min:1',
            'charge_descriptions' => 'nullable|array',
            'charge_descriptions.*' => 'nullable|string|max:255',
            'charge_amounts' => 'nullable|array',
            'charge_amounts.*' => 'numeric|min:0',
        ];

        if (!$pending) {
            $rules['payment_method'] = 'required|in:efectivo,tarjeta,transferencia,mixto';
            $rules['bank_account_id'] = 'required_if:payment_method,transferencia|nullable|exists:bank_accounts,id';
            $rules['paid_amount'] = 'required|numeric|min:0';
        }

        $messages = [
            'customer_name.required' => 'El nombre del cliente es obligatorio.',
            'customer_name.max' => 'El nombre del cliente es demasiado largo.',
            'customer_name.regex' => 'El nombre solo puede contener letras.',
            'customer_phone.regex' => 'El teléfono solo puede contener números y caracteres + - ()',
            'ticket_date.required' => 'La fecha del ticket es obligatoria.',
            'ticket_date.date' => 'La fecha del ticket no es válida.',
            'ticket_date.before_or_equal' => 'La fecha del ticket no puede ser futura.',
            'washes.*.service_id.required' => 'Debe seleccionar un servicio.',
            'washes.*.service_id.exists' => 'El servicio seleccionado no es válido.',
            'washes.*.service_price_id.required' => 'Debe seleccionar una opción de precio.',
            'washes.*.service_price_id.exists' => 'La opción de precio seleccionada no es válida.',
            'washes.*.washer_id.exists' => 'El estilista seleccionado no es válido.',
            'washes.*.tip.numeric' => 'La propina debe ser un número válido.',
            'washes.*.tip.min' => 'La propina no puede ser negativa.',
            'product_ids.*.exists' => 'Alguno de los productos seleccionados es inválido.',
            'quantities.*.min' => 'La cantidad debe ser al menos 1.',
            'drink_ids.*.exists' => 'Alguno de los tragos seleccionados es inválido.',
            'drink_quantities.*.min' => 'La cantidad debe ser al menos 1.',
            'charge_amounts.*.min' => 'El cargo adicional debe ser mayor o igual a 0.',
            'payment_method.required' => 'Debe seleccionar un método de pago.',
            'bank_account_id.required_if' => 'Debe seleccionar una cuenta bancaria.',
            'paid_amount.required' => 'Debe ingresar el monto pagado.',
            'paid_amount.numeric' => 'El monto pagado debe ser un número válido.',
            'paid_amount.min' => 'El monto pagado no puede ser negativo.',
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();

        try {
            $ticketDate = Carbon::parse($request->ticket_date)->setTimeFrom(now());
            $total = 0; $discountTotal = 0; $details = []; $productMovements = [];
            $washerPendingAmount = 0; $washInfo = [];
            $defaultCommission = CommissionSetting::currentPercentage();
            $washes = $request->input('washes', []);

            foreach ($washes as $wash) {
                $service = Service::where('active', true)
                    ->with('prices.vehicleType')
                    ->find($wash['service_id']);

                if (!$service) {
                    continue;
                }

                $prices = $service->prices;
                if ($prices->isEmpty()) {
                    DB::rollBack();
                    $message = ['washes' => ['El servicio seleccionado no tiene opciones de precio configuradas.']];
                    if ($request->expectsJson()) {
                        return response()->json(['errors' => $message], 422);
                    }
                    return back()->withErrors($message)->withInput();
                }

                $priceOption = $prices->firstWhere('id', (int) $wash['service_price_id']);
                if (!$priceOption) {
                    DB::rollBack();
                    $message = ['washes' => ['Debe seleccionar una opción de precio válida para cada servicio.']];
                    if ($request->expectsJson()) {
                        return response()->json(['errors' => $message], 422);
                    }
                    return back()->withErrors($message)->withInput();
                }

                $price = $priceOption->price;
                $discount = Discount::where('discountable_type', Service::class)
                    ->where('discountable_id', $service->id)
                    ->where('active', true)
                    ->where(function ($q) {
                        $q->whereNull('start_at')->orWhere('start_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_at')->orWhere('end_at', '>', now());
                    })
                    ->first();

                if ($discount && $discount->end_at && $discount->end_at->isPast()) {
                    $discount->update(['active' => false]);
                    $discount = null;
                }

                $discValue = 0;
                if ($discount) {
                    $discValue = $discount->amount_type === 'fixed'
                        ? $discount->amount
                        : ($price * $discount->amount / 100);
                    $price = max(0, $price - $discValue);
                }

                $tip = isset($wash['tip']) ? floatval($wash['tip']) : 0;
                $commissionPercentage = (isset($wash['commission_percentage']) && $wash['commission_percentage'] !== '')
                    ? max(0, floatval($wash['commission_percentage']))
                    : $defaultCommission;
                $commissionAmount = round($price * $commissionPercentage / 100, 2);

                $detail = [
                    'type' => 'service',
                    'service_id' => $service->id,
                    'product_id' => null,
                    'quantity' => 1,
                    'unit_price' => $price,
                    'discount_amount' => $discValue,
                    'subtotal' => $price,
                ];

                $total += $price + $tip;
                $discountTotal += $discValue;

                if (empty($wash['washer_id'])) {
                    $washerPendingAmount += $commissionAmount + $tip;
                }

                $washInfo[] = [
                    'data' => [
                        'service_id' => $service->id,
                        'service_price_id' => $priceOption->id,
                        'vehicle_type_id' => $priceOption->vehicle_type_id,
                        'price_label' => $priceOption->label ?? optional($priceOption->vehicleType)->name,
                        'service_name' => $service->name,
                        'washer_id' => $wash['washer_id'] ?? null,
                        'tip' => $tip,
                        'commission_percentage' => $commissionPercentage,
                        'commission_amount' => $commissionAmount,
                    ],
                    'details' => [$detail],
                    'has_service' => true,
                ];
            }

            if ($request->product_ids) {
                foreach ($request->product_ids as $index => $productId) {
                    $product = Product::find($productId);
                    $qty = $request->quantities[$index];
                    if (!$product || $product->stock < $qty) {
                        DB::rollBack();
                        $message = ['quantities' => ['Stock insuficiente para ' . ($product->name ?? 'producto')]];
                        if ($request->expectsJson()) {
                            return response()->json(['errors' => $message], 422);
                        }
                        return back()->withErrors($message)->withInput();
                    }
                    $price = $product->price;
                    $discount = Discount::where('discountable_type', Product::class)
                        ->where('discountable_id', $productId)
                        ->where('active', true)
                        ->where(function($q){ $q->whereNull('start_at')->orWhere('start_at','<=', now()); })
                        ->where(function($q){ $q->whereNull('end_at')->orWhere('end_at','>', now()); })
                        ->first();
                    if ($discount && $discount->end_at && $discount->end_at->isPast()) {
                        $discount->update(['active' => false]);
                        $discount = null;
                    }
                    $discValue = 0;
                    if ($discount) {
                        $discValue = $discount->amount_type === 'fixed' ? $discount->amount : ($price * $discount->amount / 100);
                        $price = max(0, $price - $discValue);
                    }
                    $subtotal = $price * $qty;
                    $details[] = [
                        'type' => 'product',
                        'service_id' => null,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'discount_amount' => $discValue,
                        'subtotal' => $subtotal,
                    ];
                    $total += $subtotal; $discountTotal += $discValue * $qty;
                    $product->decrement('stock', $qty);
                    $productMovements[] = [
                        'product_id' => $productId,
                        'user_id' => auth()->id(),
                        'movement_type' => 'salida',
                        'quantity' => $qty,
                        'concept' => 'Venta',
                    ];
                }
            }

            if ($request->drink_ids) {
                foreach ($request->drink_ids as $index => $drinkId) {
                    $drink = Drink::where('active', true)->find($drinkId);
                    $qty = $request->drink_quantities[$index];
                    if (!$drink) {
                        DB::rollBack();
                        $message = ['drink_ids' => ['Trago no disponible']];
                        if ($request->expectsJson()) {
                            return response()->json(['errors' => $message], 422);
                        }
                        return back()->withErrors($message)->withInput();
                    }
                    $price = $drink->price;
                    $discount = Discount::where('discountable_type', Drink::class)
                        ->where('discountable_id', $drinkId)
                        ->where('active', true)
                        ->where(function($q){ $q->whereNull('start_at')->orWhere('start_at','<=', now()); })
                        ->where(function($q){ $q->whereNull('end_at')->orWhere('end_at','>', now()); })
                        ->first();
                    if ($discount && $discount->end_at && $discount->end_at->isPast()) {
                        $discount->update(['active' => false]);
                        $discount = null;
                    }
                    $discValue = 0;
                    if ($discount) {
                        $discValue = $discount->amount_type === 'fixed' ? $discount->amount : ($price * $discount->amount / 100);
                        $price = max(0, $price - $discValue);
                    }
                    $subtotal = $price * $qty;
                    $details[] = [
                        'type' => 'drink',
                        'service_id' => null,
                        'product_id' => null,
                        'drink_id' => $drinkId,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'discount_amount' => $discValue,
                        'subtotal' => $subtotal,
                    ];
                    $total += $subtotal; $discountTotal += $discValue * $qty;
                }
            }

            if ($request->charge_descriptions) {
                foreach ($request->charge_descriptions as $i => $desc) {
                    $amount = floatval($request->charge_amounts[$i] ?? 0);
                    if ($desc && $amount > 0) {
                        $details[] = [
                            'type' => 'extra',
                            'service_id' => null,
                            'product_id' => null,
                            'drink_id' => null,
                            'quantity' => 1,
                            'unit_price' => $amount,
                            'discount_amount' => 0,
                            'subtotal' => $amount,
                            'description' => $desc,
                        ];
                        $total += $amount;
                    }
                }
            }

            $serviceCount = 0;
            foreach ($washInfo as $info) {
                $serviceCount += count($info['details']);
            }

            if (($serviceCount + count($details)) === 0) {
                DB::rollBack();
                $message = ['washes' => ['Debe agregar al menos un servicio, producto, trago o cargo adicional']];
                if ($request->expectsJson()) {
                    return response()->json(['errors' => $message], 422);
                }
                return back()->withErrors($message)->withInput();
            }

            if (!$pending && $request->paid_amount < $total) {
                DB::rollBack();
                $message = ['paid_amount' => ['El monto pagado es menor al total a pagar']];
                if ($request->expectsJson()) {
                    return response()->json(['errors' => $message], 422);
                }
                return back()->withErrors($message)->withInput();
            }

            $ticket = Ticket::create([
                'user_id' => auth()->id(),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'total_amount' => $total,
                'paid_amount' => $pending ? 0 : $request->paid_amount,
                'change' => $pending ? 0 : ($request->paid_amount - $total),
                'discount_total' => $discountTotal,
                'payment_method' => $pending ? null : $request->payment_method,
                'bank_account_id' => $pending ? null : $request->bank_account_id,
                'washer_pending_amount' => $washerPendingAmount,
                'pending' => $pending,
                'paid_at' => $pending ? null : $ticketDate,
                'created_at' => $ticketDate,
            ]);

            foreach ($washInfo as $info) {
                $washData = $info['data'];

                $wash = TicketWash::create([
                    'ticket_id' => $ticket->id,
                    'vehicle_id' => null,
                    'vehicle_type_id' => $washData['vehicle_type_id'],
                    'washer_id' => $washData['washer_id'] ?: null,
                    'washer_paid' => false,
                    'tip' => $washData['tip'],
                    'commission_percentage' => $washData['commission_percentage'],
                    'commission_amount' => $washData['commission_amount'],
                ]);

                foreach ($info['details'] as $d) {
                    $d['ticket_id'] = $ticket->id;
                    $d['ticket_wash_id'] = $wash->id;
                    TicketDetail::create($d);
                }

                if ($washData['washer_id'] && $info['has_service']) {
                    $increment = $washData['commission_amount'] + $washData['tip'];
                    Washer::whereId($washData['washer_id'])->increment('pending_amount', $increment);
                    if ($washData['tip'] > 0) {
                        WasherMovement::create([
                            'washer_id' => $washData['washer_id'],
                            'ticket_id' => $ticket->id,
                            'amount' => $washData['tip'],
                            'description' => '[P] '.$washData['service_name'].
                                ($washData['price_label'] ? ' | '.$washData['price_label'] : ''),
                            'created_at' => $ticketDate,
                            'updated_at' => $ticketDate,
                        ]);
                    }
                }
            }

            foreach ($details as $d) {
                if (!isset($d['ticket_id'])) {
                    $d['ticket_id'] = $ticket->id;
                    TicketDetail::create($d);
                }
            }

            foreach ($productMovements as $mov) {
                $mov['ticket_id'] = $ticket->id;
                InventoryMovement::create($mov);
            }

            DB::commit();

            $message = 'Ticket generado correctamente.';
            $printUrl = $pending ? null : route('tickets.print', $ticket);

            if ($request->expectsJson()) {
                session()->flash('success', $message);
                if ($printUrl) {
                    session()->flash('print_ticket_url', $printUrl);
                }

                return response()->json([
                    'message' => $message,
                    'redirect' => route('tickets.index'),
                    'print_url' => $printUrl,
                ]);
            }

            $redirect = redirect()->route('tickets.index')->with('success', $message);
            if ($printUrl) {
                $redirect = $redirect->with('print_ticket_url', $printUrl);
            }

            return $redirect;

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error generando ticket: ' . $e->getMessage());
        }
    }
}
