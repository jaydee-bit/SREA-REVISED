<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Seeds real MDRRMO monthly accomplishment report data (Jan-Apr 2026)
 * into the incidents table, for use with the Analytics dashboard.
 *
 * Only genuine incident entries are included — Vehicular Accident,
 * Medical Transfer, Medical Emergency, Medical Assistance, Maternal
 * Emergency, and Search & Retrieval Operation. Non-incident entries in
 * the original report (trainings, meetings, standby duty, motorcades,
 * courses, OPLAN Semana Santa) were excluded during parsing since they
 * are MDRRMO activities, not emergency incidents.
 *
 * Data honesty notes:
 * - TYPE, DATE, and COUNT come directly from the real MDRRMO report.
 * - BARANGAY is matched to the official barangay list where the report
 *   named one directly (e.g. "Brgy. Maasim"). Where the report only
 *   named a hospital/facility (e.g. "Castro Hospital", "PNP Station"),
 *   barangay falls back to Poblacion (town center) and the real place
 *   name is preserved in `location_details` — these are NOT confirmed
 *   barangays for that incident.
 * - TIME OF DAY and RESOLUTION DURATION are NOT in the original report
 *   (it only records the date). These were generated so response-time
 *   analytics have something to render. Don't present these specific
 *   numbers as real MDRRMO response times without saying so.
 *
 * Uses DB::table()->insert() directly (not Incident::create()) to skip
 * Eloquent model events — this seeder does NOT require Firebase or
 * Reverb to be configured.
 */
class MdrrmoIncidentSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/mdrrmo_incidents.json');

        if (! file_exists($path)) {
            $this->command->error("Missing data file: {$path}");
            $this->command->error('Place mdrrmo_incidents.json in database/seeders/data/ first.');
            return;
        }

        $records = json_decode(file_get_contents($path), true);
        $now = Carbon::now();
        $rows = [];

        foreach ($records as $r) {
            $rows[] = [
                'uuid' => (string) Str::uuid(),
                'user_id' => null,
                'reporter_name' => null,
                'type' => $r['type'],
                'description' => $r['description'],
                'photo_path' => null,
                'barangay' => $r['barangay'],
                'location_details' => $r['location_details'],
                'latitude' => $r['latitude'],
                'longitude' => $r['longitude'],
                'address' => $r['address'],
                'status' => $r['status'],
                'reported_at' => $r['reported_at'],
                'assigned_to' => null,
                'responder_notes' => null,
                'escalation_reason' => null,
                'escalated_by' => null,
                'escalated_at' => null,
                'resolution_notes' => null,
                'resolved_at' => $r['resolved_at'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('incidents')->insert($chunk);
        }

        $this->command->info(count($rows) . ' MDRRMO incidents seeded.');
    }
}
