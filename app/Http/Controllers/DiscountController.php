<?php
namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\DiscountLog;
use App\Models\Product;
use App\Models\Service;
use App\Models\Drink;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $discounts = Discount::with('discountable')->orderBy('created_at', 'desc')->get();
        return view('discounts.index', compact('discounts'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        $drinks = Drink::orderBy('name')->get();
        return view('discounts.create', compact('products', 'services', 'drinks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item' => 'required|string',
            'amount' => 'nullable|numeric|min:0',
            'amount_percentage' => 'nullable|numeric|min:0',
            'start_at' => 'nullable|date|before_or_equal:end_at',
            'end_at' => 'nullable|date|after_or_equal:start_at',
        ]);

        [$itemType, $id] = explode('-', $request->item);
        $model = match($itemType) {
            'service' => Service::class,
            'drink' => Drink::class,
            default => Product::class,
        };

        $amount = $request->amount ?? 0;
        $type = 'fixed';
        if(!$amount && $request->amount_percentage){
            $amount = $request->amount_percentage;
            $type = 'percentage';
        }elseif($request->amount_percentage){
            $type = 'fixed';
        }

        $discount = Discount::updateOrCreate(
            ['discountable_type' => $model, 'discountable_id' => $id],
            [
                'amount_type' => $type,
                'amount' => $amount,
                'start_at' => $request->start_at,
                'end_at' => $request->end_at,
                'active' => true,
                'created_by' => auth()->id(),
            ]
        );

        $action = $discount->wasRecentlyCreated ? 'crear' : 'actualizar';
        DiscountLog::create([
            'discount_id' => $discount->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'amount_type' => $discount->amount_type,
            'amount' => $discount->amount,
            'start_at' => $discount->start_at,
            'end_at' => $discount->end_at,
        ]);

        return redirect()->route('discounts.index')->with('success', 'Descuento guardado');
    }

    public function edit(Discount $discount)
    {
        $products = Product::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        $drinks = Drink::orderBy('name')->get();
        return view('discounts.edit', compact('discount', 'products', 'services', 'drinks'));
    }

    public function update(Request $request, Discount $discount)
    {
        $request->validate([
            'item' => 'required|string',
            'amount' => 'nullable|numeric|min:0',
            'amount_percentage' => 'nullable|numeric|min:0',
            'start_at' => 'nullable|date|before_or_equal:end_at',
            'end_at' => 'nullable|date|after_or_equal:start_at',
        ]);

        [$itemType, $id] = explode('-', $request->item);
        $model = match($itemType) {
            'service' => Service::class,
            'drink' => Drink::class,
            default => Product::class,
        };

        $amount = $request->amount ?? 0;
        $type = 'fixed';
        if(!$amount && $request->amount_percentage){
            $amount = $request->amount_percentage;
            $type = 'percentage';
        }elseif($request->amount_percentage){
            $type = 'fixed';
        }

        $discount->update([
            'discountable_type' => $model,
            'discountable_id' => $id,
            'amount_type' => $type,
            'amount' => $amount,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
        ]);

        DiscountLog::create([
            'discount_id' => $discount->id,
            'user_id' => auth()->id(),
            'action' => 'actualizar',
            'amount_type' => $discount->amount_type,
            'amount' => $discount->amount,
            'start_at' => $discount->start_at,
            'end_at' => $discount->end_at,
        ]);

        return redirect()->route('discounts.index')->with('success', 'Descuento actualizado');
    }

    public function destroy(Discount $discount)
    {
        DiscountLog::create([
            'discount_id' => $discount->id,
            'user_id' => auth()->id(),
            'action' => 'eliminar',
        ]);

        $discount->delete();

        return redirect()->route('discounts.index')->with('success', 'Descuento eliminado');
    }

    public function activate(Discount $discount)
    {
        $discount->update(['active' => true]);
        DiscountLog::create([
            'discount_id' => $discount->id,
            'user_id' => auth()->id(),
            'action' => 'activar',
            'amount_type' => $discount->amount_type,
            'amount' => $discount->amount,
            'start_at' => $discount->start_at,
            'end_at' => $discount->end_at,
        ]);
        return back()->with('success', 'Descuento activado');
    }

    public function deactivate(Discount $discount)
    {
        $discount->update(['active' => false]);
        DiscountLog::create([
            'discount_id' => $discount->id,
            'user_id' => auth()->id(),
            'action' => 'desactivar',
            'amount_type' => $discount->amount_type,
            'amount' => $discount->amount,
            'start_at' => $discount->start_at,
            'end_at' => $discount->end_at,
        ]);
        return back()->with('success', 'Descuento desactivado');
    }

    public function show(Discount $discount)
    {
        $discount->load('discountable', 'logs.user');
        return view('discounts.show', compact('discount'));
    }
}
