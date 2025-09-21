<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Presidente Light', 'price' => 150.00, 'stock' => 42],
            ['name' => 'Presidente Dura Peq.', 'price' => 150.00, 'stock' => 56],
            ['name' => 'Presidente Dura Med.', 'price' => 250.00, 'stock' => 18],
            ['name' => 'Cerveza One Peq.', 'price' => 150.00, 'stock' => 21],
            ['name' => 'Cerveza Modelo', 'price' => 200.00, 'stock' => 18],
            ['name' => 'Cerveza Smirnof', 'price' => 200.00, 'stock' => 9],
            ['name' => 'Cerveza Heiniken', 'price' => 200.00, 'stock' => 24],
            ['name' => 'Cerveza Corona', 'price' => 200.00, 'stock' => 16],
            ['name' => 'Cerveza Desperado', 'price' => 200.00, 'stock' => 4],
            ['name' => 'Cerveza Stella', 'price' => 200.00, 'stock' => 10],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['name' => $product['name']], $product);
        }
    }
}
