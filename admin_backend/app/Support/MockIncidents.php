<?php

namespace App\Support;

class MockIncidents
{
    public static function all(): array
    {
        return [
            [
                'id' => 'INC-001', 'uuid' => 'a1b2c3d4-0001', 'type' => 'Flood', 'barangay' => 'Banca-Banca',
                'user_id' => null, 'reporter_name' => 'Lito Batungbakal', 'contact' => '09301234567',
                'coordinates' => '15.0123, 121.0456', 'status' => 'Responding', 'time' => '10 min ago', 'stage' => 2,
                'photo_path' =>  asset('images/flood.jpg'),
                'video_path' => null,
                'escalated_by' => null, 'escalation_reason' => null, 'escalated_at' => null,
                'is_duplicate' => false, 'duplicate_of' => null,
                'flag_reason' => null, 'reporter_rejection_count' => 0,
                'assigned' => ['name' => 'Juan Dela Cruz', 'team' => 'Team Bravo - Ambulance AM-01', 'current_status' => 'On Scene', 'location' => 'Banca-Banca', 'metric_label' => 'Response Time', 'metric_value' => '8 min', 'last_update' => '2 min ago', 'badge' => 'Deployed'],
                'standby' => [],
                'procedure_log' => [
                    ['time' => '10:05 AM', 'step' => 'Incident reported'],
                    ['time' => '10:08 AM', 'step' => 'Dispatcher verified report'],
                    ['time' => '10:12 AM', 'step' => 'Responder dispatched'],
                    ['time' => '10:20 AM', 'step' => 'Responder arrived on scene'],
                ],
            ],
            [
                'id' => 'INC-001b', 'uuid' => 'a1b2c3d4-0002', 'type' => 'Flood', 'barangay' => 'Sampaloc',
                'user_id' => null, 'reporter_name' => null, 'contact' => null,
                'coordinates' => '15.0145, 121.0477', 'status' => 'Responding', 'time' => '15 min ago', 'stage' => 1,
                'photo_path' =>  asset('images/flood.jpg'),
                'video_path' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'escalated_by' => null, 'escalation_reason' => null, 'escalated_at' => null,
                'is_duplicate' => false, 'duplicate_of' => null,
                'flag_reason' => null, 'reporter_rejection_count' => 0,
                'assigned' => ['name' => 'Pedro Reyes', 'team' => 'Team Alpha - Rescue Van RV-01', 'current_status' => 'En Route', 'location' => 'Near Sampaloc', 'metric_label' => 'ETA', 'metric_value' => '~3 min', 'last_update' => 'Just now', 'badge' => 'Deployed'],
                'standby' => [],
                'procedure_log' => [
                    ['time' => '10:20 AM', 'step' => 'Incident reported'],
                    ['time' => '10:23 AM', 'step' => 'Responder dispatched'],
                ],
            ],
            [
                'id' => 'INC-002', 'uuid' => 'a1b2c3d4-0003', 'type' => 'Landslide', 'barangay' => 'Sampaloc',
                'user_id' => null, 'reporter_name' => null, 'contact' => null,
                'coordinates' => '15.0201, 121.0398', 'status' => 'Responding', 'time' => '25 min ago', 'stage' => 0,
                'photo_path' => asset('images/landslide.jpg'),
                'video_path' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'escalated_by' => null, 'escalation_reason' => null, 'escalated_at' => null,
                'is_duplicate' => false, 'duplicate_of' => null,
                'flag_reason' => null, 'reporter_rejection_count' => 0,
                'assigned' => ['name' => 'Juan Dela Cruz', 'team' => 'Team Bravo - Ambulance AM-01', 'current_status' => 'Deployed', 'location' => 'Sampaloc', 'metric_label' => 'Response Time', 'metric_value' => '-', 'last_update' => '5 min ago', 'badge' => 'Deployed'],
                'standby' => [],
                'procedure_log' => [
                    ['time' => '10:00 AM', 'step' => 'Incident reported'],
                    ['time' => '10:05 AM', 'step' => 'Responder dispatched'],
                ],
            ],
            [
                'id' => 'INC-003', 'uuid' => 'a1b2c3d4-0004', 'type' => 'Accident', 'barangay' => 'Maronquillo',
                'user_id' => null, 'reporter_name' => null, 'contact' => null,
                'coordinates' => '15.0089, 121.0512', 'status' => 'Pending', 'time' => '40 min ago', 'stage' => null,
                'photo_path' => asset('images/car.jpg'), 'video_path' => null,
                'escalated_by' => null, 'escalation_reason' => null, 'escalated_at' => null,
                'is_duplicate' => false, 'duplicate_of' => null,
                'flag_reason' => null, 'reporter_rejection_count' => 0,
                'assigned' => null,
                'standby' => [
                    ['name' => 'Maria Santos', 'team' => 'Team Alpha - Fire Truck FT-03'],
                    ['name' => 'Joseph Chan', 'team' => 'Team Bravo - Ambulance AM-02'],
                ],
                'procedure_log' => [
                    ['time' => '9:45 AM', 'step' => 'Incident reported'],
                ],
            ],
            [
                'id' => 'INC-004', 'uuid' => 'a1b2c3d4-0005', 'type' => 'Flood', 'barangay' => 'Tambubong',
                'user_id' => null, 'reporter_name' => null, 'contact' => null,
                'coordinates' => '15.0067, 121.0455', 'status' => 'Pending', 'time' => '1 hr ago', 'stage' => null,
                'photo_path' =>  asset('images/flood.jpg'), 'video_path' => null,
                'escalated_by' => null, 'escalation_reason' => null, 'escalated_at' => null,
                'is_duplicate' => false, 'duplicate_of' => null,
                'flag_reason' => null, 'reporter_rejection_count' => 0,
                'assigned' => null,
                'standby' => [
                    ['name' => 'Maria Santos', 'team' => 'Team Alpha - Fire Truck FT-03'],
                ],
                'procedure_log' => [
                    ['time' => '9:20 AM', 'step' => 'Incident reported'],
                ],
            ],
            [
                'id' => 'INC-005', 'uuid' => 'a1b2c3d4-0006', 'type' => 'Flood', 'barangay' => 'Caingin',
                'user_id' => null, 'reporter_name' => null, 'contact' => null,
                'coordinates' => '14.9930, 120.9610', 'status' => 'Pending', 'time' => '2 hrs ago', 'stage' => null,
                'photo_path' =>  asset('images/flood.jpg'), 'video_path' => null,
                'escalated_by' => null, 'escalation_reason' => null, 'escalated_at' => null,
                'is_duplicate' => false, 'duplicate_of' => null,
                'flag_reason' => null, 'reporter_rejection_count' => 0,
                'assigned' => null, 'standby' => [],
                'procedure_log' => [['time' => '8:20 AM', 'step' => 'Incident reported']],
            ],
            [
                'id' => 'INC-006', 'uuid' => 'a1b2c3d4-0007', 'type' => 'Fire', 'barangay' => 'Poblacion',
                'user_id' => null, 'reporter_name' => null, 'contact' => null,
                'coordinates' => '14.9985, 120.9532', 'status' => 'Escalated', 'time' => '3 hrs ago', 'stage' => null,
                'photo_path' =>  asset('images/fire.jpg'), 'video_path' => null,
                'escalated_by' => 'Pedro Reyes',
                'escalation_reason' => 'Fire spread beyond initial containment area, requesting additional units.',
                'escalated_at' => '7:40 AM',
                'is_duplicate' => false, 'duplicate_of' => null,
                'flag_reason' => null, 'reporter_rejection_count' => 0,
                'assigned' => null, 'standby' => [],
                'procedure_log' => [
                    ['time' => '7:10 AM', 'step' => 'Incident reported'],
                    ['time' => '7:15 AM', 'step' => 'Responder dispatched'],
                    ['time' => '7:40 AM', 'step' => 'Escalated by responder'],
                ],
            ],
            [
                'id' => 'INC-007', 'uuid' => 'a1b2c3d4-0008', 'type' => 'Accident', 'barangay' => 'Poblacion',
                'user_id' => null, 'reporter_name' => 'Jerome J.', 'contact' => null,
                'coordinates' => '14.9985, 120.9532', 'status' => 'Responding', 'time' => '30 min ago', 'stage' => 1,
                'photo_path' =>  asset('images/car.jpg'), 'video_path' => null,
                'escalated_by' => null, 'escalation_reason' => null, 'escalated_at' => null,
                'is_duplicate' => false, 'duplicate_of' => null,
                'flag_reason' => null, 'reporter_rejection_count' => 0,
                'assigned' => ['name' => 'Charles Lavin', 'team' => 'Team Charlie - Fire Truck FT-01', 'current_status' => 'En Route', 'location' => 'Poblacion', 'metric_label' => 'ETA', 'metric_value' => '~5 min', 'last_update' => '1 min ago', 'badge' => 'Deployed'],
                'standby' => [],
                'procedure_log' => [['time' => '9:30 AM', 'step' => 'Incident reported'], ['time' => '9:33 AM', 'step' => 'Responder dispatched']],
            ],
            [
                'id' => 'INC-008', 'uuid' => 'a1b2c3d4-0009', 'type' => 'Flood', 'barangay' => 'Lico',
                'user_id' => null, 'reporter_name' => 'Julius B.', 'contact' => null,
                'coordinates' => '14.9020, 120.9790', 'status' => 'Rejected', 'time' => '3 hr ago', 'stage' => null,
                'photo_path' =>  asset('images/flood.jpg'), 'video_path' => null,
                'escalated_by' => null, 'escalation_reason' => null, 'escalated_at' => null,
                'is_duplicate' => false, 'duplicate_of' => null,
                'flag_reason' => 'No description provided', 'reporter_rejection_count' => 4,
                'assigned' => null, 'standby' => [],
                'procedure_log' => [['time' => '7:00 AM', 'step' => 'Incident reported'], ['time' => '7:20 AM', 'step' => 'Rejected by admin — insufficient detail']],
            ],

            // --- Duplicate reports (grouped under INC-002) ---
            [
                'id' => 'INC-002-DUP-1', 'uuid' => 'a1b2c3d4-0010', 'type' => 'Landslide', 'barangay' => 'Sampaloc',
                'user_id' => null, 'reporter_name' => null, 'contact' => null,
                'coordinates' => '15.0201, 121.0398', 'status' => 'Responding', 'time' => '22 min ago', 'stage' => null,
                'photo_path' => asset('images/landslide.jpg'), 'video_path' => null,
                'escalated_by' => null, 'escalation_reason' => null, 'escalated_at' => null,
                'is_duplicate' => true, 'duplicate_of' => 'INC-002',
                'flag_reason' => null, 'reporter_rejection_count' => 0,
                'assigned' => null, 'standby' => [], 'procedure_log' => [],
            ],
            [
                'id' => 'INC-002-DUP-2', 'uuid' => 'a1b2c3d4-0011', 'type' => 'Landslide', 'barangay' => 'Sampaloc',
                'user_id' => null, 'reporter_name' => null, 'contact' => null,
                'coordinates' => '15.0201, 121.0398', 'status' => 'Responding', 'time' => '19 min ago', 'stage' => null,
                'photo_path' =>  asset('images/landslide.jpg'), 'video_path' => null,
                'escalated_by' => null, 'escalation_reason' => null, 'escalated_at' => null,
                'is_duplicate' => true, 'duplicate_of' => 'INC-002',
                'flag_reason' => null, 'reporter_rejection_count' => 0,
                'assigned' => null, 'standby' => [], 'procedure_log' => [],
            ],
        ];
    }
}