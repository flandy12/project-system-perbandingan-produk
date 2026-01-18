<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'price',
        'production_year',
        'stock',
        'category_id',
        'status'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function specifications()
    {
        return $this->hasMany(ProductSpecification::class);
    }

    public function specificationScores()
    {
        return $this->hasMany(ProductSpecificationScore::class);
    }

    public function finalScore()
    {
        return $this->hasOne(ProductFinalScore::class);
    }

    public function clicks()
    {
        return $this->hasMany(ProductClick::class);
    }

    public function salesStats()
    {
        return $this->hasMany(ProductSalesStat::class);
    }

    public function discounts()
    {
        return $this->hasMany(Discount::class);
    }
}
