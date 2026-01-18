<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecificationScore extends Model
{
    protected $fillable = [
        'specification_id',
        'is_used'
    ];

    protected $casts = [
        'is_used' => 'boolean'
    ];

    public function specification()
    {
        return $this->belongsTo(Specification::class);
    }
}
