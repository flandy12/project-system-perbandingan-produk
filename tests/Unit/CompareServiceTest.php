<?php

use App\Models\Product;
use App\Models\ProductSpecificationScore;
use App\Models\Specification;
use App\Models\SpecificationGroup;
use App\Services\CompareService;

function comparisonProduct(array $values): Product
{
    $product = new Product;
    $scores = collect();
    foreach ($values as $id => [$groupId, $value]) {
        $group = new SpecificationGroup(['name' => "Kelompok {$groupId}"]);
        $spec = new Specification(['name' => "Spesifikasi {$id}", 'specification_group_id' => $groupId]);
        $spec->setRelation('group', $group);
        $score = new ProductSpecificationScore(['specification_id' => $id, 'normalized_score' => $value]);
        $score->setRelation('specification', $spec);
        $scores->push($score);
    }

    return $product->setRelation('specificationScores', $scores);
}

it('distinguishes missing scores from zero scores', function () {
    $service = new CompareService;
    $empty = $service->compare(comparisonProduct([]), comparisonProduct([]), [1 => 'Kualitas']);
    expect($empty['hasData'])->toBeFalse()->and($empty['percentA'])->toBeNull()->and($empty['metricsA'])->toBe([null]);
    $zero = $service->compare(comparisonProduct([1 => [1, 0]]), comparisonProduct([1 => [1, 0]]));
    expect($zero['hasData'])->toBeTrue()->and($zero['percentA'])->toBe(0.0)->and($zero['winner'])->toBeNull();
});

it('uses only shared specifications and averages groups equally', function () {
    $result = (new CompareService)->compare(
        comparisonProduct([1 => [1, 80], 2 => [1, 100], 3 => [2, 50], 4 => [2, 100]]),
        comparisonProduct([1 => [1, 60], 2 => [1, 80], 3 => [2, 50]]),
        [1 => 'Kualitas', 2 => 'Fitur', 3 => 'Daya']
    );
    expect($result['metricsA'])->toBe([90.0, 50.0, null])
        ->and($result['percentA'])->toBe(70.0)
        ->and($result['percentB'])->toBe(60.0)
        ->and($result['winner'])->toBe('a')
        ->and($result['comparedCount'])->toBe(3)
        ->and($result['missingGroups'])->toBe(['Daya']);
});

it('selects product b or a tie from actual scores', function () {
    $service = new CompareService;
    $result = $service->compare(comparisonProduct([1 => [1, 30], 2 => [2, 30]]), comparisonProduct([1 => [1, 80], 2 => [2, 80]]));
    expect($result['winner'])->toBe('b')->and($result['reasons'][0])->toContain('80.00 vs 30.00');
    $tie = $service->compare(comparisonProduct([1 => [1, 80]]), comparisonProduct([1 => [1, 80]]));
    expect($tie['winner'])->toBeNull()->and($tie['percentA'])->toBe(80.0);
});

it('does not compare unrelated or invalid scores', function () {
    $service = new CompareService;
    $unrelated = $service->compare(comparisonProduct([1 => [1, 80]]), comparisonProduct([2 => [1, 80]]), [1 => 'Kualitas']);
    expect($unrelated['hasData'])->toBeFalse();
    $invalid = $service->compare(comparisonProduct([1 => [1, 101]]), comparisonProduct([1 => [1, 80]]), [1 => 'Kualitas']);
    expect($invalid['hasData'])->toBeFalse();
});

it('highlights confirmed quantities while never treating missing features as absent', function () {
    $a = comparisonProduct([]);
    $b = comparisonProduct([]);
    $usb = new Specification(['name' => 'Jumlah port USB', 'data_type' => 'number', 'unit' => 'port']);
    $fm = new Specification(['name' => 'Radio FM', 'data_type' => 'boolean']);
    $a->reference_metadata = ['specifications' => [1 => ['public' => true]]];
    $b->reference_metadata = ['specifications' => [1 => ['public' => true], 2 => ['public' => true]]];
    $a->setRelation('specifications', collect([(new \App\Models\ProductSpecification(['specification_id' => 1, 'value' => '1']))->setRelation('specification', $usb)]));
    $b->setRelation('specifications', collect([
        (new \App\Models\ProductSpecification(['specification_id' => 1, 'value' => '2']))->setRelation('specification', $usb),
        (new \App\Models\ProductSpecification(['specification_id' => 2, 'value' => '1']))->setRelation('specification', $fm),
    ]));
    $rows = (new CompareService)->compare($a, $b)['specificationRows'];
    expect($rows[0]['advantage'])->toBe('b')->and($rows[1]['a'])->toBe('Belum tersedia')->and($rows[1]['advantage'])->toBeNull();
});

it('explains why changing shared criteria changes the same products score', function () {
    $service = new CompareService;
    $burger = comparisonProduct([1 => [1, 33.33], 2 => [2, 100], 3 => [3, 100], 4 => [4, 100], 5 => [3, 50]]);
    $silver = comparisonProduct([1 => [1, 66.67], 2 => [2, 100], 3 => [3, 100], 4 => [4, 100]]);
    $ip = comparisonProduct([1 => [1, 66.67], 2 => [2, 100], 3 => [3, 100], 4 => [4, 100], 5 => [3, 50]]);
    $first = $service->compare($burger, $silver);
    $second = $service->compare($burger, $ip);
    expect(round($first['percentA'], 1))->toBe(83.3)
        ->and(round($first['percentB'], 1))->toBe(91.7)
        ->and(round($second['percentA'], 1))->toBe(77.1)
        ->and(round($second['percentB'], 1))->toBe(85.4)
        ->and(count($first['scoreCriteria']))->toBe(4)
        ->and(count($second['scoreCriteria']))->toBe(5);
});

it('labels indexed prices and rejects unconfirmed variant prices', function () {
    $product = new Product(['reference_metadata' => ['price_reference' => ['status' => 'indexed']]]);
    expect($product->price_label)->toBe('Harga referensi terakhir');
    $product->reference_metadata = ['price_reference' => ['status' => 'variant_unconfirmed']];
    expect($product->price_label)->toBe('Harga varian belum terverifikasi');
});
