<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\VehicleType;
use App\Models\ServicePrice;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Aplicamos middleware para proteger todo este controlador
     * Solo los usuarios autenticados con rol "admin" podrán usarlo.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $services = Service::orderBy('name')->get();
        return view('services.index', compact('services'));
    }

    public function create()
    {
        $vehicleTypes = VehicleType::all();
        return view('services.create', compact('vehicleTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string',
            'active' => 'sometimes|boolean',
            'prices' => 'required|array',
            'prices.*' => 'required|numeric|min:0'
        ]);

        $service = Service::create([
            'name' => $request->name,
            'description' => $request->description,
            'active' => $request->boolean('active')
        ]);

        foreach ($request->prices as $vehicleTypeId => $price) {
            ServicePrice::create([
                'service_id' => $service->id,
                'vehicle_type_id' => $vehicleTypeId,
                'price' => $price
            ]);
        }

        return redirect()->route('services.index')
            ->with('success', 'Servicio creado exitosamente.');
    }

    public function edit(Service $service)
    {
        $vehicleTypes = VehicleType::all();
        $prices = $service->prices->pluck('price', 'vehicle_type_id');
        return view('services.edit', compact('service', 'vehicleTypes', 'prices'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:services,name,' . $service->id,
            'description' => 'nullable|string',
            'active' => 'sometimes|boolean',
            'prices' => 'required|array',
            'prices.*' => 'required|numeric|min:0'
        ]);

        $service->update([
            'name' => $request->name,
            'description' => $request->description,
            'active' => $request->boolean('active')
        ]);

        foreach ($request->prices as $vehicleTypeId => $price) {
            ServicePrice::updateOrCreate(
                ['service_id' => $service->id, 'vehicle_type_id' => $vehicleTypeId],
                ['price' => $price]
            );
        }

        return redirect()->route('services.index')
            ->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Servicio eliminado correctamente.');
    }
}
