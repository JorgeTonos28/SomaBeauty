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
use App\Models\Drink;
use App\Models\Discount;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,cajero']);
    }

    public function index(Request $request)
    {
        $query = Ticket::with(['details', 'bankAccount'])->where('canceled', false);

        if ($request->boolean('pending')) {
            $query->where('pending', true);
        }

        if ($request->filled('start')) {
            $query->whereDate('created_at', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $query->whereDate('created_at', '<=', $request->end);
        }

        $tickets = $query->latest()->paginate(20);

        $bankAccounts = BankAccount::all();

        if ($request->ajax()) {
            return view('tickets.partials.table', [
                'tickets' => $tickets,
                'bankAccounts' => $bankAccounts,
            ]);
        }

        return view('tickets.index', [
            'tickets' => $tickets,
            'filters' => $request->only(['start', 'end', 'pending']),
            'bankAccounts' => $bankAccounts,
        ]);
    }

    public function canceled(Request $request)
    {
        $query = Ticket::with(['details', 'bankAccount'])->where('canceled', true);

        if ($request->filled('start')) {
            $query->whereDate('created_at', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $query->whereDate('created_at', '<=', $request->end);
        }

        $tickets = $query->latest()->paginate(20);

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
        $query = Ticket::with(['details', 'bankAccount'])
            ->where('canceled', false)
            ->where('pending', true);

        if ($request->filled('start')) {
            $query->whereDate('created_at', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $query->whereDate('created_at', '<=', $request->end);
        }

        $tickets = $query->latest()->paginate(20);
        $bankAccounts = BankAccount::all();

        if ($request->ajax()) {
            return view('tickets.partials.table', [
                'tickets' => $tickets,
                'bankAccounts' => $bankAccounts,
            ]);
        }

        return view('tickets.pending', [
            'tickets' => $tickets,
            'filters' => $request->only(['start', 'end']),
            'bankAccounts' => $bankAccounts,
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

        $products = Product::where('stock', '>', 0)->get();
        $productPrices = $products->pluck('price', 'id');
        $productStocks = $products->pluck('stock', 'id');

        $drinks = Drink::where('active', true)->get();
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
            'washers' => Washer::where('active', true)->get(),
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

        $rules = [
            'customer_name' => ['required', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/', 'max:255'],
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
            'plate' => ['required_with:service_ids', 'alpha_num', 'max:20'],
            'brand' => ['required_with:service_ids', 'regex:/^[A-Za-z0-9\s]+$/', 'max:50'],
            'model' => ['required_with:service_ids', 'regex:/^[A-Za-z0-9\s]+$/', 'max:50'],
            'color' => ['required_with:service_ids', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/', 'max:50'],
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
        ];
        if (!$pending) {
            $rules['payment_method'] = 'required|in:efectivo,tarjeta,transferencia,mixto';
            $rules['bank_account_id'] = 'required_if:payment_method,transferencia|nullable|exists:bank_accounts,id';
            $rules['paid_amount'] = 'required|numeric|min:0';
        }

        $request->validate($rules, [
            'customer_name.required' => 'El nombre del cliente es obligatorio.',
            'customer_name.max' => 'El nombre del cliente es demasiado largo.',
            'vehicle_type_id.exists' => 'El tipo de vehículo seleccionado no es válido.',
            'plate.required_with' => 'La placa es obligatoria.',
            'plate.alpha_num' => 'La placa solo puede contener letras y numeros.',
            'brand.required_with' => 'La marca es obligatoria.',
            'brand.regex' => 'La marca solo puede contener letras y numeros.',
            'model.required_with' => 'El modelo es obligatorio.',
            'model.regex' => 'El modelo solo puede contener letras y numeros.',
            'color.required_with' => 'El color es obligatorio.',
            'color.regex' => 'El color solo puede contener letras.',
            'customer_name.regex' => 'El nombre solo puede contener letras.',
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
            'paid_amount.min' => 'El monto pagado no puede ser negativo.'
        ]);

        DB::beginTransaction();

        try {
            $vehicleType = $request->vehicle_type_id ? VehicleType::findOrFail($request->vehicle_type_id) : null;
            $total = 0;
            $discountTotal = 0;
            $details = [];

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
            }

            // Productos
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
                    InventoryMovement::create([
                        'product_id' => $productId,
                        'user_id' => auth()->id(),
                        'movement_type' => 'salida',
                        'quantity' => $qty,
                        'concept' => 'Venta',
                    ]);
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
                'total_amount' => $total,
                'paid_amount' => $pending ? 0 : $request->paid_amount,
                'change' => $pending ? 0 : ($request->paid_amount - $total),
                'discount_total' => $discountTotal,
                'payment_method' => $pending ? null : $request->payment_method,
                'bank_account_id' => $pending ? null : $request->bank_account_id,
                'pending' => $pending,
                'paid_at' => $pending ? null : now(),
            ]);

            foreach ($details as $detail) {
                $detail['ticket_id'] = $ticket->id;
                TicketDetail::create($detail);
            }

            if ($request->washer_id) {
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
        abort(403);
    }

    public function update(Request $request, Ticket $ticket)
    {
        abort(403);
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
            'paid_at' => now(),
        ]);

        return redirect()->route('tickets.index')->with('success', 'Ticket pagado correctamente.');
    }

    public function cancel(Ticket $ticket)
    {
        if ($ticket->canceled) {
            return redirect()->route('tickets.index');
        }

        DB::transaction(function() use ($ticket) {
            foreach ($ticket->details as $detail) {
                if ($detail->type === 'product' && $detail->product) {
                    $detail->product->increment('stock', $detail->quantity);
                    InventoryMovement::create([
                        'product_id' => $detail->product_id,
                        'user_id' => auth()->id(),
                        'movement_type' => 'entrada',
                        'quantity' => $detail->quantity,
                        'concept' => 'Cancelación',
                    ]);
                }
            }

            if ($ticket->washer_id) {
                Washer::whereId($ticket->washer_id)->decrement('pending_amount', 100);
            }

            $ticket->update(['canceled' => true]);
        });

        return redirect()->route('tickets.index')->with('success', 'Ticket cancelado');
    }
}
