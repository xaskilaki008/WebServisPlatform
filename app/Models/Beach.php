<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beach extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'latitude',
        'longitude',
        'wave_level',
    ];

    protected $casts = [
        'number' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'wave_level' => 'integer',
    ];
}