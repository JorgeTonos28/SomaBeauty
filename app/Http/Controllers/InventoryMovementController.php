<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryMovementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,cajero']);
    }

    public function index()
    {
        $movements = InventoryMovement::with(['product','user'])->latest()->paginate(20);
        return view('inventory.index', compact('movements'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        return view('inventory.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        InventoryMovement::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'movement_type' => 'entrada',
            'quantity' => $request->quantity,
        ]);
        $product->increment('stock', $request->quantity);

        return redirect()->route('inventory.index')
            ->with('success', 'Entrada registrada correctamente.');
    }
}
