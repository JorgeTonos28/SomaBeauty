<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BankAccount;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (BankAccount::count() > 0) {
            return;
        }

        $accounts = [
            [
                'bank' => 'BHD',
                'account' => '00123456789',
                'type' => 'Corriente',
                'holder' => 'Car Wash S.R.L.',
                'holder_cedula' => '131-1234567-1',
            ],
            [
                'bank' => 'Banreservas',
                'account' => '00234567890',
                'type' => 'Ahorro',
                'holder' => 'Car Wash S.R.L.',
                'holder_cedula' => '131-1234567-1',
            ],
            [
                'bank' => 'Popular',
                'account' => '00345678901',
                'type' => 'Corriente',
                'holder' => 'Car Wash S.R.L.',
                'holder_cedula' => '131-1234567-1',
            ],
        ];

        foreach ($accounts as $account) {
            BankAccount::firstOrCreate(
                ['bank' => $account['bank'], 'account' => $account['account']],
                $account
            );
        }
    }
}
