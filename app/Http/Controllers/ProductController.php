<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,cajero'])->only('index');
        $this->middleware(['auth', 'role:admin'])->except('index');
    }

    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $products = $query->orderBy('name')->get();

        if ($request->ajax()) {
            return view('products.partials.table', [
                'products' => $products,
            ]);
        }

        return view('products.index', [
            'products' => $products,
            'filters' => $request->only('q'),
            'lowStockProducts' => Product::lowStockItems(),
        ]);
    }

    public function create()
    {
        return view('products.create', [
            'defaultMinimumStock' => AppSetting::defaultMinimumStock(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
        ]);

        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'low_stock_threshold' => $request->filled('low_stock_threshold')
                ? (int) $request->low_stock_threshold
                : null,
        ]);

        if ($product->stock > 0) {
            InventoryMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'movement_type' => 'entrada',
                'quantity' => $product->stock,
                'concept' => 'Registro inicial',
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product' => $product,
            'defaultMinimumStock' => AppSetting::defaultMinimumStock(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'price' => 'required|numeric|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
        ]);

        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'low_stock_threshold' => $request->filled('low_stock_threshold')
                ? (int) $request->low_stock_threshold
                : null,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado correctamente.');
    }
}
