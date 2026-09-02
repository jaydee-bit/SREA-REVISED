<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponderProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team',
        'vehicle',
        'current_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}