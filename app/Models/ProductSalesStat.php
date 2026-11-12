<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSalesStat extends Model
{
    protected $fillable = [
        'product_id',
        'month',
        'year',
        'total_sold', 'source', 'source_reference', 'verification_status', 'is_simulated', 'sales_import_batch_id',
    ];

    protected $casts = ['is_simulated' => 'boolean'];

    public function scopeVerifiedReal(\Illuminate\Database\Eloquent\Builder $query): void
    {
        $query->where('verification_status', 'verified')->where('is_simulated', false)
            ->whereNotNull('source')->where('source', '!=', '')
            ->whereNotNull('source_reference')->where('source_reference', '!=', '');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
