<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreWeight extends Model
{
    protected $fillable = [
        'key',
        'weight',
    ];

    protected $casts = [
        'weight' => 'decimal:3',
    ];
}
