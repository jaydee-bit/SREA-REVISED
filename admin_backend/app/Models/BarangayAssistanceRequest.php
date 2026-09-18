<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangayAssistanceRequest extends Model
{
    protected $fillable = [
        'incident_id',
        'requesting_barangay',
        'target_barangay',
        'requested_by',
        'responded_by',
        'status',
        'decline_reason',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }
}