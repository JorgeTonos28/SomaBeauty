<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleType;

class VehicleTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            'Bicicleta', 'Motor', 'Pasola', 'Carro', 'Jeepeta',
            'Camioneta', 'Minibús', 'Guagua', 'Camión',
        ];

        foreach ($types as $type) {
            VehicleType::create(['name' => $type]);
        }
    }
}

