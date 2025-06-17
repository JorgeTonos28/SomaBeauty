<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Ticket, TicketDetail, Service, Product, Drink, ServicePrice, Vehicle, Washer, User, BankAccount};

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        if (Ticket::count() > 0) {
            return;
        }

        $user = User::first();
        $washer1 = Washer::first();
        $washer2 = Washer::skip(1)->first();
        $vehicle1 = Vehicle::first();
        $vehicle2 = Vehicle::skip(1)->first();
        $service = Service::first();
        $service2 = Service::skip(1)->first();
        $product = Product::first();
        $drink = Drink::first();
        $bank = BankAccount::first();
        $now = now();

        $price1 = ServicePrice::where('service_id', $service->id)->where('vehicle_type_id', $vehicle1->vehicle_type_id)->value('price');
        $price2 = ServicePrice::where('service_id', $service2->id)->where('vehicle_type_id', $vehicle2->vehicle_type_id)->value('price');

        // Ticket 1: two days ago, washer1 paid cash
        $t1 = Ticket::create([
            'user_id' => $user->id,
            'washer_id' => $washer1->id,
            'vehicle_type_id' => $vehicle1->vehicle_type_id,
            'vehicle_id' => $vehicle1->id,
            'customer_name' => $vehicle1->customer_name,
            'total_amount' => $price1,
            'paid_amount' => $price1,
            'change' => 0,
            'discount_total' => 0,
            'payment_method' => 'efectivo',
            'bank_account_id' => null,
            'washer_pending_amount' => 0,
            'pending' => false,
            'paid_at' => $now->copy()->subDays(2),
            'created_at' => $now->copy()->subDays(2),
            'updated_at' => $now->copy()->subDays(2),
        ]);
        TicketDetail::create([
            'ticket_id' => $t1->id,
            'type' => 'service',
            'service_id' => $service->id,
            'quantity' => 1,
            'unit_price' => $price1,
            'discount_amount' => 0,
            'subtotal' => $price1,
        ]);
        $washer1->increment('pending_amount', 100);

        // Ticket 2: yesterday, washer1 transfer
        $t2 = Ticket::create([
            'user_id' => $user->id,
            'washer_id' => $washer1->id,
            'vehicle_type_id' => $vehicle2->vehicle_type_id,
            'vehicle_id' => $vehicle2->id,
            'customer_name' => $vehicle2->customer_name,
            'total_amount' => $price2,
            'paid_amount' => $price2,
            'change' => 0,
            'discount_total' => 0,
            'payment_method' => 'transferencia',
            'bank_account_id' => $bank->id,
            'washer_pending_amount' => 0,
            'pending' => false,
            'paid_at' => $now->copy()->subDay(),
            'created_at' => $now->copy()->subDay(),
            'updated_at' => $now->copy()->subDay(),
        ]);
        TicketDetail::create([
            'ticket_id' => $t2->id,
            'type' => 'service',
            'service_id' => $service2->id,
            'quantity' => 1,
            'unit_price' => $price2,
            'discount_amount' => 0,
            'subtotal' => $price2,
        ]);
        $washer1->increment('pending_amount', 100);

        // Ticket 3: today washer2 with discount 10% on service1
        $discounted = $price1 - ($price1 * 0.10);
        $t3 = Ticket::create([
            'user_id' => $user->id,
            'washer_id' => $washer2->id,
            'vehicle_type_id' => $vehicle1->vehicle_type_id,
            'vehicle_id' => $vehicle1->id,
            'customer_name' => $vehicle1->customer_name,
            'total_amount' => $discounted,
            'paid_amount' => $discounted,
            'change' => 0,
            'discount_total' => $price1 - $discounted,
            'payment_method' => 'efectivo',
            'bank_account_id' => null,
            'washer_pending_amount' => 0,
            'pending' => false,
            'paid_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        TicketDetail::create([
            'ticket_id' => $t3->id,
            'type' => 'service',
            'service_id' => $service->id,
            'quantity' => 1,
            'unit_price' => $discounted,
            'discount_amount' => $price1 - $discounted,
            'subtotal' => $discounted,
        ]);
        $washer2->increment('pending_amount', 100);

        // Ticket 4: today no washer assigned, paid cash
        $t4 = Ticket::create([
            'user_id' => $user->id,
            'washer_id' => null,
            'vehicle_type_id' => $vehicle1->vehicle_type_id,
            'vehicle_id' => $vehicle1->id,
            'customer_name' => 'Cliente sin Lavador',
            'total_amount' => $price1,
            'paid_amount' => $price1,
            'change' => 0,
            'discount_total' => 0,
            'payment_method' => 'efectivo',
            'bank_account_id' => null,
            'washer_pending_amount' => 100,
            'pending' => false,
            'paid_at' => $now,
        ]);
        TicketDetail::create([
            'ticket_id' => $t4->id,
            'type' => 'service',
            'service_id' => $service->id,
            'quantity' => 1,
            'unit_price' => $price1,
            'discount_amount' => 0,
            'subtotal' => $price1,
        ]);

        // Ticket 5: yesterday only products
        $subtotal = $product->price;
        $t5 = Ticket::create([
            'user_id' => $user->id,
            'washer_id' => null,
            'vehicle_type_id' => null,
            'vehicle_id' => null,
            'customer_name' => 'Comprador',
            'total_amount' => $subtotal,
            'paid_amount' => $subtotal,
            'change' => 0,
            'discount_total' => 0,
            'payment_method' => 'efectivo',
            'bank_account_id' => null,
            'washer_pending_amount' => 0,
            'pending' => false,
            'paid_at' => $now->copy()->subDay(),
            'created_at' => $now->copy()->subDay(),
            'updated_at' => $now->copy()->subDay(),
        ]);
        TicketDetail::create([
            'ticket_id' => $t5->id,
            'type' => 'product',
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->price,
            'discount_amount' => 0,
            'subtotal' => $product->price,
        ]);

        // Ticket 6: today pending payment with washer1
        $t6 = Ticket::create([
            'user_id' => $user->id,
            'washer_id' => $washer1->id,
            'vehicle_type_id' => $vehicle1->vehicle_type_id,
            'vehicle_id' => $vehicle1->id,
            'customer_name' => $vehicle1->customer_name,
            'total_amount' => $price1,
            'paid_amount' => 0,
            'change' => 0,
            'discount_total' => 0,
            'payment_method' => null,
            'bank_account_id' => null,
            'washer_pending_amount' => 0,
            'pending' => true,
            'paid_at' => null,
        ]);
        TicketDetail::create([
            'ticket_id' => $t6->id,
            'type' => 'service',
            'service_id' => $service->id,
            'quantity' => 1,
            'unit_price' => $price1,
            'discount_amount' => 0,
            'subtotal' => $price1,
        ]);
        $washer1->increment('pending_amount', 100);
    }
}
