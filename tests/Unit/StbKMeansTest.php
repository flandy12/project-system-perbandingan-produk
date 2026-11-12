<?php

use App\Services\StbKMeans;

it('separates two clouds deterministically with consistent centroids and bounded normalization', function () {
    $values = [[100000, 20, 10], [110000, 22, 12], [120000, 24, 14], [500000, 90, 200], [510000, 92, 202], [520000, 94, 204]];
    $engine = new StbKMeans;
    $result = $engine->analyze($values, 2);
    expect($engine->analyze($values, 2))->toBe($result)->and($result['converged'])->toBeTrue()
        ->and($result['silhouette'])->toBeGreaterThan(0.9)->and($result['sse'])->toBeLessThan(0.02);
    expect(array_unique(array_slice($result['assignments'], 0, 3)))->toHaveCount(1);
    expect($result['assignments'][0])->not->toBe($result['assignments'][3]);
    foreach ($result['centroids_raw'] as $c => $center) {
        $members = array_filter($values, fn ($i) => $result['assignments'][$i] === $c, ARRAY_FILTER_USE_KEY);
        foreach (range(0, 2) as $j) {
            expect($center[$j])->toEqualWithDelta(array_sum(array_column($members, $j)) / count($members), 1e-8);
        }
    }
    foreach ($result['normalized'] as $row) {
        foreach ($row as $value) {
            expect($value)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(1);
        }
    }
});
it('is invariant to currency scaling and translation', function () {
    $values = [[100, 10, 5], [105, 15, 10], [500, 90, 200], [510, 95, 220]];
    $scaled = array_map(fn ($r) => [$r[0] * 1000 + 10, $r[1], $r[2]], $values);
    $engine = new StbKMeans;
    expect($engine->analyze($values, 2)['assignments'])->toBe($engine->analyze($scaled, 2)['assignments']);
});
it('handles constant features duplicates and singleton silhouette', function () {
    $r = (new StbKMeans)->analyze([[1, 50, 0], [1, 50, 0], [10, 50, 100]], 2);
    expect($r['ranges'][1]['constant'])->toBeTrue()->and($r['silhouette'])->toEqualWithDelta(2 / 3, 1e-9)
        ->and(array_unique($r['assignments']))->toHaveCount(2);
});
it('rejects insufficient identical nonfinite negative and invalid k input', function () {
    $engine = new StbKMeans;
    foreach ([[[1, 2, 3], [2, 3, 4]], [[1, 1, 1], [1, 1, 1], [1, 1, 1]], [[1, 2, 3], [2, 3, 4], [INF, 3, 5]], [[-1, 2, 3], [2, 3, 4], [4, 5, 6]]] as $rows) {
        expect(fn () => $engine->analyze($rows))->toThrow(InvalidArgumentException::class);
    }
    expect(fn () => $engine->analyze([[1, 2, 3], [2, 3, 4], [4, 5, 6]], 3))->toThrow(InvalidArgumentException::class);
});
it('chooses the highest silhouette among evaluated candidates', function () {
    $r = (new StbKMeans)->analyze([[1, 10, 1], [2, 11, 2], [3, 12, 3], [90, 90, 90], [91, 91, 91], [92, 92, 92]]);
    expect($r['k'])->toBe(2)->and($r['silhouette'])->toBe(max(array_column($r['evaluation'], 'silhouette')));
});
