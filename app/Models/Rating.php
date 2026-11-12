<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    public function scopeReal(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where(fn ($q) => $q->whereNull('review')->orWhere('review', 'not like', '[SIMULASI PENGUJIAN]%'))
            ->whereHas('user', fn ($q) => $q->where('is_active', true));
    }

    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'review',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
