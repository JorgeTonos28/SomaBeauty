<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Washer;

class WasherSeeder extends Seeder
{
    public function run()
    {
        $washers = [
            ['name' => 'Carlos Martínez', 'phone' => '8091234567'],
            ['name' => 'Luis Gómez', 'phone' => '8092345678'],
            ['name' => 'Pedro Rodríguez', 'phone' => '8093456789'],
            ['name' => 'Jonathan Ramírez', 'phone' => '8094567890'],
        ];

        foreach ($washers as $washer) {
            Washer::create($washer);
        }
    }
}

