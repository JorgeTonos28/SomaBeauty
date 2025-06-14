<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            ['name' => 'Brugal Añejo', 'price' => 350],
            ['name' => 'Ron Barceló', 'price' => 360],
            ['name' => 'Whisky Johnnie Walker', 'price' => 1200],
            ['name' => 'Whisky Chivas Regal', 'price' => 1350],
            ['name' => 'Papas Lay’s', 'price' => 50],
            ['name' => 'Doritos', 'price' => 60],
            ['name' => 'Agua Botella', 'price' => 25],
            ['name' => 'Cerveza Presidente', 'price' => 100],
        ];

        foreach ($products as $item) {
            Product::create([
                'name' => $item['name'],
                'price' => $item['price'],
                'stock' => 100
            ]);
        }
    }
}

