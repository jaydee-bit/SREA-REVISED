<?php

namespace App\Support;

class MockAuditLog
{
    public static function all(): array
    {
        return [
            ['time' => 'Jul 20, 2026 — 10:05 AM', 'actor' => 'Maria Santos', 'role' => 'Responder', 'action' => 'Reported', 'target' => 'INC-001'],
            ['time' => 'Jul 20, 2026 — 10:08 AM', 'actor' => 'SREA Admin', 'role' => 'Admin', 'action' => 'Verified', 'target' => 'INC-001'],
            ['time' => 'Jul 20, 2026 — 10:12 AM', 'actor' => 'Juan Dela Cruz', 'role' => 'Responder', 'action' => 'Dispatched', 'target' => 'INC-001'],
            ['time' => 'Jul 20, 2026 — 10:20 AM', 'actor' => 'Juan Dela Cruz', 'role' => 'Responder', 'action' => 'Arrived On Scene', 'target' => 'INC-001'],
            ['time' => 'Jul 20, 2026 — 10:20 AM', 'actor' => 'Pedro Reyes', 'role' => 'Responder', 'action' => 'Dispatched', 'target' => 'INC-001b'],
            ['time' => 'Jul 20, 2026 — 10:00 AM', 'actor' => 'Juan Dela Cruz', 'role' => 'Responder', 'action' => 'Dispatched', 'target' => 'INC-002'],
            ['time' => 'Jul 20, 2026 — 9:45 AM', 'actor' => 'Anonymous', 'role' => 'Resident', 'action' => 'Reported', 'target' => 'INC-003'],
            ['time' => 'Jul 20, 2026 — 9:20 AM', 'actor' => 'Anonymous', 'role' => 'Resident', 'action' => 'Reported', 'target' => 'INC-004'],
            ['time' => 'Jul 20, 2026 — 8:20 AM', 'actor' => 'Anonymous', 'role' => 'Resident', 'action' => 'Reported', 'target' => 'INC-005'],
            ['time' => 'Jul 20, 2026 — 7:10 AM', 'actor' => 'Anonymous', 'role' => 'Resident', 'action' => 'Reported', 'target' => 'INC-006'],
            ['time' => 'Jul 20, 2026 — 7:15 AM', 'actor' => 'Pedro Reyes', 'role' => 'Responder', 'action' => 'Dispatched', 'target' => 'INC-006'],
            ['time' => 'Jul 20, 2026 — 7:40 AM', 'actor' => 'Pedro Reyes', 'role' => 'Responder', 'action' => 'Escalated', 'target' => 'INC-006'],
            ['time' => 'Jul 20, 2026 — 9:30 AM', 'actor' => 'Anonymous', 'role' => 'Resident', 'action' => 'Reported', 'target' => 'INC-007'],
            ['time' => 'Jul 20, 2026 — 9:33 AM', 'actor' => 'Charles Lavin', 'role' => 'Responder', 'action' => 'Dispatched', 'target' => 'INC-007'],
            ['time' => 'Jul 20, 2026 — 7:00 AM', 'actor' => 'Anonymous', 'role' => 'Resident', 'action' => 'Reported', 'target' => 'INC-008'],
            ['time' => 'Jul 20, 2026 — 7:10 AM', 'actor' => 'Ana Garcia', 'role' => 'Responder', 'action' => 'Dispatched', 'target' => 'INC-008'],
            ['time' => 'Jul 20, 2026 — 7:45 AM', 'actor' => 'Ana Garcia', 'role' => 'Responder', 'action' => 'Resolved', 'target' => 'INC-008'],
        ];
    }
}