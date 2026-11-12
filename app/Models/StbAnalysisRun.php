<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StbAnalysisRun extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_simulated' => 'boolean', 'snapshot' => 'array', 'result' => 'array', 'published_at' => 'datetime'];
}
