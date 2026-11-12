<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\Specification;
use App\Models\SpecificationGroup;
use App\Services\ProductFeatureIndex;

function featureProduct(array $values = [1080, 2, 1, 1, 1]): Product
{
    $category = Category::firstOrCreate(['name' => 'STB'], ['is_active' => true]);
    $group = SpecificationGroup::firstOrCreate(['name' => 'Test features']);
    $product = Product::create(['title' => 'STB', 'price' => 100, 'status' => 'active', 'category_id' => $category->id, 'comparison_type' => 'terrestrial']);
    $metadata = [];
    foreach (array_values(config('stb_analysis.feature_rubrics.terrestrial')) as $i => $rule) {
        $key = array_keys(config('stb_analysis.feature_rubrics.terrestrial'))[$i];
        $spec = Specification::firstOrCreate(['name' => $rule['name'], 'specification_group_id' => $group->id], ['data_type' => $rule['data_type'], 'unit' => $rule['unit']]);
        if (! array_key_exists($i, $values)) {
            continue;
        }
        ProductSpecification::create(['product_id' => $product->id, 'specification_id' => $spec->id, 'value' => $values[$i]]);
        $metadata[$spec->id] = ['key' => $key, 'verified_value' => $values[$i], 'public' => true, 'sources' => [['type' => 'official', 'url' => 'https://example.com/source']]];
    }
    $product->update(['reference_metadata' => ['specifications' => $metadata]]);

    return $product->fresh();
}

it('calculates a fixed sourced feature index independent of counterpart and comparison scores', function () {
    $product = featureProduct();
    $service = new ProductFeatureIndex;
    $first = $service->evaluate($product);
    expect($first['score'])->toBe(90.0)->and($first['complete'])->toBe(5)->and($first['ready'])->toBeTrue();
    featureProduct([2160, 2, 1, 1, 1]);
    expect($service->evaluate($product->fresh()))->toBe($first);
});

it('keeps unknown data unavailable rather than changing the denominator', function () {
    $result = (new ProductFeatureIndex)->evaluate(featureProduct([1080, 2, 1]));
    expect($result['score'])->toBeNull()->and($result['complete'])->toBe(3)->and($result['required'])->toBe(5)->and($result['missing'])->toHaveCount(2);
});

it('counts confirmed lack of support as zero and caps numeric capabilities at the rubric limit', function () {
    $result = (new ProductFeatureIndex)->evaluate(featureProduct([4320, 4, 0, 0, 1]));
    expect($result['score'])->toBe(60.0)->and($result['criteria'][0]['score'])->toBe(100.0)->and($result['criteria'][2]['score'])->toBe(0.0);
});

it('withholds an index if values drift from evidence or use the wrong USB unit', function () {
    $product = featureProduct();
    $record = $product->specifications()->first();
    $record->update(['value' => 2160]);
    expect((new ProductFeatureIndex)->evaluate($product->fresh())['score'])->toBeNull();
    $record->update(['value' => 1080]);
    $usb = $product->specifications()->whereHas('specification', fn ($q) => $q->where('name', 'Jumlah port USB'))->first();
    $usb->specification->update(['unit' => 'versi']);
    expect((new ProductFeatureIndex)->evaluate($product->fresh())['score'])->toBeNull();
});

it('rejects unsupported booleans and unverified or hidden evidence', function () {
    $product = featureProduct([1080, 2, 2, 1, 1]);
    expect((new ProductFeatureIndex)->evaluate($product)['score'])->toBeNull();
    $product = featureProduct();
    $metadata = $product->reference_metadata;
    $id = array_key_first($metadata['specifications']);
    $metadata['specifications'][$id]['sources'] = [];
    $product->update(['reference_metadata' => $metadata]);
    expect((new ProductFeatureIndex)->evaluate($product)['score'])->toBeNull();
    $metadata['specifications'][$id]['sources'] = [['type' => 'photo', 'path' => 'products/source.jpg']];
    $metadata['specifications'][$id]['public'] = false;
    $product->update(['reference_metadata' => $metadata]);
    expect((new ProductFeatureIndex)->evaluate($product)['score'])->toBeNull();
});
