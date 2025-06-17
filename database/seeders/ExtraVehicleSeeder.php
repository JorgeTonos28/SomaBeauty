<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\VehicleType;

class ExtraVehicleSeeder extends Seeder
{
    public function run(): void
    {
        if (Vehicle::count() > 3) {
            return;
        }

        $vehicles = [
            [
                'customer_name' => 'Laura Torres',
                'type' => 'Carro',
                'plate' => 'C111AA',
                'brand' => 'Kia',
                'model' => 'Rio',
                'color' => 'Blanco',
                'year' => 2019,
            ],
            [
                'customer_name' => 'Miguel Santos',
                'type' => 'Jeepeta',
                'plate' => 'D222BB',
                'brand' => 'Ford',
                'model' => 'Explorer',
                'color' => 'Gris',
                'year' => 2021,
            ],
        ];

        foreach ($vehicles as $data) {
            $type = VehicleType::firstWhere('name', $data['type']);
            if (!$type) {
                continue;
            }
            Vehicle::updateOrCreate(
                ['plate' => $data['plate']],
                [
                    'customer_name' => $data['customer_name'],
                    'vehicle_type_id' => $type->id,
                    'brand' => $data['brand'],
                    'model' => $data['model'],
                    'color' => $data['color'],
                    'year' => $data['year'],
                ]
            );
        }
    }
}
