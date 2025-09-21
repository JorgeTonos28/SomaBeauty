<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleType;

class VehicleTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            'Pelo Corto',
            'Pelo Largo',
            'Regular',
            'Puntas',
            'Elaborado',
            'Simple',
            'Manos - Pintura Normal',
            'Pies - Pintura Normal',
            'Gel Mano y Pie',
            'Manicure Ruso',
            'Pedicure Ruso',
            'Retiro de gel',
            'Acrílico',
            'Acrílico Full',
        ];

        foreach ($types as $type) {
            VehicleType::firstOrCreate(['name' => $type]);
        }
    }
}

