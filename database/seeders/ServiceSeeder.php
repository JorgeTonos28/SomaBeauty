<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $services = [
            'Lavado Básico',
            'Lavado Premium',
            'Aspirado Interior',
            'Lavado de Motor',
            'Pulido de Carrocería',
        ];

        foreach ($services as $name) {
            Service::create([
                'name' => $name,
                'description' => $name . ' para todo tipo de vehículo.'
            ]);
        }
    }
}

