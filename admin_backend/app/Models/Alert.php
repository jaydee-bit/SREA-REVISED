<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'level', 'barangay',
        'location_lat', 'location_lng', 'is_active', 'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'location_lat' => 'decimal:7',
        'location_lng' => 'decimal:7',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}