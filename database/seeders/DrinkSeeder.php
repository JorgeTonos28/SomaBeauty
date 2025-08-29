<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DrinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drinks = [
            [
                'name' => 'Margarita de limón',
                'ingredients' => 'Tequila, sal y limón.',
                'price' => 300,
                'active' => true,
            ],
            [
                'name' => 'Mojito de limón',
                'ingredients' => '*',
                'price' => 350,
                'active' => true,
            ],
            [
                'name' => 'Sangría',
                'ingredients' => 'Vino tinto, jugo de naranja, jugo de limón y soda ...',
                'price' => 350,
                'active' => true,
            ],
            [
                'name' => 'Mojito de Coco',
                'ingredients' => '*',
                'price' => 350,
                'active' => true,
            ],
            [
                'name' => 'Margarita de fresa',
                'ingredients' => '*',
                'price' => 350,
                'active' => true,
            ],
            [
                'name' => 'Caipiriña',
                'ingredients' => '*',
                'price' => 350,
                'active' => true,
            ],
            [
                'name' => 'Piña Colada',
                'ingredients' => '*',
                'price' => 350,
                'active' => true,
            ],
        ];

        foreach ($drinks as $drink) {
            \App\Models\Drink::updateOrCreate(['name' => $drink['name']], $drink);
        }
    }
}
