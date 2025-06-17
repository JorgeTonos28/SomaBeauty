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

    public function index(Request $request)
    {
        $query = InventoryMovement::with(['product', 'user']);

        if ($request->filled('start')) {
            $query->whereDate('created_at', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $query->whereDate('created_at', '<=', $request->end);
        }

        if ($request->filled('product')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->product . '%');
            });
        }

        $movements = $query->latest()->get();

        if ($request->ajax()) {
            return view('inventory.partials.table', [
                'movements' => $movements,
            ]);
        }

        return view('inventory.index', [
            'movements' => $movements,
            'filters' => $request->only(['start', 'end', 'product']),
        ]);
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        return view('inventory.create', compact('products'));
    }

    public function createExit()
    {
        $products = Product::orderBy('name')->get();
        return view('inventory.create_exit', compact('products'));
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
            'concept' => 'Entrada',
        ]);
        $product->increment('stock', $request->quantity);

        return redirect()->route('inventory.index')
            ->with('success', 'Entrada registrada correctamente.');
    }

    public function storeExit(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'concept' => 'required|string|max:255',
        ]);

        $product = Product::findOrFail($request->product_id);
        if ($product->stock < $request->quantity) {
            return back()->withErrors(['quantity' => 'Stock insuficiente'])->withInput();
        }

        InventoryMovement::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'movement_type' => 'salida',
            'quantity' => $request->quantity,
            'concept' => $request->concept,
        ]);
        $product->decrement('stock', $request->quantity);

        return redirect()->route('inventory.index')
            ->with('success', 'Salida registrada correctamente.');
    }
}
