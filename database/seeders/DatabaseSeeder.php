<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\BankAccountSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            VehicleTypeSeeder::class,
            ServiceSeeder::class,
            ServicePriceSeeder::class,
            ProductSeeder::class,
            WasherSeeder::class,
            VehicleSeeder::class,
            DrinkSeeder::class,
            DiscountSeeder::class,
            BankAccountSeeder::class,
        ]);
    }

}
