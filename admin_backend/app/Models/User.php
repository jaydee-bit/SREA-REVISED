<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;   // <-- required for createToken()
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use CrudTrait;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;   // <-- added HasApiTokens

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'barangay',
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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'birth_date' => 'date',
        ];
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

    // Relationships
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
