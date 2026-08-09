<?php

namespace App\Support;

class MockResponderReports
{
    public static function all(): array
    {
        return [
            [
                'id' => 'RPT-001',
                'incident_id' => 'INC-001',
                'type' => 'Flood',
                'barangay' => 'Banca-Banca',
                'description' => 'Water level reached knee-height along the main road. Assisted 3 households to evacuate to the barangay hall.',
                'photo_path' => null,
                'persons_involved' => 3,
                'responder_name' => 'Juan Dela Cruz',
                'responder_notes' => 'Deployed rubber boat for evacuation. No injuries reported.',
                'status' => 'resolved',
                'escalation_reason' => null,
                'escalated_by' => null,
                'escalated_at' => null,
                'resolution_notes' => 'All residents safely evacuated. Water receded after 2 hours.',
                'resolved_at' => 'July 12, 2026 — 3:45 PM',
            ],
            [
                'id' => 'RPT-002',
                'incident_id' => 'INC-002',
                'type' => 'Landslide',
                'barangay' => 'Sampaloc',
                'description' => 'Minor landslide blocked half of the barangay road after heavy rain. No structures affected.',
                'photo_path' => null,
                'persons_involved' => 0,
                'responder_name' => 'Pedro Reyes',
                'responder_notes' => 'Coordinated with barangay engineer for debris clearing. Road passable one lane.',
                'status' => 'escalated',
                'escalation_reason' => 'Slope instability suspected — requested geohazard assessment from MDRRMO engineering team.',
                'escalated_by' => 'Pedro Reyes',
                'escalated_at' => 'July 13, 2026 — 9:10 AM',
                'resolution_notes' => null,
                'resolved_at' => null,
            ],
            [
                'id' => 'RPT-003',
                'incident_id' => 'INC-007',
                'type' => 'Accident',
                'barangay' => 'Poblacion',
                'description' => 'Motorcycle collision at the intersection near the market. One passenger sustained minor injuries.',
                'photo_path' => null,
                'persons_involved' => 2,
                'responder_name' => 'Charles Lavin',
                'responder_notes' => 'Provided first aid on-site, transported patient to San Rafael District Hospital for observation.',
                'status' => 'resolved',
                'escalation_reason' => null,
                'escalated_by' => null,
                'escalated_at' => null,
                'resolution_notes' => 'Patient stable, discharged same day. Traffic cleared within 30 minutes.',
                'resolved_at' => 'July 11, 2026 — 5:20 PM',
            ],
        ];
    }
}