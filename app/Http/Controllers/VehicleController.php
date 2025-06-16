<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,cajero']);
    }

    public function search(Request $request)
    {
        $query = $request->input('plate');
        $vehicles = [];
        if ($query) {
            $vehicles = Vehicle::with('vehicleType')
                ->where('plate', 'like', $query.'%')
                ->limit(5)
                ->get()
                ->map(function($v){
                    return [
                        'id' => $v->id,
                        'plate' => $v->plate,
                        'brand' => $v->brand,
                        'model' => $v->model,
                        'color' => $v->color,
                        'year' => $v->year,
                        'vehicle_type_id' => $v->vehicle_type_id,
                        'type' => optional($v->vehicleType)->name,
                    ];
                });
        }
        return response()->json($vehicles);
    }
}
