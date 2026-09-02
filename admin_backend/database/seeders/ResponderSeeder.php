<?php

namespace Database\Seeders;

use App\Models\ResponderProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ResponderSeeder extends Seeder
{
    public function run(): void
    {
        $responders = [
            ['name' => 'Juan Dela Cruz', 'email' => 'juan.delacruz@srea.gov.ph', 'team' => 'Team Bravo', 'vehicle' => 'Ambulance AM-01', 'current_status' => 'Deployed'],
            ['name' => 'Maria Santos', 'email' => 'maria.santos@srea.gov.ph', 'team' => 'Team Alpha', 'vehicle' => 'Fire Truck FT-03', 'current_status' => 'Standby'],
            ['name' => 'Pedro Reyes', 'email' => 'pedro.reyes@srea.gov.ph', 'team' => 'Team Alpha', 'vehicle' => 'Rescue Van RV-01', 'current_status' => 'Deployed'],
            ['name' => 'Ana Garcia', 'email' => 'ana.garcia@srea.gov.ph', 'team' => 'Team Bravo', 'vehicle' => 'Rescue Van RV-02', 'current_status' => 'Standby'],
            ['name' => 'Jose Mendoza', 'email' => 'jose.mendoza@srea.gov.ph', 'team' => 'Team Charlie', 'vehicle' => 'Fire Truck FT-01', 'current_status' => 'Off Duty'],
            ['name' => 'Charles Lavin', 'email' => 'charles.lavin@srea.gov.ph', 'team' => 'Team Charlie', 'vehicle' => 'Fire Truck FT-01', 'current_status' => 'Deployed'],
            ['name' => 'Joseph Chan', 'email' => 'joseph.chan@srea.gov.ph', 'team' => 'Team Bravo', 'vehicle' => 'Ambulance AM-02', 'current_status' => 'Standby'],
        ];

        foreach ($responders as $r) {
            $user = User::updateOrCreate(
                ['email' => $r['email']],
                [
                    'name' => $r['name'],
                    'password' => Hash::make('Responder123!'),
                    'role' => 'responder',
                ]
            );

            ResponderProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'team' => $r['team'],
                    'vehicle' => $r['vehicle'],
                    'current_status' => $r['current_status'],
                ]
            );
        }
    }
}