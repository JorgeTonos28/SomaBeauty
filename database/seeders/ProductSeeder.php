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
            ['name' => 'Coca-Cola 20 Onz.', 'price' => 90.00, 'stock' => 15],
            ['name' => 'Gatorade Peq.', 'price' => 60.00, 'stock' => 6],
            ['name' => '7-Up 20 Onz.', 'price' => 90.00, 'stock' => 14],
            ['name' => 'Botella Agua Planeta Azul - 20 Onz.', 'price' => 30.00, 'stock' => 17],
            ['name' => 'Botella de Agua Tonica', 'price' => 90.00, 'stock' => 12],
            ['name' => 'Botella Agua Enrriquillo', 'price' => 90.00, 'stock' => 10],
            ['name' => 'Jugo Motts', 'price' => 150.00, 'stock' => 15],
            ['name' => 'Whisky Black Label 12 años', 'price' => 3200.00, 'stock' => 1],
            ['name' => 'Botella Agua Perrier', 'price' => 150.00, 'stock' => 4],
            ['name' => 'Vodka Stoli', 'price' => 1200.00, 'stock' => 1],
            ['name' => 'Whisky Old Parr', 'price' => 3800.00, 'stock' => 4],
            ['name' => 'Ron Brugal Doble Reserva', 'price' => 1200.00, 'stock' => 2],
            ['name' => 'Vino Frontera', 'price' => 1000.00, 'stock' => 1],
            ['name' => 'Vino Secret', 'price' => 1000.00, 'stock' => 1],
            ['name' => 'Cidra Rose', 'price' => 900.00, 'stock' => 1],
            ['name' => 'Ron Brugal Leyenda', 'price' => 2300.00, 'stock' => 1],
            ['name' => 'Vodka Absolute', 'price' => 1600.00, 'stock' => 1],
            ['name' => 'Fireball Mini Shot', 'price' => 150.00, 'stock' => 10],
            ['name' => 'Serpis Lata Aceitunas Verdes - 120g', 'price' => 150.00, 'stock' => 0],
            ['name' => 'Serpis Lata Aceituna Negras sin hueso - 200g', 'price' => 200.00, 'stock' => 2],
            ['name' => 'La Explanda Lata Aceitunas Queso Azul - 350g', 'price' => 350.00, 'stock' => 1],
            ['name' => 'Pinos Ambientadores - Little Trees', 'price' => 150.00, 'stock' => 5],
            ['name' => 'Ambientadores - KIA', 'price' => 100.00, 'stock' => 2],
            ['name' => 'Ambientador - Manzana Azul', 'price' => 250.00, 'stock' => 2],
            ['name' => 'Ambientador Kuksuan', 'price' => 200.00, 'stock' => 0],
            ['name' => 'Lanilla Micrfibra de colores', 'price' => 100.00, 'stock' => 5],
            ['name' => 'Mani al granel', 'price' => 100.00, 'stock' => 1],
            ['name' => 'Tridents', 'price' => 100.00, 'stock' => 6],
            ['name' => 'Vasos 10 Onz.', 'price' => 180.00, 'stock' => 0],
            ['name' => 'Servilleta 500 Ud', 'price' => 230.00, 'stock' => 1],
            ['name' => 'Whisky Double Black', 'price' => 5500.00, 'stock' => 2],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['name' => $product['name']], $product);
        }
    }
}
