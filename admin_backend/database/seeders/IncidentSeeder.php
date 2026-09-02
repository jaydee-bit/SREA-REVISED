<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Seeder;

class IncidentSeeder extends Seeder
{
    public function run(): void
    {
        $juan = User::where('email', 'juan.delacruz@srea.gov.ph')->first();
        $maria = User::where('email', 'maria.santos@srea.gov.ph')->first();
        $pedro = User::where('email', 'pedro.reyes@srea.gov.ph')->first();
        $ana = User::where('email', 'ana.garcia@srea.gov.ph')->first();
        $charles = User::where('email', 'charles.lavin@srea.gov.ph')->first();

        $incidents = [
            [
                'type' => 'Flood', 'barangay' => 'Banca-Banca', 'address' => 'Near the main bridge, Banca-Banca',
                'latitude' => 15.0123, 'longitude' => 121.0456,
                'description' => 'Emergency report with selfie and video',
                'photo_path' => 'https://picsum.photos/seed/inc001/300/300',
                'video_path' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'status' => 'Responding', 'assigned_to' => $juan?->id,
                'responder_notes' => 'Water level rising near the bridge, deployed rubber boat, evacuation in progress.',
                'reported_at' => now()->subMinutes(10),
            ],
            [
                'type' => 'Emergency', 'barangay' => 'Sampaloc', 'address' => 'Sampaloc main road',
                'latitude' => 15.0145, 'longitude' => 121.0477,
                'description' => 'Emergency report with selfie and video',
                'photo_path' => 'https://picsum.photos/seed/inc001b/300/300',
                'video_path' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'status' => 'Responding', 'assigned_to' => $pedro?->id,
                'reported_at' => now()->subMinutes(15),
            ],
            [
                'type' => 'Landslide', 'barangay' => 'Sampaloc', 'address' => 'Sampaloc hillside road',
                'latitude' => 15.0201, 'longitude' => 121.0398,
                'description' => 'Emergency report with selfie and video',
                'photo_path' => 'https://picsum.photos/seed/inc002/300/300',
                'video_path' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'status' => 'Responding', 'assigned_to' => $juan?->id,
                'responder_notes' => 'Debris partially blocking one lane, coordinating with barangay engineer.',
                'reported_at' => now()->subMinutes(25),
            ],
            [
                'type' => 'Emergency', 'barangay' => 'Maronquillo', 'address' => 'Maronquillo town center',
                'latitude' => 15.0089, 'longitude' => 121.0512,
                'description' => 'Emergency report with selfie and video',
                'photo_path' => 'https://picsum.photos/seed/inc003/300/300',
                'status' => 'Pending',
                'reported_at' => now()->subMinutes(40),
            ],
            [
                'type' => 'Emergency', 'barangay' => 'Tambubong', 'address' => 'Tambubong barangay hall vicinity',
                'latitude' => 15.0067, 'longitude' => 121.0455,
                'description' => 'Emergency report with selfie and video',
                'photo_path' => 'https://picsum.photos/seed/inc004/300/300',
                'status' => 'Pending',
                'reported_at' => now()->subHour(),
            ],
            [
                'type' => 'Emergency', 'barangay' => 'Caingin', 'address' => 'Caingin main road',
                'latitude' => 14.9930, 'longitude' => 120.9610,
                'description' => 'Emergency report with selfie and video',
                'photo_path' => 'https://picsum.photos/seed/inc005/300/300',
                'status' => 'Pending',
                'reported_at' => now()->subHours(2),
            ],
            [
                'type' => 'Emergency', 'barangay' => 'Poblacion', 'address' => 'Poblacion market area',
                'latitude' => 14.9985, 'longitude' => 120.9532,
                'description' => 'Emergency report with selfie and video',
                'photo_path' => 'https://picsum.photos/seed/inc006/300/300',
                'video_path' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'status' => 'Escalated',
                'escalation_reason' => 'Situation more severe than initially reported, requesting additional units.',
                'escalated_by' => $pedro?->id, 'escalated_at' => now()->subHours(3)->addMinutes(30),
                'reported_at' => now()->subHours(3),
            ],
            [
                'type' => 'Emergency', 'barangay' => 'Poblacion', 'address' => 'Poblacion intersection',
                'latitude' => 14.9985, 'longitude' => 120.9532,
                'description' => 'Emergency report with selfie and video',
                'photo_path' => 'https://picsum.photos/seed/inc007/300/300',
                'reporter_name' => 'Jerome J.',
                'status' => 'Responding', 'assigned_to' => $charles?->id,
                'reported_at' => now()->subMinutes(30),
            ],
            [
                'type' => 'Flood', 'barangay' => 'Lico', 'address' => 'Lico riverside',
                'latitude' => 14.9020, 'longitude' => 120.9790,
                'description' => 'Ankle-deep flooding along the main road near the barangay hall.',
                'photo_path' => 'https://picsum.photos/seed/inc008/300/300',
                'video_path' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'reporter_name' => 'Julius B.',
                'status' => 'Resolved', 'assigned_to' => $ana?->id,
                'resolution_notes' => 'All residents safely evacuated. Water receded after 2 hours.',
                'resolved_at' => now()->subHours(3)->addMinutes(45),
                'reported_at' => now()->subHours(3),
            ],
            // Duplicates for INC-002 (Sampaloc landslide) — same type/location/time window
            [
                'type' => 'Landslide', 'barangay' => 'Sampaloc', 'address' => 'Sampaloc hillside road',
                'latitude' => 15.0201, 'longitude' => 121.0398,
                'description' => 'Emergency report with selfie and video',
                'status' => 'Responding',
                'reported_at' => now()->subMinutes(22),
            ],
            [
                'type' => 'Landslide', 'barangay' => 'Sampaloc', 'address' => 'Sampaloc hillside road',
                'latitude' => 15.0201, 'longitude' => 121.0398,
                'description' => 'Emergency report with selfie and video',
                'status' => 'Responding',
                'reported_at' => now()->subMinutes(19),
            ],
        ];

        foreach ($incidents as $inc) {
            Incident::create($inc);
        }
    }
}