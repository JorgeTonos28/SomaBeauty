<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            ['name' => 'Brugal Añejo', 'type' => 'bebida', 'price' => 350],
            ['name' => 'Ron Barceló', 'type' => 'bebida', 'price' => 360],
            ['name' => 'Whisky Johnnie Walker', 'type' => 'bebida', 'price' => 1200],
            ['name' => 'Whisky Chivas Regal', 'type' => 'bebida', 'price' => 1350],
            ['name' => 'Papas Lay’s', 'type' => 'producto', 'price' => 50],
            ['name' => 'Doritos', 'type' => 'producto', 'price' => 60],
            ['name' => 'Agua Botella', 'type' => 'producto', 'price' => 25],
            ['name' => 'Cerveza Presidente', 'type' => 'bebida', 'price' => 100],
        ];

        foreach ($products as $item) {
            Product::updateOrCreate(
                ['name' => $item['name']],
                ['price' => $item['price'], 'type' => $item['type'], 'stock' => 100]
            );
        }
    }
}

