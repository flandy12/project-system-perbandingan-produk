<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title',
        'official_url',
        'description',
        'image',
        'price',
        'production_year',
        'release_year',
        'stock',
        'category_id',
        'status',
        'comparison_type',
        'reference_metadata',
    ];

    protected static function booted(): void
    {
        static::created(function (Product $product) {
            if (! $product->product_code) {
                $product->forceFill(['product_code' => 'STB-'.str_pad((string) $product->id, 4, '0', STR_PAD_LEFT)])->saveQuietly();
            }
        });
    }

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'reference_metadata' => 'array',
    ];

    public function getActiveDiscountPercentageAttribute(): int
    {
        $today = now()->toDateString();
        if (! $this->exists && ! $this->relationLoaded('discounts')) {
            return 0;
        }
        $this->loadMissing('discounts');
        $discounts = $this->discounts;

        return (int) $discounts->filter(fn ($discount) => $discount->start_date->toDateString() <= $today
            && $discount->end_date->toDateString() >= $today)->max('percentage');
    }

    public function getSellingPriceAttribute(): ?float
    {
        return $this->price === null ? null : round((float) $this->price * (100 - min(100, max(0, $this->active_discount_percentage))) / 100);
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->price === null ? 'Harga belum tersedia' : 'Rp '.number_format($this->selling_price, 0, ',', '.');
    }

    public function getCatalogueSummaryAttribute(): string
    {
        return $this->reference_metadata['summary'] ?? trim(html_entity_decode(strip_tags(preg_replace('/<\/(p|li|div)>/i', ' ', $this->description ?? ''))));
    }

    public function getCatalogueHighlightsAttribute(): array
    {
        return $this->reference_metadata['highlights'] ?? [];
    }

    public function getPriceLabelAttribute(): string
    {
        $reference = $this->reference_metadata['price_reference'] ?? null;
        if (! $reference) {
            return '';
        }

        if (($reference['status'] ?? '') === 'variant_unconfirmed') {
            return 'Harga varian belum terverifikasi';
        }

        return ($reference['type'] ?? '') === 'official' ? 'Harga tercantum resmi' : (in_array($reference['status'] ?? '', ['indexed', 'out_of_stock'], true) ? 'Harga referensi terakhir' : 'Harga referensi penjual');
    }

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

    // OPTIONAL (advanced)
    public function specs()
    {
        return $this->hasManyThrough(
            Specification::class,
            ProductSpecification::class,
            'product_id',
            'id',
            'id',
            'specification_id'
        );
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function salesStat()
    {
        return $this->hasOne(ProductSalesStat::class, 'product_id');
    }
}
