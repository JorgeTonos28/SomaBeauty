<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Washer, WasherPayment};

class WasherPaymentSeeder extends Seeder
{
    public function run(): void
    {
        if (WasherPayment::count() > 0) {
            return;
        }

        $washer1 = Washer::first();
        $washer2 = Washer::skip(1)->first();
        $now = now();

        // Pay washer1 for one wash two days ago (today payment)
        WasherPayment::create([
            'washer_id' => $washer1->id,
            'payment_date' => $now->toDateString(),
            'total_washes' => 1,
            'amount_paid' => 100,
            'observations' => 'Pago de ejemplo',
        ]);
        $washer1->decrement('pending_amount', 100);

        // Pay washer2 for wash today
        WasherPayment::create([
            'washer_id' => $washer2->id,
            'payment_date' => $now->toDateString(),
            'total_washes' => 1,
            'amount_paid' => 100,
            'observations' => 'Pago de ejemplo',
        ]);
        $washer2->decrement('pending_amount', 100);
    }
}
