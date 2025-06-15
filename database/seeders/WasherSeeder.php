<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Washer;

class WasherSeeder extends Seeder
{
    public function run()
    {
        $washers = [
            ['name' => 'Carlos Martínez', 'active' => true],
            ['name' => 'Luis Gómez', 'active' => true],
            ['name' => 'Pedro Rodríguez', 'active' => true],
            ['name' => 'Jonathan Ramírez', 'active' => true],
        ];

        foreach ($washers as $washer) {
            $washer['pending_amount'] = 0;
            Washer::updateOrCreate(['name' => $washer['name']], $washer);
        }
    }
}

