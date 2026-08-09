<?php

namespace App\Support;

class MockAnnouncements
{
    public static function all(): array
    {
        return [
            ['id' => 'ANN-001', 'title' => 'Flash Flood Warning', 'preview' => 'Flash flood warning along Banca-Banca river...', 'target' => 'All Barangays', 'posted_by' => 'Main Admin', 'date' => 'Mar 17, 2026'],
            ['id' => 'ANN-002', 'title' => 'Typhoon Advisory', 'preview' => 'PAGASA Typhoon signal #2 expected tonight...', 'target' => 'All Barangays', 'posted_by' => 'Main Admin', 'date' => 'Mar 17, 2026'],
            ['id' => 'ANN-003', 'title' => 'Community Meeting', 'preview' => 'All residents of Banca-Banca are invited...', 'target' => 'Banca-Banca', 'posted_by' => 'Brgy. Admin', 'date' => 'Mar 17, 2026'],
        ];
    }
}