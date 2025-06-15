<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
        ]);
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'type' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0'
        ]);

        Product::create($request->only('name', 'type', 'price', 'stock'));

        return redirect()->route('products.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'type' => 'required|string',
            'price' => 'required|numeric|min:0'
        ]);

        $product->update($request->only('name', 'type', 'price'));

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
