<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{InventoryMovement, Product, User};

class InventoryMovementSeeder extends Seeder
{
    public function run(): void
    {
        if (InventoryMovement::count() > 0) {
            return;
        }

        $user = User::first();
        $p1 = Product::first();
        $p2 = Product::skip(1)->first();
        $now = now();

        $movements = [
            ['product_id' => $p1->id, 'movement_type' => 'entrada', 'quantity' => 20, 'concept' => 'Compra inicial', 'created_at' => $now->copy()->subDays(2)],
            ['product_id' => $p1->id, 'movement_type' => 'salida',  'quantity' => 5,  'concept' => 'Venta', 'created_at' => $now->copy()->subDay()],
            ['product_id' => $p2->id, 'movement_type' => 'entrada', 'quantity' => 10, 'concept' => 'Compra', 'created_at' => $now],
            ['product_id' => $p2->id, 'movement_type' => 'salida',  'quantity' => 3,  'concept' => 'Venta', 'created_at' => $now],
        ];

        foreach ($movements as $data) {
            $prod = Product::find($data['product_id']);
            InventoryMovement::create([
                'product_id' => $prod->id,
                'user_id' => $user->id,
                'movement_type' => $data['movement_type'],
                'quantity' => $data['quantity'],
                'concept' => $data['concept'],
                'created_at' => $data['created_at'],
                'updated_at' => $data['created_at'],
            ]);
            if ($data['movement_type'] === 'entrada') {
                $prod->increment('stock', $data['quantity']);
            } else {
                $prod->decrement('stock', $data['quantity']);
            }
        }
    }
}
