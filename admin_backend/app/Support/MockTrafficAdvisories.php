<?php

namespace App\Support;

class MockTrafficAdvisories
{
    public static function all(): array
    {
        return [
            ['id' => 'TRF-001', 'title' => 'Road Closure — Flooding', 'road' => 'Banca-Banca Bridge Road', 'content' => 'Road Closure — Flooding', 'target' => 'All Barangays', 'status' => 'active', 'date' => 'Mar 17, 2026', 'users_reached' => 1240, 'time_ago' => '1 hr ago'],
            ['id' => 'TRF-002', 'title' => 'Road Works', 'road' => 'Maronquillo Main Road', 'content' => 'Road Works', 'target' => 'Maronquillo', 'status' => 'active', 'date' => 'Mar 17, 2026', 'users_reached' => 540, 'time_ago' => '3 hrs ago'],
            ['id' => 'TRF-003', 'title' => 'Accident Clearance', 'road' => 'Tigbe-Sampaloc Road', 'content' => 'Accident cleared, road now open', 'target' => 'Tigbe', 'status' => 'cleared', 'date' => 'Mar 17, 2026', 'users_reached' => 310, 'time_ago' => '5 hrs ago'],
        ];
    }
}