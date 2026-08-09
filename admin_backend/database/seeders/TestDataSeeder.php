<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Incident;
use App\Models\Alert;
use App\Models\Announcement;
use App\Models\TrafficAdvisory;
use App\Models\EmergencyCall;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'barangay' => null,
            'is_verified' => true,
        ]);

        // 2. Responder
        $responder = User::create([
            'name' => 'Alpha Responder',
            'email' => 'responder@example.com',
            'password' => Hash::make('password'),
            'role' => 'responder',
            'barangay' => 'Poblacion',
            'is_verified' => true,
        ]);

       

    }
}