<?php
namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\DiscountLog;
use App\Models\Product;
use App\Models\Service;
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
        return view('discounts.create', compact('products', 'services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:product,service',
            'discountable_id' => 'required|integer',
            'amount_type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
            'end_at' => 'nullable|date',
        ]);

        $model = $request->type === 'product' ? Product::class : Service::class;

        $discount = Discount::updateOrCreate(
            ['discountable_type' => $model, 'discountable_id' => $request->discountable_id],
            [
                'amount_type' => $request->amount_type,
                'amount' => $request->amount,
                'end_at' => $request->end_at,
                'active' => true,
                'created_by' => auth()->id(),
            ]
        );

        DiscountLog::create([
            'discount_id' => $discount->id,
            'user_id' => auth()->id(),
            'action' => 'create',
            'amount_type' => $discount->amount_type,
            'amount' => $discount->amount,
            'end_at' => $discount->end_at,
        ]);

        return redirect()->route('discounts.index')->with('success', 'Descuento guardado');
    }

    public function edit(Discount $discount)
    {
        $products = Product::orderBy('name')->get();
        $services = Service::orderBy('name')->get();
        return view('discounts.edit', compact('discount', 'products', 'services'));
    }

    public function update(Request $request, Discount $discount)
    {
        $request->validate([
            'type' => 'required|in:product,service',
            'discountable_id' => 'required|integer',
            'amount_type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
            'end_at' => 'nullable|date',
        ]);

        $model = $request->type === 'product' ? Product::class : Service::class;

        $discount->update([
            'discountable_type' => $model,
            'discountable_id' => $request->discountable_id,
            'amount_type' => $request->amount_type,
            'amount' => $request->amount,
            'end_at' => $request->end_at,
        ]);

        DiscountLog::create([
            'discount_id' => $discount->id,
            'user_id' => auth()->id(),
            'action' => 'update',
            'amount_type' => $discount->amount_type,
            'amount' => $discount->amount,
            'end_at' => $discount->end_at,
        ]);

        return redirect()->route('discounts.index')->with('success', 'Descuento actualizado');
    }

    public function destroy(Discount $discount)
    {
        DiscountLog::create([
            'discount_id' => $discount->id,
            'user_id' => auth()->id(),
            'action' => 'delete',
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
            'action' => 'activate',
            'amount_type' => $discount->amount_type,
            'amount' => $discount->amount,
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
            'action' => 'deactivate',
            'amount_type' => $discount->amount_type,
            'amount' => $discount->amount,
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
