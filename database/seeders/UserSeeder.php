<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() > 0) {
            return;
        }

        $users = [
            [
                'name' => 'Admin Uno',
                'email' => 'admin1@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Admin Dos',
                'email' => 'admin2@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Cajero',
                'email' => 'cajero@example.com',
                'password' => Hash::make('password'),
                'role' => 'cajero',
            ],
        ];

        foreach ($users as $data) {
            User::create($data);
        }
    }
}
