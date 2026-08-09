<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class TrafficAdvisorySeeder extends Seeder
{
    public function run(): void
    {
        // IMPORTANT: Replace `1` with an actual user ID that exists in your users table.
        // Check your users table first: `SELECT id FROM users LIMIT 5;`
        $userId = 1;

        DB::table('traffic_advisories')->insert([
            [
                'title' => 'Road Repair in Poblacion',
                'description' => 'The main road in Poblacion will undergo repair from July 30 to August 2. Expect heavy traffic. Use alternate routes.',
                'location' => 'Poblacion',
                'severity' => 'high',
                'is_active' => true,
                'created_by' => $userId,
                'created_at' => Carbon::parse('2026-07-29 10:00:00'),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Bridge Inspection',
                'description' => 'The San Rafael Bridge will be closed for inspection on August 1 from 9:00 AM to 12:00 PM. Plan your travel accordingly.',
                'location' => 'Banca-Banca',
                'severity' => 'medium',
                'is_active' => true,
                'created_by' => $userId,
                'created_at' => Carbon::parse('2026-07-28 16:20:00'),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Flooding Alert',
                'description' => 'Heavy rainfall may cause flooding in low-lying areas. Stay updated and avoid crossing flooded streets.',
                'location' => 'Multiple Barangays',
                'severity' => 'high',
                'is_active' => true,
                'created_by' => $userId,
                'created_at' => Carbon::parse('2026-07-27 08:30:00'),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Alternate Route Opening',
                'description' => 'The new bypass road in Pasong Bangkal is now open to light vehicles. This should ease traffic during peak hours.',
                'location' => 'Pasong Bangkal',
                'severity' => 'low',
                'is_active' => true,
                'created_by' => $userId,
                'created_at' => Carbon::parse('2026-07-26 14:00:00'),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
