<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\VehicleType;

class VehicleSeeder extends Seeder
{
    public function run()
    {
        $vehicles = [
            [
                'customer_name' => 'Juan Pérez',
                'type' => 'Carro',
                'plate' => 'A123BC',
                'brand' => 'Toyota',
                'model' => 'Corolla',
                'color' => 'Rojo',
                'year' => 2012,
            ],
            [
                'customer_name' => 'María Gómez',
                'type' => 'Jeepeta',
                'plate' => 'B456CD',
                'brand' => 'Honda',
                'model' => 'CRV',
                'color' => 'Negro',
                'year' => 2018,
            ],
            [
                'customer_name' => 'Pedro Ramírez',
                'type' => 'Motor',
                'plate' => 'M789EF',
                'brand' => 'Bajaj',
                'model' => 'Pulsar',
                'color' => 'Azul',
                'year' => 2020,
            ],
        ];

        foreach ($vehicles as $data) {
            $type = VehicleType::firstWhere('name', $data['type']);
            if (!$type) {
                continue;
            }
            Vehicle::updateOrCreate([
                'plate' => $data['plate']
            ], [
                'customer_name' => $data['customer_name'],
                'vehicle_type_id' => $type->id,
                'brand' => $data['brand'],
                'model' => $data['model'],
                'color' => $data['color'],
                'year' => $data['year'],
            ]);
        }
    }
}
