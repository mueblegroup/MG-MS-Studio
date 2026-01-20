<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'praveenthamo99@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Praveen_99'),
                'role' => 'admin',
            ]
        );
    }
}
