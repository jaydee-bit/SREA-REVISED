<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AlertSeeder extends Seeder
{
    public function run(): void
    {
        // Replace `1` with an actual user ID from your users table
        $userId = 1;

        DB::table('alerts')->insert([
            [
                'title' => 'Severe Flooding Alert',
                'description' => 'Severe flooding reported in low-lying areas. Evacuate immediately to designated evacuation centers. Avoid crossing flooded streets.',
                'level' => 'critical', // 'critical', 'high', 'medium', 'low'
                'barangay' => null, // null = all barangays (or set to specific e.g., 'Poblacion')
                'location_lat' => null,
                'location_lng' => null,
                'is_active' => true,
                'created_by' => $userId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
