<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'name' => 'Margarita',
                'ingredients' => 'Tequila, sal y limón.',
                'price' => 300,
            ],
            [
                'name' => 'Mojito',
                'ingredients' => 'Hoja de menta, limón, ron blanco, azúcar y 7up.',
                'price' => 300,
            ],
            [
                'name' => 'Sangría',
                'ingredients' => 'Vino tinto, jugo de naranja, jugo de limón y soda (agua con gas).',
                'price' => 350,
            ],
            [
                'name' => 'Gin Tonic',
                'ingredients' => 'Ginebra y agua tónica, decorada con aceitunas.',
                'price' => 350,
            ],
        ];

        foreach ($drinks as $drink) {
            \App\Models\Drink::create($drink);
        }
    }
}
