<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductFinalScore extends Model
{
    protected $fillable = [
        'product_id',
        'specification_score',
        'click_score',
        'sales_score',
        'final_score',
        'calculated_at'
    ];

    protected $casts = [
        'specification_score' => 'decimal:2',
        'click_score' => 'decimal:2',
        'sales_score' => 'decimal:2',
        'final_score' => 'decimal:2',
        'calculated_at' => 'datetime'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
