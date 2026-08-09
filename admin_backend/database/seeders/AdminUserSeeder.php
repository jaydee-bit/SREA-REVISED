<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@srea.gov.ph'],
            [
                'name' => 'SREA Admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_verified' => true,
            ]
        );
    }
}