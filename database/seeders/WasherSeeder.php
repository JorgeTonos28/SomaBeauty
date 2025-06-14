<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Washer;

class WasherSeeder extends Seeder
{
    public function run()
    {
        $washers = [
            ['name' => 'Carlos Martínez'],
            ['name' => 'Luis Gómez'],
            ['name' => 'Pedro Rodríguez'],
            ['name' => 'Jonathan Ramírez'],
        ];

        foreach ($washers as $washer) {
            $washer['pending_amount'] = 0;
            Washer::updateOrCreate(['name' => $washer['name']], $washer);
        }
    }
}

