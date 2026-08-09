<?php

namespace App\Support;

class MockResponders
{
    public static function all(): array
    {
        $responders = [
            ['id' => 'R-001', 'name' => 'Juan Dela Cruz', 'team' => 'Team Alpha', 'vehicle' => 'Rescue Van RV-01', 'phone' => '09171234567', 'status' => 'deployed', 'location' => 'Banca-Banca'],
            ['id' => 'R-002', 'name' => 'Maria Santos',   'team' => 'Team Alpha', 'vehicle' => 'Fire Truck FT-03', 'phone' => '09181234567', 'status' => 'standby',  'location' => 'Mun. Hall'],
            ['id' => 'R-003', 'name' => 'Pedro Reyes',    'team' => 'Team Bravo', 'vehicle' => 'Ambulance AM-01',  'phone' => '09191234567', 'status' => 'deployed', 'location' => 'Sampaloc'],
            ['id' => 'R-004', 'name' => 'Ana Garcia',     'team' => 'Team Bravo', 'vehicle' => 'Rescue Van RV-02', 'phone' => '09201234567', 'status' => 'standby',  'location' => 'Mun. Hall'],
            ['id' => 'R-005', 'name' => 'Jose Mendoza',   'team' => 'Team Charlie', 'vehicle' => 'Fire Truck FT-01', 'phone' => '09211234567', 'status' => 'off_duty', 'location' => null],
        ];

        $incidents = MockIncidents::all();

        return array_map(function ($r) use ($incidents) {
            $match = collect($incidents)->first(fn ($inc) => $inc['assigned'] && $inc['assigned']['name'] === $r['name']);
            $r['assigned_incident'] = $match['id'] ?? null;
            return $r;
        }, $responders);
    }
}