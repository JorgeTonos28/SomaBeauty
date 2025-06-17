<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Discount, Product, Service, Drink, User};

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        if (Discount::count() > 0) {
            return;
        }

        $creator = User::first();
        $now = now();

        $discounts = [
            [ // fixed amount, single end date
                'discountable_type' => Product::class,
                'discountable_id' => Product::first()->id ?? 1,
                'amount_type' => 'fixed',
                'amount' => 20,
                'start_at' => null,
                'end_at' => $now->copy()->addDay(),
                'active' => true,
            ],
            [ // percentage, current range
                'discountable_type' => Service::class,
                'discountable_id' => Service::first()->id ?? 1,
                'amount_type' => 'percentage',
                'amount' => 10,
                'start_at' => $now->copy(),
                'end_at' => $now->copy()->addDays(2),
                'active' => true,
            ],
            [ // past range
                'discountable_type' => Drink::class,
                'discountable_id' => Drink::first()->id ?? 1,
                'amount_type' => 'fixed',
                'amount' => 30,
                'start_at' => $now->copy()->subDays(3),
                'end_at' => $now->copy()->subDay(),
                'active' => true,
            ],
            [ // future range
                'discountable_type' => Product::class,
                'discountable_id' => Product::skip(1)->first()->id ?? 1,
                'amount_type' => 'percentage',
                'amount' => 5,
                'start_at' => $now->copy()->addDay(),
                'end_at' => $now->copy()->addDays(3),
                'active' => true,
            ],
            [ // inactive discount
                'discountable_type' => Service::class,
                'discountable_id' => Service::skip(1)->first()->id ?? 1,
                'amount_type' => 'fixed',
                'amount' => 50,
                'start_at' => $now->copy()->subDays(3),
                'end_at' => $now->copy()->addDay(),
                'active' => false,
            ],
        ];

        foreach ($discounts as $data) {
            $data['created_by'] = $creator->id ?? 1;
            Discount::create($data);
        }
    }
}
