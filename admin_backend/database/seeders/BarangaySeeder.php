<?php

namespace Database\Seeders;

use App\Models\Barangay;
use Illuminate\Database\Seeder;

class BarangaySeeder extends Seeder
{
    public function run(): void
    {
        $barangays = [
            ['name' => 'Poblacion',        'lat' => 14.9572615, 'lng' => 120.9649346],
            ['name' => 'Banca-Banca',      'lat' => 15.0218123, 'lng' => 120.9198815],
            ['name' => 'Sampaloc',         'lat' => 14.9825590, 'lng' => 120.9262660],
            ['name' => 'Caingin',          'lat' => 14.9817752, 'lng' => 120.9453711],
            ['name' => 'Maronquillo',      'lat' => 14.9782720, 'lng' => 121.0000660],
            ['name' => 'Tambubong',        'lat' => 14.9700357, 'lng' => 120.9259458],
            ['name' => 'Lico',             'lat' => 14.9605687, 'lng' => 120.9548158],
            ['name' => 'Cruz na Daan',     'lat' => 15.0278433, 'lng' => 120.9343931],
            ['name' => 'Dagat-Dagatan',    'lat' => 15.0329904, 'lng' => 120.9185707],
            ['name' => 'Diliman I',        'lat' => 15.0222238, 'lng' => 120.9529865],
            ['name' => 'Diliman II',       'lat' => 15.0337495, 'lng' => 120.9528047],
            ['name' => 'Maasim',           'lat' => 15.0355726, 'lng' => 120.9375348],
            ['name' => 'Pantubig',         'lat' => 14.9677516, 'lng' => 120.9525813],
            ['name' => 'Pulong Bayabas',   'lat' => 15.0168479, 'lng' => 120.9069307],
            ['name' => 'San Roque',        'lat' => 15.0096012, 'lng' => 120.9325489],
            ['name' => 'Talacsan',         'lat' => 14.9696552, 'lng' => 120.9825196],
            ['name' => 'Tukod',            'lat' => 14.9950213, 'lng' => 121.0521065],
            ['name' => 'Sapang Pahalang',  'lat' => 14.9949183, 'lng' => 121.0364831],
            ['name' => 'Coral na Bato',    'lat' => 14.9896328, 'lng' => 120.9711649],
            ['name' => 'Capihan',          'lat' => 14.9971174, 'lng' => 120.9323225],
            ['name' => 'BMA-Balagtas',     'lat' => 14.9685705, 'lng' => 120.9675186],
            ['name' => 'Mabalas-Balas',    'lat' => 15.0270170, 'lng' => 120.9421673],
            ['name' => 'Libis',            'lat' => 14.9575190, 'lng' => 120.9706490],
            ['name' => 'Maguinao',         'lat' => 15.0195730, 'lng' => 120.9372580],
            ['name' => 'Paco',             'lat' => 14.9954921, 'lng' => 120.9060485],
            ['name' => 'Pansumaloc',       'lat' => 15.0195824, 'lng' => 120.8978532],
            ['name' => 'Pasong Bangkal',   'lat' => 15.0022427, 'lng' => 121.0111036],
            ['name' => 'Pasong Callos',    'lat' => 14.9995144, 'lng' => 120.9963554],
            ['name' => 'Pasong Intsik',    'lat' => 15.0073485, 'lng' => 120.9664529],
            ['name' => 'Pinacpinacan',     'lat' => 14.9975479, 'lng' => 120.9153343],
            ['name' => 'Pulo',             'lat' => 14.9777033, 'lng' => 121.0206043],
            ['name' => 'Salapungan',       'lat' => 15.0193681, 'lng' => 120.9627416],
            ['name' => 'San Agustin',      'lat' => 15.0288620, 'lng' => 120.9270923],
            ['name' => 'Ulingao',          'lat' => 14.9801064, 'lng' => 120.9123032],
        ];

        foreach ($barangays as $b) {
            Barangay::updateOrCreate(
                ['name' => $b['name']],
                ['latitude' => $b['lat'], 'longitude' => $b['lng']]
            );
        }
    }
}