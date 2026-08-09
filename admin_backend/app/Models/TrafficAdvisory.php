<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrafficAdvisory extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'location', 'severity', 'is_active', 'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}