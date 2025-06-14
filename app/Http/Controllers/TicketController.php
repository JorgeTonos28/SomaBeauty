<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TicketDetail;
use App\Models\InventoryMovement;
use App\Models\VehicleType;
use App\Models\Washer;
use App\Models\Drink;
use App\Models\Discount;
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
        $query = Ticket::with('details')->where('canceled', false);

        if ($request->filled('start')) {
            $query->whereDate('created_at', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $query->whereDate('created_at', '<=', $request->end);
        }

        $tickets = $query->latest()->paginate(20);

        if ($request->ajax()) {
            return view('tickets.partials.table', [
                'tickets' => $tickets,
            ]);
        }

        return view('tickets.index', [
            'tickets' => $tickets,
            'filters' => $request->only(['start', 'end']),
        ]);
    }

    public function canceled(Request $request)
    {
        $query = Ticket::with('details')->where('canceled', true);

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

        $drinks = Drink::all();
        $drinkPrices = $drinks->pluck('price', 'id');

        return view('tickets.create', [
            'services' => $services,
            'vehicleTypes' => VehicleType::all(),
            'products' => $products,
            'washers' => Washer::all(),
            'servicePrices' => $servicePrices,
            'productPrices' => $productPrices,
            'drinks' => $drinks,
            'drinkPrices' => $drinkPrices,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_cedula' => 'nullable|string|max:50',
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
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
            'payment_method' => 'required|in:efectivo,tarjeta,transferencia,mixto',
            'paid_amount' => 'required|numeric|min:0'
        ], [
            'customer_name.required' => 'El nombre del cliente es obligatorio.',
            'customer_name.max' => 'El nombre del cliente es demasiado largo.',
            'customer_cedula.max' => 'La cédula es demasiado larga.',
            'vehicle_type_id.exists' => 'El tipo de vehículo seleccionado no es válido.',
            'washer_id.exists' => 'El lavador seleccionado no es válido.',
            'service_ids.*.exists' => 'Alguno de los servicios seleccionados es inválido.',
            'product_ids.*.exists' => 'Alguno de los productos seleccionados es inválido.',
            'quantities.*.min' => 'La cantidad debe ser al menos 1.',
            'drink_ids.*.exists' => 'Alguno de los tragos seleccionados es inválido.',
            'drink_quantities.*.min' => 'La cantidad debe ser al menos 1.',
            'payment_method.required' => 'Debe seleccionar un método de pago.',
            'paid_amount.required' => 'Debe ingresar el monto pagado.',
            'paid_amount.numeric' => 'El monto pagado debe ser un número válido.',
            'paid_amount.min' => 'El monto pagado no puede ser negativo.'
        ]);

        DB::beginTransaction();

        try {
            $vehicleType = $request->vehicle_type_id ? VehicleType::findOrFail($request->vehicle_type_id) : null;
            $total = 0;
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
                    ->where(function($q){ $q->whereNull('end_at')->orWhere('end_at','>', now()); })
                    ->first();
                if ($discount && $discount->end_at && $discount->end_at->isPast()) {
                    $discount->update(['active' => false]);
                    $discount = null;
                }
                if ($discount) {
                    $disc = $discount->amount_type === 'fixed' ? $discount->amount : ($price * $discount->amount / 100);
                    $price = max(0, $price - $disc);
                }

                $details[] = [
                    'type' => 'service',
                    'service_id' => $serviceId,
                    'product_id' => null,
                    'quantity' => 1,
                    'unit_price' => $price,
                    'subtotal' => $price,
                ];

                $total += $price;
            }

            // Productos
            if ($request->product_ids) {
                foreach ($request->product_ids as $index => $productId) {
                    $product = Product::find($productId);
                    $qty = $request->quantities[$index];
                    $price = $product->price;
                    $discount = Discount::where('discountable_type', Product::class)
                        ->where('discountable_id', $productId)
                        ->where('active', true)
                        ->where(function($q){ $q->whereNull('end_at')->orWhere('end_at','>', now()); })
                        ->first();
                    if ($discount && $discount->end_at && $discount->end_at->isPast()) {
                        $discount->update(['active' => false]);
                        $discount = null;
                    }
                    if ($discount) {
                        $disc = $discount->amount_type === 'fixed' ? $discount->amount : ($price * $discount->amount / 100);
                        $price = max(0, $price - $disc);
                    }
                    $subtotal = $price * $qty;

                    $details[] = [
                        'type' => 'product',
                        'service_id' => null,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'subtotal' => $subtotal,
                    ];

                    $total += $subtotal;

                    $product->decrement('stock', $qty);
                    InventoryMovement::create([
                        'product_id' => $productId,
                        'user_id' => auth()->id(),
                        'movement_type' => 'salida',
                        'quantity' => $qty,
                    ]);
                }
            }

            // Tragos
            if ($request->drink_ids) {
                foreach ($request->drink_ids as $index => $drinkId) {
                    $drink = Drink::find($drinkId);
                    $qty = $request->drink_quantities[$index];
                    $subtotal = $drink->price * $qty;

                    $details[] = [
                        'type' => 'drink',
                        'service_id' => null,
                        'product_id' => null,
                        'drink_id' => $drinkId,
                        'quantity' => $qty,
                        'unit_price' => $drink->price,
                        'subtotal' => $subtotal,
                    ];

                    $total += $subtotal;
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

            if ($request->paid_amount < $total) {
                DB::rollBack();
                $message = ['paid_amount' => ['El monto pagado es menor al total a pagar']];
                if ($request->expectsJson()) {
                    return response()->json(['errors' => $message], 422);
                }
                return back()->withErrors($message)->withInput();
            }

            $ticket = Ticket::create([
                'user_id' => auth()->id(),
                'washer_id' => $request->washer_id,
                'vehicle_type_id' => $request->vehicle_type_id,
                'customer_name' => $request->customer_name,
                'customer_cedula' => $request->customer_cedula,
                'total_amount' => $total,
                'paid_amount' => $request->paid_amount,
                'change' => $request->paid_amount - $total,
                'payment_method' => $request->payment_method,
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

    public function cancel(Ticket $ticket)
    {
        $ticket->update(['canceled' => true]);
        return redirect()->route('tickets.index')->with('success', 'Ticket cancelado');
    }
}
