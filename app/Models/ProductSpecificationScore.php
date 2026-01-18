<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSpecificationScore extends Model
{
    protected $fillable = [
        'product_id',
        'specification_id',
        'raw_value',
        'normalized_score'
    ];

    protected $casts = [
        'raw_value' => 'decimal:2',
        'normalized_score' => 'decimal:2'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function specification()
    {
        return $this->belongsTo(Specification::class);
    }
}
