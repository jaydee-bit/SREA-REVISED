<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Incident extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'uuid',
        'user_id',
        'reporter_name',
        'type',
        'description',
        'photo_path',
        'video_path',
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'assigned_to', 'escalated_by', 'type'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Incident {$eventName}");
    }

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
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    // --- Status scopes ---
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeResponding($query)
    {
        return $query->where('status', 'Responding');
    }

    public function scopeEscalated($query)
    {
        return $query->where('status', 'Escalated');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'Rejected');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'Resolved');
    }

    // --- Real-time duplicate detection (Haversine formula, ~100m radius, 30-minute window) ---
    public function findNearbyReports()
    {
        return static::query()
            ->where('id', '!=', $this->id)
            ->where('type', $this->type)
            ->whereBetween('reported_at', [
                $this->reported_at->copy()->subMinutes(30),
                $this->reported_at->copy()->addMinutes(30),
            ])
            ->selectRaw('
                *,
                (6371000 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )) AS distance_meters
            ', [$this->latitude, $this->longitude, $this->latitude])
            ->havingRaw('distance_meters <= 100')
            ->get();
    }
}