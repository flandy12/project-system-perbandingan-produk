<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpecificationScore;
use App\Models\Specification;
use App\Models\SpecificationGroup;
use App\Models\User;
use App\Services\CompareService;
use App\Services\RefreshStbSpecifications;
use App\Services\SaveProductSpecifications;
use App\Services\VerifiedComparisonScores;

function verifiedComparisonFixture(): array
{
    $category = Category::create(['name' => 'Verified fixture']);
    $group = SpecificationGroup::create(['name' => 'Koneksivitas']);
    $spec = Specification::create(['name' => 'Jumlah port USB', 'data_type' => 'numeric', 'unit' => 'port', 'specification_group_id' => $group->id]);
    $make = function ($title, $value) use ($category, $spec) {
        $p = Product::create(['title' => $title, 'status' => 'active', 'category_id' => $category->id, 'comparison_type' => 'terrestrial', 'price' => 100,
            'reference_metadata' => ['catalogue_key' => $title, 'specifications' => [$spec->id => ['key' => 'usb', 'verified_value' => $value, 'reference' => 2, 'public' => true,
                'sources' => [['type' => 'official', 'url' => 'https://example.com/specs']]]]]]);
        $p->specifications()->create(['specification_id' => $spec->id, 'value' => (string) $value]);

        return $p;
    };

    return [$make('a', 1), $make('b', 2), $spec, $group];
}

it('derives comparison from current verified facts rather than missing or obsolete cached scores', function () {
    [$a, $b, $spec, $group] = verifiedComparisonFixture();
    ProductSpecificationScore::create(['product_id' => $a->id, 'specification_id' => $spec->id, 'raw_value' => 2, 'normalized_score' => 100]);
    $result = app(CompareService::class)->compare($a, $b, [$group->id => 'Koneksivitas', 999 => 'Tanpa bukti']);
    expect($result['percentA'])->toBe(50.0)->and($result['percentB'])->toBe(100.0)->and($result['winner'])->toBeNull();
    $this->get('/compare?ids='.$a->id.','.$b->id)->assertOk()->assertSee('Grafik batang digunakan')->assertDontSee('cdn.jsdelivr.net/npm/chart.js', false)->assertSee('<svg', false);
    $a->specifications()->update(['value' => 2]);
    $result = app(CompareService::class)->compare($a->fresh(), $b->fresh());
    expect($result['hasData'])->toBeFalse()->and($result['specificationRows'][0]['a'])->toBe('Belum diverifikasi ulang');
    app(SaveProductSpecifications::class)->save($a->fresh(), [$spec->id => '2'], [$spec->id => ['confirmed' => 1, 'url' => 'https://example.com/specs', 'type' => 'official']], User::factory()->create()->id);
    expect(app(VerifiedComparisonScores::class)->forProduct($a->fresh())->first()->normalized_score)->toBe('100.00');
});

it('does not score explicit exclusions or unsupported evidence and preserves confirmed zero', function () {
    [$a, $b, $spec] = verifiedComparisonFixture();
    $meta = $a->reference_metadata;
    $meta['specifications'][$spec->id]['reference'] = null;
    $a->update(['reference_metadata' => $meta]);
    expect(app(VerifiedComparisonScores::class)->forProduct($a->fresh()))->toBeEmpty();
    $meta['specifications'][$spec->id]['reference'] = 2;
    $meta['specifications'][$spec->id]['verified_value'] = 0;
    $a->specifications()->update(['value' => '0']);
    $a->update(['reference_metadata' => $meta]);
    expect((float) app(VerifiedComparisonScores::class)->forProduct($a->fresh())->first()->normalized_score)->toBe(0.0);
    $meta['specifications'][$spec->id]['sources'] = [];
    $a->update(['reference_metadata' => $meta]);
    expect(app(VerifiedComparisonScores::class)->forProduct($a->fresh()))->toBeEmpty();
});

it('applies the official research patch repeatably while preserving prices and admin changes', function () {
    [$a, $b, $spec] = verifiedComparisonFixture();
    $patch = ['checked_at' => '2026-11-06', 'products' => [['key' => 'a', 'url' => 'https://example.com/specs', 'facts' => [
        ['key' => 'usb', 'name' => 'Jumlah port USB', 'group' => 'Koneksivitas', 'data_type' => 'numeric', 'unit' => 'port', 'value' => 1, 'reference' => 2, 'note' => 'Official model page'],
    ]]]];
    $base = ['products' => [['key' => 'a', 'specifications' => [['key' => 'usb', 'value' => 1]]]]];
    $service = app(RefreshStbSpecifications::class);
    expect($service->apply($patch, $base)['conflicts'])->toBeEmpty();
    $before = $a->fresh()->reference_metadata;
    $service->apply($patch, $base);
    expect($a->fresh()->reference_metadata)->toBe($before)->and((float) $a->fresh()->price)->toBe(100.0);
    $a->specifications()->update(['value' => '2']);
    expect($service->apply($patch, $base)['conflicts'])->toHaveCount(1)->and($a->fresh()->specifications->first()->value)->toBe('2');
});
