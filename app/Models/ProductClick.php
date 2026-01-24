<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductClick extends Model
{
protected $fillable = [
    'product_id',
    'user_id',
    'ip_address',
    'clicked_at'
];

    protected $casts = [
        'clicked_at' => 'datetime'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
