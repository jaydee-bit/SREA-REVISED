<?php

namespace Database\Seeders;

use App\Models\Barangay;
use Illuminate\Database\Seeder;

class BarangaySeeder extends Seeder
{
    public function run(): void
    {
        $barangays = [
            ['name' => 'Poblacion',        'lat' => 14.9985, 'lng' => 120.9532],
            ['name' => 'Banca-Banca',      'lat' => 15.0035, 'lng' => 120.9490],
            ['name' => 'Sampaloc',         'lat' => 15.0060, 'lng' => 120.8230],
            ['name' => 'Caingin',          'lat' => 14.9560, 'lng' => 120.9150],
            ['name' => 'Maronquillo',      'lat' => 15.0010, 'lng' => 120.9450],
            ['name' => 'Tambubong',        'lat' => 14.9400, 'lng' => 120.8290],
            ['name' => 'Lico',             'lat' => 14.9020, 'lng' => 120.9790],
            ['name' => 'Cruz na Daan',     'lat' => 15.0110, 'lng' => 120.9550],
            ['name' => 'Dagat-Dagatan',    'lat' => 15.0140, 'lng' => 120.9480],
            ['name' => 'Diliman I',        'lat' => 15.0180, 'lng' => 120.9620],
            ['name' => 'Diliman II',       'lat' => 15.0210, 'lng' => 120.9580],
            ['name' => 'Maasim',           'lat' => 14.9860, 'lng' => 120.9430],
            ['name' => 'Pantubig',         'lat' => 14.9240, 'lng' => 120.9620],
            ['name' => 'Pulong Bayabas',   'lat' => 14.9780, 'lng' => 120.9580],
            ['name' => 'San Roque',        'lat' => 15.0070, 'lng' => 120.9720],
            ['name' => 'Talacsan',         'lat' => 14.8970, 'lng' => 121.0940],
            ['name' => 'Tukod',            'lat' => 14.9920, 'lng' => 120.9750],
            ['name' => 'Sapang Pahalang',  'lat' => 15.0290, 'lng' => 120.9350],
            ['name' => 'Coral na Bato',    'lat' => 15.0860, 'lng' => 121.0810],
            ['name' => 'Capihan',          'lat' => 15.0910, 'lng' => 120.8480],
            ['name' => 'BMA-Balagtas',     'lat' => 14.9420, 'lng' => 121.0340],
            ['name' => 'Mabalas-Balas',    'lat' => 15.0550, 'lng' => 121.0690],
        ];

        foreach ($barangays as $b) {
            Barangay::updateOrCreate(
                ['name' => $b['name']],
                ['latitude' => $b['lat'], 'longitude' => $b['lng']]
            );
        }
    }
}