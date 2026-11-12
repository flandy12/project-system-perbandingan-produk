<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesImportBatch extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['rows' => 'array', 'summary' => 'array', 'applied_at' => 'datetime'];
}
