<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPriceObservation extends Model
{
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected $guarded = ['id'];

    protected $casts = ['observed_on' => 'date', 'price' => 'decimal:2'];
}
