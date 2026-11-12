<?php

use App\Models\Discount;
use App\Models\Product;
use Illuminate\Support\Carbon;

uses(Tests\TestCase::class);

it('applies the active discount inclusively without stacking promotions', function () {
    Carbon::setTestNow('2026-10-06');
    try {
        $product = new Product(['price' => 326000]);
        $product->setRelation('discounts', collect([
            new Discount(['percentage' => 20, 'start_date' => '2026-10-06', 'end_date' => '2026-10-31']),
            new Discount(['percentage' => 10, 'start_date' => '2026-10-01', 'end_date' => '2026-10-31']),
            new Discount(['percentage' => 50, 'start_date' => '2026-10-07', 'end_date' => '2026-10-31']),
            new Discount(['percentage' => 80, 'start_date' => '2026-10-01', 'end_date' => '2026-10-05']),
        ]));
        expect($product->active_discount_percentage)->toBe(20)
            ->and($product->selling_price)->toBe(260800.0)
            ->and($product->formatted_price)->toBe('Rp 260.800');
        Carbon::setTestNow('2026-11-01');
        expect($product->selling_price)->toBe(326000.0);
    } finally {
        Carbon::setTestNow();
    }
});

it('does not turn an unknown price into a free product', function () {
    $product = new Product(['price' => null]);
    expect($product->selling_price)->toBeNull()->and($product->formatted_price)->toBe('Harga belum tersedia');
});
