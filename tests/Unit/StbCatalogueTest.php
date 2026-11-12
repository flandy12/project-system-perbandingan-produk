<?php

use App\Models\Product;
use App\Models\ProductSpecificationScore;
use App\Models\Specification;
use App\Models\SpecificationGroup;
use App\Services\CompareService;

function stbCatalogue(): array
{
    return json_decode(file_get_contents(__DIR__.'/../../docs/data/stb-catalogue-2026-10-04.json'), true, flags: JSON_THROW_ON_ERROR);
}

function catalogueFixture(array $row): Product
{
    $product = new Product(['title' => $row['title'], 'comparison_type' => $row['comparison_type']]);
    $groups = ['Teknologi' => 1, 'Koneksivitas' => 2, 'Kualitas' => 3, 'Fitur' => 4];
    $scores = collect();
    foreach ($row['specifications'] as $entry) {
        if ($entry['reference'] === null) {
            continue;
        }
        $spec = new Specification(['name' => $entry['name'], 'unit' => $entry['unit'], 'specification_group_id' => $groups[$entry['group']]]);
        $spec->setRelation('group', new SpecificationGroup(['name' => $entry['group']]));
        // Same documented specification gets the same identifier for every fixture.
        $id = sprintf('%u', crc32($entry['name']));
        $score = new ProductSpecificationScore(['specification_id' => $id, 'raw_value' => $entry['value'], 'normalized_score' => round(min($entry['value'] / $entry['reference'] * 100, 100), 2)]);
        $scores->push($score->setRelation('specification', $spec));
    }

    return $product->setRelation('specificationScores', $scores);
}

it('accounts for every photo as a separately keyed catalogue entry', function () {
    $rows = stbCatalogue()['products'];
    $photos = array_merge(array_column($rows, 'original_photo'), array_merge(...array_map(fn ($row) => array_column($row['alternate_images'], 'original'), $rows)));
    expect($rows)->toHaveCount(19)->and(array_unique(array_column($rows, 'key')))->toHaveCount(19)
        ->and($photos)->toHaveCount(19)->and(array_unique($photos))->toHaveCount(19);
    foreach ($rows as $row) {
        expect(is_file(__DIR__.'/../../storage/app/public/'.$row['image']))->toBeTrue();
    }
});

it('scores only sourced numeric facts and excludes ambiguous or subjective criteria', function () {
    foreach (stbCatalogue()['products'] as $row) {
        expect($row['price'] === null || $row['price_source'] !== null)->toBeTrue();
        foreach ($row['specifications'] as $entry) {
            expect($entry['sources'])->not->toBeEmpty();
            expect(str_starts_with($entry['name'], 'Simulasi:'))->toBeFalse();
            if (in_array($entry['key'], ['memory', 'flash', 'chip', 'app_count', 'menu', 'usb_min', 'fm'])) {
                expect($entry['reference'])->toBeNull();
            }
            if ($row['key'] === 'apple-kuning' && $entry['key'] === 'usb') {
                expect($entry['reference'])->toBeNull();
            }
            if ($entry['reference'] !== null) {
                expect(is_numeric($entry['value']))->toBeTrue()->and($entry['reference'])->toBeGreaterThan(0);
            }
        }
    }
});

it('compares all catalogue pairs while avoiding winners across device types', function () {
    $rows = stbCatalogue()['products'];
    $service = new CompareService;
    $groups = [1 => 'Teknologi', 2 => 'Koneksivitas', 3 => 'Kualitas', 4 => 'Fitur'];
    foreach ($rows as $i => $a) {
        foreach (array_slice($rows, $i + 1) as $b) {
            $result = $service->compare(catalogueFixture($a), catalogueFixture($b), $groups);
            expect($result['hasData'])->toBeTrue()->and($result['hasDemoData'])->toBeFalse();
            foreach (array_merge($result['metricsA'], $result['metricsB']) as $value) {
                expect($value === null || ($value >= 0 && $value <= 100))->toBeTrue();
            }
            if ($a['comparison_type'] !== $b['comparison_type']) {
                expect($result['winner'])->toBeNull()->and($result['comparisonStatus'])->toBe('different_type');
            }
        }
    }
});

it('distinguishes unknown price from a genuine zero price', function () {
    expect((new Product(['price' => null]))->formatted_price)->toBe('Harga belum tersedia')
        ->and((new Product(['price' => 0]))->formatted_price)->toBe('Rp 0');
});

it('does not inherit modern hardware or prices for old Mola combo photographs', function () {
    foreach (stbCatalogue()['products'] as $row) {
        if (! in_array($row['key'], ['mola-nex-dangdut-combo', 'mola-nex-merah-combo'], true)) {
            continue;
        }
        expect($row['price'])->toBeNull()
            ->and($row['price_reference']['status'])->toBe('variant_unconfirmed')
            ->and(array_column($row['specifications'], 'key'))->not->toContain('chip', 'ram', 'rom', 'usb');
        foreach ($row['specifications'] as $spec) {
            expect(array_unique(array_column($spec['sources'], 'type')))->toBe(['photo']);
        }
    }
});
