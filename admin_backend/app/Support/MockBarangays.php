<?php

namespace App\Support;

class MockBarangays
{
    // Approximate positions for visual/demo purposes — not surveyed coordinates.
    public static function all(): array
    {
        return [
            ['name' => 'Poblacion',        'lat' => 14.9985, 'lng' => 120.9532],
            ['name' => 'Banca-Banca',      'lat' => 15.0035, 'lng' => 120.9490],
            ['name' => 'Sampaloc',         'lat' => 15.0060, 'lng' => 120.9605],
            ['name' => 'Caingin',          'lat' => 14.9930, 'lng' => 120.9610],
            ['name' => 'Maronquillo',      'lat' => 15.0010, 'lng' => 120.9450],
            ['name' => 'Tambubong',        'lat' => 14.9890, 'lng' => 120.9560],
            ['name' => 'Lico',             'lat' => 14.9950, 'lng' => 120.9680],
            ['name' => 'Cruz na Daan',     'lat' => 15.0110, 'lng' => 120.9550],
            ['name' => 'Dagat-Dagatan',    'lat' => 15.0140, 'lng' => 120.9480],
            ['name' => 'Diliman I',        'lat' => 15.0180, 'lng' => 120.9620],
            ['name' => 'Diliman II',       'lat' => 15.0210, 'lng' => 120.9580],
            ['name' => 'Maasim',           'lat' => 14.9860, 'lng' => 120.9430],
            ['name' => 'Pantubig',         'lat' => 14.9820, 'lng' => 120.9500],
            ['name' => 'Pulong Bayabas',   'lat' => 14.9780, 'lng' => 120.9580],
            ['name' => 'San Roque',        'lat' => 15.0070, 'lng' => 120.9720],
            ['name' => 'Talacsan',         'lat' => 15.0250, 'lng' => 120.9450],
            ['name' => 'Tukod',            'lat' => 14.9920, 'lng' => 120.9750],
            ['name' => 'Sapang Pahalang',  'lat' => 15.0290, 'lng' => 120.9350],
        ];
    }

    public static function withIncidentCounts(): array
    {
        $incidents = MockIncidents::all();
        $counts = collect($incidents)->countBy('barangay');

        return collect(self::all())->map(function ($brgy) use ($counts) {
            $brgy['incident_count'] = $counts[$brgy['name']] ?? 0;
            return $brgy;
        })->all();
    }
}