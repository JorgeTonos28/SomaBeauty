<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\VehicleType;
use App\Models\ServicePrice;

class ServicePriceSeeder extends Seeder
{
    public function run()
    {
        $services = Service::all();
        $vehicles = VehicleType::all();

        $basePrices = [
            1 => 100, // Bicicleta
            2 => 150, // Motor
            3 => 160, // Pasola
            4 => 250, // Carro
            5 => 300, // Jeepeta
            6 => 350, // Camioneta
            7 => 400, // Minibús
            8 => 450, // Guagua
            9 => 500, // Camión
        ];

        $serviceIncrement = [
            1 => 0,
            2 => 50,
            3 => 30,
            4 => 60,
            5 => 80,
        ];

        foreach ($services as $service) {
            foreach ($vehicles as $vehicle) {
                $price = ($basePrices[$vehicle->id] ?? 0) + ($serviceIncrement[$service->id] ?? 0);

                ServicePrice::updateOrCreate(
                    ['service_id' => $service->id, 'vehicle_type_id' => $vehicle->id],
                    ['label' => $vehicle->name, 'price' => $price]
                );
            }
        }
    }
}

