<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{PettyCashExpense, User};

class PettyCashExpenseSeeder extends Seeder
{
    public function run(): void
    {
        if (PettyCashExpense::count() > 0) {
            return;
        }

        $user = User::first();
        $now = now();
        $expenses = [
            ['description' => 'Compra de detergente', 'amount' => 120, 'created_at' => $now->copy()->subDays(2)],
            ['description' => 'Reparación menor', 'amount' => 80, 'created_at' => $now->copy()->subDay()],
            ['description' => 'Compra de bolsas', 'amount' => 50, 'created_at' => $now],
        ];

        foreach ($expenses as $exp) {
            PettyCashExpense::create([
                'user_id' => $user->id,
                'description' => $exp['description'],
                'amount' => $exp['amount'],
                'created_at' => $exp['created_at'],
                'updated_at' => $exp['created_at'],
            ]);
        }
    }
}
