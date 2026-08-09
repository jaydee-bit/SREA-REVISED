<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        // Replace `1` with an actual user ID from your users table.
        // Run `php artisan tinker` and `App\Models\User::pluck('id');` to find one.
        $userId = 1;

        DB::table('announcements')->insert([
            [
                'title' => 'Municipal Hall Closure',
                'body' => 'The Municipal Hall will be closed on July 31, 2026 for a staff training. Regular operations will resume on August 1.',
                'type' => 'general',
                'barangay' => null, // applies to all barangays
                'is_published' => true,
                'published_at' => Carbon::now(),
                'created_by' => $userId,
                'created_at' => Carbon::parse('2026-07-28 14:30:00'),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Free Medical Mission',
                'body' => 'A free medical mission will be held at the Barangay Hall on August 5, 2026 from 8:00 AM to 4:00 PM. Bring your valid IDs.',
                'type' => 'general',
                'barangay' => 'Poblacion',
                'is_published' => true,
                'published_at' => Carbon::now(),
                'created_by' => $userId,
                'created_at' => Carbon::parse('2026-07-25 09:15:00'),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Community Clean-Up Drive',
                'body' => 'Join the community clean-up drive on August 10, 2026. Meet at the plaza at 6:00 AM. Bags and gloves will be provided.',
                'type' => 'general',
                'barangay' => null,
                'is_published' => true,
                'published_at' => Carbon::now(),
                'created_by' => $userId,
                'created_at' => Carbon::parse('2026-07-20 18:45:00'),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Emergency Alert System Test',
                'body' => 'A test of the SREA emergency alert system will be conducted on August 2, 2026 at 10:00 AM. No action required.',
                'type' => 'weather', // or 'evacuation' – adjust as needed
                'barangay' => null,
                'is_published' => true,
                'published_at' => Carbon::now(),
                'created_by' => $userId,
                'created_at' => Carbon::parse('2026-07-29 08:00:00'),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
