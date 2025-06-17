<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\BankAccountSeeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            VehicleTypeSeeder::class,
            ServiceSeeder::class,
            ServicePriceSeeder::class,
            ProductSeeder::class,
            WasherSeeder::class,
            VehicleSeeder::class,
            ExtraVehicleSeeder::class,
            DrinkSeeder::class,
            BankAccountSeeder::class,
            UserSeeder::class,
            DiscountSeeder::class,
            InventoryMovementSeeder::class,
            PettyCashExpenseSeeder::class,
            TicketSeeder::class,
            WasherPaymentSeeder::class,
        ]);
    }
}
