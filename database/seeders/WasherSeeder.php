<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Washer;

class WasherSeeder extends Seeder
{
    public function run()
    {
        $washers = [
            ['name' => 'Ana Martínez', 'active' => true],
            ['name' => 'Laura Gómez', 'active' => true],
            ['name' => 'María Rodríguez', 'active' => true],
            ['name' => 'Daniela Ramírez', 'active' => true],
        ];

        foreach ($washers as $washer) {
            $washer['pending_amount'] = 0;
            Washer::updateOrCreate(['name' => $washer['name']], $washer);
        }
    }
}

