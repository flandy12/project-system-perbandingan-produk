<?php

use App\Models\Product;
use App\Services\ProductRecommendations;

function recommendedFixture(int $id, int $sales, int $reviews, float $rating, string $type = 'terrestrial'): Product
{
    return (new Product(['comparison_type' => $type]))->forceFill(['id' => $id, 'sales_stats_sum_total_sold' => $sales, 'ratings_count' => $reviews, 'ratings_avg_rating' => $rating]);
}

it('prioritizes recorded purchases and requires three reviews for a rating recommendation', function () {
    $result = (new ProductRecommendations)->select(collect([
        recommendedFixture(1, 0, 1, 5),
        recommendedFixture(2, 10, 0, 0),
        recommendedFixture(3, 0, 3, 4.8),
        recommendedFixture(4, 10, 4, 4.5),
    ]));
    expect($result['products']->pluck('id')->all())->toBe([4, 2, 3]);
});

it('provides catalogue variety without claiming an unsupported ranking when evidence is insufficient', function () {
    $result = (new ProductRecommendations)->select(collect([
        recommendedFixture(1, 0, 0, 0), recommendedFixture(2, 0, 0, 0), recommendedFixture(3, 0, 0, 0),
        recommendedFixture(4, 0, 0, 0, 'satellite'), recommendedFixture(5, 0, 0, 0, 'android'),
    ]));
    expect($result['products']->pluck('id')->all())->toBe([1, 2, 4, 5])
        ->and($result['description'])->toContain('belum cukup')
        ->and($result['products'][0]->recommendation_reason)->toBe('TV digital antena UHF');
});
