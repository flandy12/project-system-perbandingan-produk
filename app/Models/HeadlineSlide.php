<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeadlineSlide extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'link',
        'image',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'position'  => 'integer',
    ];

    /**
     * Scope untuk slider aktif (frontend)
     */
    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->orderBy('position');
    }
}
