<?php

namespace App\Support;

class MockIncidents
{
    public static function all(): array
    {
        return [
            [
                'id' => 'INC-001', 'type' => 'Flood', 'barangay' => 'Banca-Banca',
                'reporter' => 'Lito Batungbakal', 'contact' => '09301234567',
                'coordinates' => '15.0123, 121.0456', 'level' => 'critical', 'status' => 'responding',
                'time' => '10 min ago', 'stage' => 2,
                'assigned' => ['name' => 'Juan Dela Cruz', 'team' => 'Team Bravo - Ambulance AM-01', 'current_status' => 'On Scene', 'location' => 'Banca-Banca', 'metric_label' => 'Response Time', 'metric_value' => '8 min', 'last_update' => '2 min ago', 'badge' => 'Deployed'],
                'standby' => [],
            ],
            [
                'id' => 'INC-001b', 'type' => 'Flood', 'barangay' => 'Sampaloc',
                'reporter' => 'Nena C.', 'contact' => '09301234000',
                'coordinates' => '15.0145, 121.0477', 'level' => 'high', 'status' => 'responding',
                'time' => '15 min ago', 'stage' => 1,
                'assigned' => ['name' => 'Pedro Reyes', 'team' => 'Team Alpha - Rescue Van RV-01', 'current_status' => 'En Route', 'location' => 'Near Sampaloc', 'metric_label' => 'ETA', 'metric_value' => '~3 min', 'last_update' => 'Just now', 'badge' => 'Deployed'],
                'standby' => [],
            ],
            [
                'id' => 'INC-002', 'type' => 'Landslide', 'barangay' => 'Sampaloc',
                'reporter' => 'Rowell R.', 'contact' => '09301234111',
                'coordinates' => '15.0201, 121.0398', 'level' => 'high', 'status' => 'responding',
                'time' => '25 min ago', 'stage' => 0,
                'assigned' => ['name' => 'Juan Dela Cruz', 'team' => 'Team Bravo - Ambulance AM-01', 'current_status' => 'Deployed', 'location' => 'Sampaloc', 'metric_label' => 'Response Time', 'metric_value' => '-', 'last_update' => '5 min ago', 'badge' => 'Deployed'],
                'standby' => [],
            ],
            [
                'id' => 'INC-003', 'type' => 'Accident', 'barangay' => 'Maronquillo',
                'reporter' => 'Erwin P.', 'contact' => '09301234222',
                'coordinates' => '15.0089, 121.0512', 'level' => 'medium', 'status' => 'waiting',
                'time' => '40 min ago', 'stage' => null,
                'assigned' => null,
                'standby' => [
                    ['name' => 'Maria Santos', 'team' => 'Team Alpha - Fire Truck FT-03'],
                    ['name' => 'Joseph Chan', 'team' => 'Team Bravo - Ambulance AM-02'],
                ],
            ],
            [
                'id' => 'INC-004', 'type' => 'Flood', 'barangay' => 'Tambubong',
                'reporter' => 'Rommel C.', 'contact' => '09301234333',
                'coordinates' => '15.0067, 121.0455', 'level' => 'low', 'status' => 'waiting',
                'time' => '1 hr ago', 'stage' => null,
                'assigned' => null,
                'standby' => [
                    ['name' => 'Maria Santos', 'team' => 'Team Alpha - Fire Truck FT-03'],
                ],
            ],
        ];
    }
}