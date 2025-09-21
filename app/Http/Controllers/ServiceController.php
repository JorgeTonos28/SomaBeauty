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
        return view('services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string',
            'active' => 'sometimes|boolean',
            'price_options' => 'required|array|min:1',
            'price_options.*.label' => 'required|string|max:255',
            'price_options.*.price' => 'required|numeric|min:0'
        ]);

        $service = Service::create([
            'name' => $request->name,
            'description' => $request->description,
            'active' => $request->boolean('active')
        ]);

        foreach ($request->price_options as $option) {
            $vehicleType = VehicleType::create([
                'name' => $option['label'],
            ]);

            $service->prices()->create([
                'vehicle_type_id' => $vehicleType->id,
                'label' => $option['label'],
                'price' => $option['price'],
            ]);
        }

        return redirect()->route('services.index')
            ->with('success', 'Servicio creado exitosamente.');
    }

    public function edit(Service $service)
    {
        $prices = $service->prices()->orderBy('id')->with('vehicleType')->get();
        return view('services.edit', compact('service', 'prices'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:services,name,' . $service->id,
            'description' => 'nullable|string',
            'active' => 'sometimes|boolean',
            'price_options' => 'required|array|min:1',
            'price_options.*.id' => 'nullable|integer|exists:service_prices,id',
            'price_options.*.label' => 'required|string|max:255',
            'price_options.*.price' => 'required|numeric|min:0'
        ]);

        $service->update([
            'name' => $request->name,
            'description' => $request->description,
            'active' => $request->boolean('active')
        ]);

        $submittedOptions = collect($request->price_options);
        $submittedIds = $submittedOptions
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        $service->prices()->whereNotIn('id', $submittedIds)->get()->each(function (ServicePrice $price) {
            $vehicleType = $price->vehicleType;
            $price->delete();

            if ($vehicleType && $vehicleType->servicePrices()->count() === 0 && $vehicleType->vehicles()->count() === 0) {
                $vehicleType->delete();
            }
        });

        foreach ($submittedOptions as $option) {
            if (!empty($option['id'])) {
                /** @var ServicePrice|null $price */
                $price = $service->prices()->where('id', $option['id'])->with('vehicleType')->first();
                if ($price) {
                    $price->update([
                        'label' => $option['label'],
                        'price' => $option['price'],
                    ]);

                    if ($price->vehicleType) {
                        $price->vehicleType->update(['name' => $option['label']]);
                    }
                }
                continue;
            }

            $vehicleType = VehicleType::create(['name' => $option['label']]);

            $service->prices()->create([
                'vehicle_type_id' => $vehicleType->id,
                'label' => $option['label'],
                'price' => $option['price'],
            ]);
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
