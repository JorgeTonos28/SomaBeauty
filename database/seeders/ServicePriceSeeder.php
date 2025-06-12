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

        foreach ($services as $service) {
            foreach ($vehicles as $vehicle) {
                ServicePrice::create([
                    'service_id' => $service->id,
                    'vehicle_type_id' => $vehicle->id,
                    'price' => rand(350, 600)
                ]);
            }
        }
    }
}

