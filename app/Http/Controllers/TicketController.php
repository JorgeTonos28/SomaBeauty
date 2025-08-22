<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TicketDetail;
use App\Models\InventoryMovement;
use App\Models\VehicleType;
use App\Models\Vehicle;
use App\Models\Washer;
use App\Models\WasherMovement;
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

        $query = Ticket::with(['details', 'bankAccount'])->where('canceled', false);

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
        $query = Ticket::with(['details', 'bankAccount'])->where('canceled', true);

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

        $query = Ticket::with(['details', 'bankAccount'])
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
        $services = Service::where('active', true)->with('prices')->get();
        $servicePrices = [];
        foreach ($services as $service) {
            foreach ($service->prices as $price) {
                $servicePrices[$service->id][$price->vehicle_type_id] = $price->price;
            }
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
            'vehicleTypes' => VehicleType::all(),
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
        $pending = $request->input('ticket_action') === 'pending';

        $serviceIds = $request->input('service_ids', []);
        $hasWash = Service::whereIn('id', $serviceIds)
            ->where('name', 'like', 'Lavado%')
            ->exists();

        $rules = [
            'customer_name' => ['required', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/', 'max:255'],
            'vehicle_type_id' => [$hasWash ? 'required' : 'nullable', 'exists:vehicle_types,id'],
            'customer_phone' => ['nullable','regex:/^[0-9+()\s-]+$/','max:20'],
            'plate' => [$hasWash ? 'required' : 'nullable', 'alpha_num', 'max:20'],
            'brand' => [$hasWash ? 'required' : 'nullable', 'regex:/^[A-Za-z0-9\s]+$/', 'max:50'],
            'model' => [$hasWash ? 'required' : 'nullable', 'regex:/^[A-Za-z0-9\s]+$/', 'max:50'],
            'color' => [$hasWash ? 'required' : 'nullable', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/', 'max:50'],
            'year' => 'nullable|integer|between:1890,' . date('Y'),
            'washer_id' => 'nullable|exists:washers,id',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'exists:services,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'quantities' => 'nullable|array',
            'quantities.*' => 'integer|min:1',
            'drink_ids' => 'nullable|array',
            'drink_ids.*' => 'exists:drinks,id',
            'drink_quantities' => 'nullable|array',
            'drink_quantities.*' => 'integer|min:1',
            'ticket_date' => 'required|date|before_or_equal:today',
        ];
        if (!$pending) {
            $rules['payment_method'] = 'required|in:efectivo,tarjeta,transferencia,mixto';
            $rules['bank_account_id'] = 'required_if:payment_method,transferencia|nullable|exists:bank_accounts,id';
            $rules['paid_amount'] = 'required|numeric|min:0';
        }

        $request->validate($rules, [
            'customer_name.required' => 'El nombre del cliente es obligatorio.',
            'customer_name.max' => 'El nombre del cliente es demasiado largo.',
            'vehicle_type_id.required' => 'El tipo de vehículo es obligatorio.',
            'vehicle_type_id.exists' => 'El tipo de vehículo seleccionado no es válido.',
            'plate.required' => 'La placa es obligatoria.',
            'plate.alpha_num' => 'La placa solo puede contener letras y numeros.',
            'brand.required' => 'La marca es obligatoria.',
            'brand.regex' => 'La marca solo puede contener letras y numeros.',
            'model.required' => 'El modelo es obligatorio.',
            'model.regex' => 'El modelo solo puede contener letras y numeros.',
            'color.required' => 'El color es obligatorio.',
            'color.regex' => 'El color solo puede contener letras.',
            'customer_name.regex' => 'El nombre solo puede contener letras.',
            'customer_phone.regex' => 'El teléfono solo puede contener números y caracteres + - ()',
            'year.between' => 'El año debe estar entre 1890 y '.date('Y').'.',
            'washer_id.exists' => 'El lavador seleccionado no es válido.',
            'service_ids.*.exists' => 'Alguno de los servicios seleccionados es inválido.',
            'product_ids.*.exists' => 'Alguno de los productos seleccionados es inválido.',
            'quantities.*.min' => 'La cantidad debe ser al menos 1.',
            'drink_ids.*.exists' => 'Alguno de los tragos seleccionados es inválido.',
            'drink_quantities.*.min' => 'La cantidad debe ser al menos 1.',
            'payment_method.required' => 'Debe seleccionar un método de pago.',
            'bank_account_id.required_if' => 'Debe seleccionar una cuenta bancaria.',
            'paid_amount.required' => 'Debe ingresar el monto pagado.',
            'paid_amount.numeric' => 'El monto pagado debe ser un número válido.',
            'paid_amount.min' => 'El monto pagado no puede ser negativo.',
            'ticket_date.required' => 'La fecha del ticket es obligatoria.',
            'ticket_date.date' => 'La fecha del ticket no es válida.',
            'ticket_date.before_or_equal' => 'La fecha del ticket no puede ser futura.'
        ]);

        DB::beginTransaction();

        try {
            $ticketDate = Carbon::parse($request->ticket_date)->setTimeFrom(now());
            $vehicleType = $request->vehicle_type_id ? VehicleType::findOrFail($request->vehicle_type_id) : null;
            $total = 0;
            $discountTotal = 0;
            $details = [];
            $hasService = false;

            // Servicios
            foreach ($request->service_ids ?? [] as $serviceId) {
                $service = Service::where('active', true)->find($serviceId);
                if (!$service || !$vehicleType) {
                    continue;
                }
                $priceRow = $service->prices()->where('vehicle_type_id', $vehicleType->id)->first();
                $price = $priceRow ? $priceRow->price : 0;

                $discount = Discount::where('discountable_type', Service::class)
                    ->where('discountable_id', $serviceId)
                    ->where('active', true)
                    ->where(function($q){
                        $q->whereNull('start_at')->orWhere('start_at','<=', now());
                    })
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

                $details[] = [
                    'type' => 'service',
                    'service_id' => $serviceId,
                    'product_id' => null,
                    'quantity' => 1,
                    'unit_price' => $price,
                    'discount_amount' => $discValue,
                    'subtotal' => $price,
                ];

                $total += $price;
                $discountTotal += $discValue;
                $hasService = true;
            }

            // Productos
            $productMovements = [];
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
                        ->where(function($q){
                            $q->whereNull('start_at')->orWhere('start_at','<=', now());
                        })
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

                    $total += $subtotal;
                    $discountTotal += $discValue * $qty;

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

            // Tragos
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
                        ->where(function($q){
                            $q->whereNull('start_at')->orWhere('start_at','<=', now());
                        })
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

                    $total += $subtotal;
                    $discountTotal += $discValue * $qty;
                }
            }

            if (count($details) === 0) {
                DB::rollBack();
                $message = ['service_ids' => ['Debe agregar al menos un servicio, producto o trago']];
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

            $vehicle = null;
            if ($request->filled('plate')) {
                $vehicle = Vehicle::where('plate', $request->plate)->first();
                if (!$vehicle) {
                    $vehicle = Vehicle::create([
                        'customer_name' => $request->customer_name,
                        'vehicle_type_id' => $request->vehicle_type_id,
                        'plate' => $request->plate,
                        'brand' => $request->brand,
                        'model' => $request->model,
                        'color' => $request->color,
                        'year' => $request->year,
                    ]);
                } elseif (!$vehicle->year && $request->filled('year')) {
                    $vehicle->update(['year' => $request->year]);
                }
            }

            $ticket = Ticket::create([
                'user_id' => auth()->id(),
                'washer_id' => $request->washer_id,
                'vehicle_type_id' => $request->vehicle_type_id,
                'vehicle_id' => optional($vehicle)->id,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'total_amount' => $total,
                'paid_amount' => $pending ? 0 : $request->paid_amount,
                'change' => $pending ? 0 : ($request->paid_amount - $total),
                'discount_total' => $discountTotal,
                'payment_method' => $pending ? null : $request->payment_method,
                'bank_account_id' => $pending ? null : $request->bank_account_id,
                'washer_pending_amount' => $hasService ? 100 : 0,
                'pending' => $pending,
                'paid_at' => $pending ? null : $ticketDate,
                'created_at' => $ticketDate,
            ]);

            foreach ($details as $detail) {
                $detail['ticket_id'] = $ticket->id;
                TicketDetail::create($detail);
            }

            foreach ($productMovements as $mov) {
                $mov['ticket_id'] = $ticket->id;
                InventoryMovement::create($mov);
            }

            if ($request->washer_id && $hasService) {
                Washer::whereId($request->washer_id)->increment('pending_amount', 100);
            }

            DB::commit();

            return redirect()->route('tickets.index')
                ->with('success', 'Ticket generado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error generando ticket: ' . $e->getMessage());
        }
    }

    public function edit(Ticket $ticket)
    {
        if (!$ticket->pending) {
            abort(403);
        }

        if ($ticket->created_at->lt(now()->subHours(6)) && auth()->user()->role === 'cajero') {
            return redirect()->route('tickets.index')->with('error', 'No se puede editar un ticket con más de 6 horas de creado.');
        }

        $services = Service::where('active', true)->with('prices')->get();
        $servicePrices = [];
        foreach ($services as $service) {
            foreach ($service->prices as $price) {
                $servicePrices[$service->id][$price->vehicle_type_id] = $price->price;
            }
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

        $ticketServices = $ticket->details->where('type','service')->pluck('service_id')->toArray();
        $ticketProducts = $ticket->details->where('type','product')->map(fn($d)=>['id'=>$d->product_id,'qty'=>$d->quantity]);
        $ticketDrinks = $ticket->details->where('type','drink')->map(fn($d)=>['id'=>$d->drink_id,'qty'=>$d->quantity]);
        $hasWash = Service::whereIn('id', $ticketServices)
            ->where('name', 'like', 'Lavado%')
            ->exists();

        return view('tickets.edit', [
            'ticket' => $ticket,
            'services' => $services,
            'vehicleTypes' => VehicleType::all(),
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
            'ticketServices' => $ticketServices,
            'ticketProducts' => $ticketProducts,
            'ticketDrinks' => $ticketDrinks,
            'hasWash' => $hasWash,
        ]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        if ($ticket->created_at->lt(now()->subHours(6)) && auth()->user()->role === 'cajero') {
            return back()->with('error', 'No se puede editar un ticket con más de 6 horas de creado.');
        }
        if ($ticket->pending && $request->has('ticket_action')) {
            $pending = $request->input('ticket_action') === 'pending';

            $serviceIds = $request->input('service_ids', []);
            $hasWash = Service::whereIn('id', $serviceIds)
                ->where('name', 'like', 'Lavado%')->exists();

            $rules = [
                'customer_name' => ['required','regex:/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/','max:255'],
                'vehicle_type_id' => [$hasWash ? 'required' : 'nullable','exists:vehicle_types,id'],
                'customer_phone' => ['nullable','regex:/^[0-9+()\s-]+$/','max:20'],
                'plate' => [$hasWash ? 'required' : 'nullable','alpha_num','max:20'],
                'brand' => [$hasWash ? 'required' : 'nullable','regex:/^[A-Za-z0-9\s]+$/','max:50'],
                'model' => [$hasWash ? 'required' : 'nullable','regex:/^[A-Za-z0-9\s]+$/','max:50'],
                'color' => [$hasWash ? 'required' : 'nullable','regex:/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/','max:50'],
                'year' => 'nullable|integer|between:1890,' . date('Y'),
                'washer_id' => 'nullable|exists:washers,id',
                'service_ids' => 'nullable|array',
                'service_ids.*' => 'exists:services,id',
                'product_ids' => 'nullable|array',
                'product_ids.*' => 'exists:products,id',
                'quantities' => 'nullable|array',
                'quantities.*' => 'integer|min:1',
                'drink_ids' => 'nullable|array',
                'drink_ids.*' => 'exists:drinks,id',
                'drink_quantities' => 'nullable|array',
                'drink_quantities.*' => 'integer|min:1',
                'ticket_date' => 'required|date|before_or_equal:today',
            ];

            if (!$pending) {
                $rules['payment_method'] = 'required|in:efectivo,tarjeta,transferencia,mixto';
                $rules['bank_account_id'] = 'required_if:payment_method,transferencia|nullable|exists:bank_accounts,id';
                $rules['paid_amount'] = 'required|numeric|min:0';
            }

            $request->validate($rules, [
                'ticket_date.before_or_equal' => 'La fecha del ticket no puede ser futura.'
            ]);

            DB::beginTransaction();
            try {
                $ticketDate = Carbon::parse($request->ticket_date)->setTimeFrom($ticket->created_at);
                $oldService = $ticket->details()->where('type','service')->exists();
                if ($oldService) {
                    if ($ticket->washer_id) {
                        Washer::whereId($ticket->washer_id)->decrement('pending_amount',100);
                    } elseif ($ticket->washer_pending_amount > 0) {
                        $ticket->washer_pending_amount = 0;
                    }
                }

                foreach ($ticket->details as $det) {
                    if ($det->type === 'product' && $det->product) {
                        $det->product->increment('stock', $det->quantity);
                        InventoryMovement::where('ticket_id',$ticket->id)
                            ->where('product_id',$det->product_id)
                            ->where('movement_type','salida')->delete();
                    }
                }

                $ticket->details()->delete();

                $vehicleType = $request->vehicle_type_id ? VehicleType::findOrFail($request->vehicle_type_id) : null;
                $total = 0; $discountTotal = 0; $details = []; $productMovements = [];

                foreach ($serviceIds as $serviceId) {
                    $service = Service::where('active',true)->find($serviceId);
                    if (!$service || !$vehicleType) continue;
                    $priceRow = $service->prices()->where('vehicle_type_id',$vehicleType->id)->first();
                    $price = $priceRow?->price ?? 0;
                    $discount = Discount::where('discountable_type',Service::class)
                        ->where('discountable_id',$serviceId)
                        ->where('active',true)
                        ->where(function($q){$q->whereNull('start_at')->orWhere('start_at','<=',now());})
                        ->where(function($q){$q->whereNull('end_at')->orWhere('end_at','>',now());})
                        ->first();
                    $discValue = 0;
                    if($discount){
                        if($discount->end_at && $discount->end_at->isPast()){
                            $discount->update(['active'=>false]);
                        }else{
                            $discValue = $discount->amount_type === 'fixed' ? $discount->amount : ($price * $discount->amount/100);
                            $price = max(0,$price - $discValue);
                        }
                    }
                    $details[] = [
                        'type' => 'service',
                        'service_id' => $serviceId,
                        'product_id' => null,
                        'quantity' => 1,
                        'unit_price' => $price,
                        'discount_amount' => $discValue,
                        'subtotal' => $price,
                    ];
                    $total += $price; $discountTotal += $discValue; $hasWash = true;
                }

                if ($request->product_ids) {
                    foreach ($request->product_ids as $index => $productId) {
                        $product = Product::find($productId);
                        $qty = $request->quantities[$index];
                        if (!$product || $product->stock < $qty) {
                            DB::rollBack();
                            return back()->withErrors(['quantities' => ['Stock insuficiente para '.($product->name ?? 'producto')]])->withInput();
                        }
                        $price = $product->price;
                        $discount = Discount::where('discountable_type',Product::class)
                            ->where('discountable_id',$productId)
                            ->where('active',true)
                            ->where(function($q){$q->whereNull('start_at')->orWhere('start_at','<=',now());})
                            ->where(function($q){$q->whereNull('end_at')->orWhere('end_at','>',now());})
                            ->first();
                        $discValue = 0;
                        if($discount){
                            if($discount->end_at && $discount->end_at->isPast()){
                                $discount->update(['active'=>false]);
                            }else{
                                $discValue = $discount->amount_type==='fixed'? $discount->amount : ($price*$discount->amount/100);
                                $price = max(0,$price - $discValue);
                            }
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
                        $total += $subtotal; $discountTotal += $discValue*$qty;
                        $product->decrement('stock',$qty);
                        $productMovements[] = [
                            'product_id'=>$productId,
                            'user_id'=>auth()->id(),
                            'movement_type'=>'salida',
                            'quantity'=>$qty,
                            'concept'=>'Venta'
                        ];
                    }
                }

                if ($request->drink_ids) {
                    foreach ($request->drink_ids as $index => $drinkId) {
                        $drink = Drink::where('active',true)->find($drinkId);
                        $qty = $request->drink_quantities[$index];
                        if(!$drink){
                            DB::rollBack();
                            return back()->withErrors(['drink_ids'=>['Trago no disponible']])->withInput();
                        }
                        $price = $drink->price;
                        $discount = Discount::where('discountable_type',Drink::class)
                            ->where('discountable_id',$drinkId)
                            ->where('active',true)
                            ->where(function($q){$q->whereNull('start_at')->orWhere('start_at','<=',now());})
                            ->where(function($q){$q->whereNull('end_at')->orWhere('end_at','>',now());})
                            ->first();
                        $discValue = 0;
                        if($discount){
                            if($discount->end_at && $discount->end_at->isPast()){
                                $discount->update(['active'=>false]);
                            }else{
                                $discValue = $discount->amount_type==='fixed'? $discount->amount : ($price*$discount->amount/100);
                                $price = max(0,$price - $discValue);
                            }
                        }
                        $subtotal = $price*$qty;
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
                        $total += $subtotal; $discountTotal += $discValue*$qty;
                    }
                }

                if (count($details) === 0) {
                    DB::rollBack();
                    return back()->withErrors(['service_ids'=>['Debe agregar al menos un servicio, producto o trago']])->withInput();
                }

                if (!$pending && $request->paid_amount < $total) {
                    DB::rollBack();
                    return back()->withErrors(['paid_amount'=>['El monto pagado es menor al total a pagar']])->withInput();
                }

                $vehicle = null;
                if ($request->filled('plate')) {
                    $vehicle = Vehicle::where('plate',$request->plate)->first();
                    if(!$vehicle){
                        $vehicle = Vehicle::create([
                            'customer_name'=>$request->customer_name,
                            'vehicle_type_id'=>$request->vehicle_type_id,
                            'plate'=>$request->plate,
                            'brand'=>$request->brand,
                            'model'=>$request->model,
                            'color'=>$request->color,
                            'year'=>$request->year,
                        ]);
                    } elseif(!$vehicle->year && $request->filled('year')) {
                        $vehicle->update(['year'=>$request->year]);
                    }
                }

                $ticket->update([
                    'washer_id' => $request->washer_id,
                    'vehicle_type_id' => $request->vehicle_type_id,
                    'vehicle_id' => optional($vehicle)->id,
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'total_amount' => $total,
                    'paid_amount' => $pending ? 0 : $request->paid_amount,
                    'change' => $pending ? 0 : ($request->paid_amount - $total),
                    'discount_total' => $discountTotal,
                    'payment_method' => $pending ? null : $request->payment_method,
                    'bank_account_id' => $pending ? null : $request->bank_account_id,
                    'washer_pending_amount' => $hasWash ? 100 : 0,
                    'pending' => $pending,
                    'paid_at' => $pending ? null : $ticketDate,
                    'created_at' => $ticketDate,
                ]);

                foreach ($details as $detail) {
                    $detail['ticket_id'] = $ticket->id;
                    TicketDetail::create($detail);
                }

                foreach ($productMovements as $mov) {
                    $mov['ticket_id'] = $ticket->id;
                    InventoryMovement::create($mov);
                }

                if ($request->washer_id && $hasWash) {
                    Washer::whereId($request->washer_id)->increment('pending_amount',100);
                }

                DB::commit();

                return redirect()->route('tickets.index')->with('success','Ticket actualizado.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error','Error actualizando ticket: '.$e->getMessage());
            }
        }

        $request->validate([
            'washer_id' => 'nullable|exists:washers,id',
            'payment_method' => 'required|in:efectivo,tarjeta,transferencia,mixto',
            'bank_account_id' => 'required_if:payment_method,transferencia|nullable|exists:bank_accounts,id',
        ]);

        DB::transaction(function() use ($ticket, $request) {
            $oldWasher = $ticket->washer_id;
            $newWasher = $request->washer_id;

            $hasService = $ticket->details()->where('type', 'service')->exists();

            if ($oldWasher !== $newWasher && $hasService) {
                if ($oldWasher) {
                    Washer::whereId($oldWasher)->decrement('pending_amount', 100);
                } elseif ($ticket->washer_pending_amount <= 0) {
                    $ticket->washer_pending_amount = 100;
                }

                if ($newWasher) {
                    Washer::whereId($newWasher)->increment('pending_amount', 100);
                    $ticket->washer_pending_amount = 0;
                }

                $ticket->washer_id = $newWasher;
            }

            $ticket->update([
                'payment_method' => $request->payment_method,
                'bank_account_id' => $request->bank_account_id,
                'washer_id' => $ticket->washer_id,
                'washer_pending_amount' => $ticket->washer_pending_amount,
            ]);
        });

        return redirect()->route('tickets.index')->with('success', 'Ticket actualizado.');
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

        return redirect()->route('tickets.index')->with('success', 'Ticket pagado correctamente.');
    }

    public function cancel(Request $request, Ticket $ticket)
    {
        if ($ticket->canceled) {
            return redirect()->route('tickets.index');
        }

        if ($ticket->created_at->lt(now()->subHours(6)) && auth()->user()->role === 'cajero') {
            return back()->with('error', 'No se puede cancelar un ticket con más de 6 horas de creado.');
        }

        $request->validate([
            'cancel_reason' => 'required|string|max:255',
        ]);

        DB::transaction(function() use ($ticket, $request) {
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

            if ($ticket->washer_id) {
                $washer = Washer::find($ticket->washer_id);
                $alreadyPaid = $washer->pending_amount <= 0;
                $washer->decrement('pending_amount', 100);

                WasherMovement::create([
                    'washer_id' => $washer->id,
                    'ticket_id' => $ticket->id,
                    'amount' => -100,
                    'description' => $alreadyPaid
                        ? 'Cuenta por cobrar - Ganancia de ticket cancelado'
                        : 'Ticket Cancelado',
                ]);
            } elseif ($ticket->washer_pending_amount > 0) {
                $ticket->washer_pending_amount = 0;
            }

            $ticket->update([
                'canceled' => true,
                'cancel_reason' => $request->cancel_reason,
                'washer_pending_amount' => $ticket->washer_pending_amount
            ]);
        });

        return redirect()->route('tickets.index')->with('success', 'Ticket cancelado');
    }
}
