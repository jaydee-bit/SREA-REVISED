<?php

    namespace App\Support;
    class MockAnalytics
    {
        public static function stats(): array
        {
            return [
                'avg_response_time' => '8.4 min',
                'avg_response_improved' => '12%',
                'high_risk_barangays' => ['count' => 4, 'names' => 'Banca-Banca, Sampaloc +2'],
                'prediction_accuracy' => '79%',
            ];
        }

        // Each row tagged with a real date so the From/To filter has something to compare against.
        public static function incidentsPerBarangay(): array
        {
            return [
                ['barangay' => 'Banca-Banca', 'count' => 27, 'date' => '2026-02-10'],
                ['barangay' => 'Sampaloc',    'count' => 24, 'date' => '2026-02-15'],
                ['barangay' => 'Libis',       'count' => 20, 'date' => '2026-01-20'],
                ['barangay' => 'Tambubong',   'count' => 15, 'date' => '2026-01-05'],
                ['barangay' => 'Pulo',        'count' => 11, 'date' => '2026-03-01'],
            ];
        }

        public static function typeDistribution(): array
        {
            return [
                ['type' => 'Flood',     'percent' => 77, 'color' => '#2C6BE0'],
                ['type' => 'Fire',      'percent' => 45, 'color' => '#D63939'],
                ['type' => 'Landslide', 'percent' => 28, 'color' => '#C9A227'],
                ['type' => 'Typhoon',   'percent' => 18, 'color' => '#6C63FF'],
                ['type' => 'Accident',  'percent' => 12, 'color' => '#F76707'],
            ];
        }

        public static function monthlyTrend(): array
        {
            return [
                ['month' => 'Jan',  'actual' => 62, 'date' => '2026-01-01'],
                ['month' => 'Feb',  'actual' => 18, 'date' => '2026-02-01'],
                ['month' => 'Mar',  'actual' => 12, 'date' => '2026-03-01'],
                ['month' => 'Apr',  'actual' => 22, 'date' => '2026-04-01'],
                ['month' => 'May',  'actual' => 38, 'date' => '2026-05-01'],
                ['month' => 'June', 'actual' => 2,  'date' => '2026-06-01'],
                ['month' => 'July', 'actual' => null, 'date' => '2026-07-01'],
                ['month' => 'Aug',  'actual' => null, 'date' => '2026-08-01'],
            ];
        }
    }