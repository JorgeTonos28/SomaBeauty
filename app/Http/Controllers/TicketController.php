<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TicketDetail;
use App\Models\InventoryMovement;
use App\Models\VehicleType;
use App\Models\Washer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,cajero']);
    }

    public function index()
    {
        $tickets = Ticket::latest()->take(10)->get();
    
        return view('tickets.index', [
            'tickets' => $tickets
        ]);
    }


    public function create()
    {
        return view('tickets.create', [
            'services' => Service::where('active', true)->get(),
            'vehicleTypes' => VehicleType::all(),
            'products' => Product::where('stock', '>', 0)->get(),
            'washers' => Washer::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'washer_id' => 'required|exists:washers,id',
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'quantities' => 'nullable|array',
            'quantities.*' => 'integer|min:1',
            'payment_method' => 'required|in:efectivo,tarjeta,transferencia,mixto',
            'paid_amount' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();

        try {
            $vehicleType = VehicleType::findOrFail($request->vehicle_type_id);
            $total = 0;
            $details = [];

            // Servicios
            foreach ($request->service_ids as $serviceId) {
                $service = Service::where('active', true)->find($serviceId);
                if (!$service) {
                    continue;
                }
                $priceRow = $service->prices()->where('vehicle_type_id', $vehicleType->id)->first();
                $price = $priceRow ? $priceRow->price : 0;

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
                    $subtotal = $product->price * $qty;

                    $details[] = [
                        'type' => 'product',
                        'service_id' => null,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'unit_price' => $product->price,
                        'subtotal' => $subtotal,
                    ];

                    $total += $subtotal;

                    $product->decrement('stock', $qty);
                    InventoryMovement::create([
                        'product_id' => $productId,
                        'movement_type' => 'salida',
                        'quantity' => $qty,
                        'description' => 'Venta',
                    ]);
                }
            }

            $ticket = Ticket::create([
                'user_id' => auth()->id(),
                'washer_id' => $request->washer_id,
                'vehicle_type_id' => $request->vehicle_type_id,
                'total_amount' => $total,
                'paid_amount' => $request->paid_amount,
                'change' => $request->paid_amount - $total,
                'payment_method' => $request->payment_method,
            ]);

            foreach ($details as $detail) {
                $detail['ticket_id'] = $ticket->id;
                TicketDetail::create($detail);
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
        abort(403); // Edición de tickets deshabilitada por integridad
    }

    public function update(Request $request, Ticket $ticket)
    {
        abort(403); // Lo mismo
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket eliminado');
    }
}
