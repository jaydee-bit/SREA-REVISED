<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // ✅ RESTORED – critical for UUID generation

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'reporter_name',
        'type',
        'description',
        'photo_path',
        'video_path',        // ✅ Added
        'barangay',
        'location_details',
        'latitude',
        'longitude',
        'address',
        'status',
        'reported_at',
        'assigned_to',
        'responder_notes',
        'escalation_reason',
        'escalated_by',
        'escalated_at',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'escalated_at' => 'datetime',
        'resolved_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function escalatedBy()
    {
        return $this->belongsTo(User::class, 'escalated_by');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid(); // ✅ Uses the imported Str
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
