<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class User extends Authenticatable
{
    use CrudTrait, HasApiTokens, HasFactory, Notifiable, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'barangay',
        'is_super_admin',
        'is_verified',
        'phone',
        'gender',
        'birth_date',
        'street',
        'province',
        'municipality',
        'valid_id_type',
        'valid_id_photo',
        'profile_image',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'is_super_admin' => 'boolean',
            'birth_date' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['role', 'name'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Staff account {$eventName}");
    }

    // Helper methods
    public function isResponder(): bool
    {
        return $this->role === 'responder';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin' && $this->is_super_admin === true;
    }

    // Convenience: true only for a barangay-scoped admin, not for
    // responders or a super admin.
    public function isBarangayAdmin(): bool
    {
        return $this->role === 'admin' && $this->is_super_admin === false;
    }

    // Relationships
    public function responderProfile()
    {
        return $this->hasOne(ResponderProfile::class);
    }

    public function incidentsReported()
    {
        return $this->hasMany(Incident::class, 'user_id');
    }

    public function incidentsAssigned()
    {
        return $this->hasMany(Incident::class, 'assigned_to');
    }

    public function emergencyCalls()
    {
        return $this->hasMany(EmergencyCall::class);
    }
}