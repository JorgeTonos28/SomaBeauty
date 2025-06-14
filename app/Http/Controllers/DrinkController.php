<?php

namespace App\Http\Controllers;

use App\Models\Drink;
use Illuminate\Http\Request;

class DrinkController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,cajero'])->only('index');
        $this->middleware(['auth', 'role:admin'])->except('index');
    }

    public function index(Request $request)
    {
        $query = Drink::query();

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $drinks = $query->orderBy('name')->get();

        if ($request->ajax()) {
            return view('drinks.partials.table', [
                'drinks' => $drinks,
            ]);
        }

        return view('drinks.index', [
            'drinks' => $drinks,
            'filters' => $request->only('q'),
        ]);
    }

    public function create()
    {
        return view('drinks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:drinks,name',
            'price' => 'required|numeric|min:0',
            'ingredients' => 'nullable|string',
        ], [
            'name.required' => 'El nombre del trago es obligatorio.',
            'name.unique' => 'Ya existe un trago con ese nombre.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número válido.',
            'price.min' => 'El precio no puede ser negativo.',
        ]);

        Drink::create($request->only('name', 'price', 'ingredients'));

        return redirect()->route('drinks.index')
            ->with('success', 'Trago creado exitosamente.');
    }

    public function edit(Drink $drink)
    {
        return view('drinks.edit', compact('drink'));
    }

    public function update(Request $request, Drink $drink)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:drinks,name,' . $drink->id,
            'price' => 'required|numeric|min:0',
            'ingredients' => 'nullable|string',
        ], [
            'name.required' => 'El nombre del trago es obligatorio.',
            'name.unique' => 'Ya existe un trago con ese nombre.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número válido.',
            'price.min' => 'El precio no puede ser negativo.',
        ]);

        $drink->update($request->only('name', 'price', 'ingredients'));

        return redirect()->route('drinks.index')
            ->with('success', 'Trago actualizado correctamente.');
    }

    public function destroy(Drink $drink)
    {
        $drink->delete();

        return redirect()->route('drinks.index')
            ->with('success', 'Trago eliminado correctamente.');
    }
}
