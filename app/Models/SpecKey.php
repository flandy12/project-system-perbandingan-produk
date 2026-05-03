<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecKey extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'unit',
        'is_higher_better'
    ];

    public function category()
    {
        return $this->belongsTo(SpecCategory::class);
    }
}
