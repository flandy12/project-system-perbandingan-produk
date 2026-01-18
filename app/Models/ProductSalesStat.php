<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSalesStat extends Model
{
    protected $fillable = [
        'product_id',
        'month',
        'year',
        'total_sold'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
