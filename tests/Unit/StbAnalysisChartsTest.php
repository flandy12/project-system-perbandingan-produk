<?php

use App\Models\StbAnalysisRun;
use App\Services\StbAnalysisCharts;

function chartRun(array $values, array $evaluation, int $k = 2): StbAnalysisRun
{
    return (new StbAnalysisRun)->forceFill(['status' => 'ready', 'snapshot' => ['rows' => array_map(fn ($i) => [
        'code' => 'STB-'.$i, 'title' => 'Product '.$i, 'values' => $values[$i]], array_keys($values))],
        'result' => ['k' => $k, 'assignments' => array_map(fn ($i) => $i % $k, array_keys($values)), 'evaluation' => $evaluation]]);
}

it('plots stored raw values and evaluation without reclustering or changing the snapshot', function () {
    $run = chartRun([[100000, 70, 0], [200000, 90, 100], [300000, 80, 50]],
        [['k' => 2, 'sse' => 1.5, 'silhouette' => 0.3], ['k' => 3, 'sse' => 0.5, 'silhouette' => 0.2]]);
    $before = $run->getAttributes();
    $charts = (new StbAnalysisCharts)->build($run);
    expect($run->getAttributes())->toBe($before)->and($charts['points'][0]['values'])->toBe([100000, 70, 0])
        ->and($charts['points'][0]['y'])->toBe(245.0)
        ->and($charts['points'][1]['bar_width'])->toBe(382.5)
        ->and($charts['points'][0]['color'])->toBe($charts['legend'][0]['color'])
        ->and(array_column($charts['sse']['points'], 'value'))->toBe([1.5, 0.5])
        ->and(array_column($charts['silhouette']['points'], 'selected'))->toBe([true, false]);
});
it('keeps geometry finite for identical prices zero sales and one evaluated k', function () {
    $run = chartRun([[100000, 0, 0], [100000, 100, 0], [100000, 50, 0]], [['k' => 2, 'sse' => 0, 'silhouette' => 0]]);
    $charts = (new StbAnalysisCharts)->build($run);
    foreach ($charts['points'] as $p) {
        expect(is_finite($p['x']))->toBeTrue()->and(is_finite($p['y']))->toBeTrue()
            ->and($p['x'])->toBeGreaterThanOrEqual(80)->toBeLessThanOrEqual(555);
    }
    expect($charts['sse']['points'][0]['x'])->toBe(317.5)
        ->and($charts['silhouette']['selected_x'])->toBe(317.5)
        ->and($charts['points'][0]['bar_width'])->toBe(0.0);
});
it('does not render charts for insufficient data and supports negative silhouette curves', function () {
    $service = new StbAnalysisCharts;
    expect($service->build((new StbAnalysisRun)->forceFill(['status' => 'insufficient', 'result' => null])))->toBeNull();
    $run = chartRun([[10, 10, 0], [20, 20, 1], [30, 30, 2]], [['k' => 2, 'sse' => 2, 'silhouette' => -0.8], ['k' => 3, 'sse' => 1, 'silhouette' => -0.4]]);
    $charts = $service->build($run);
    expect($charts['silhouette']['ticks'][0]['value'])->toBeGreaterThanOrEqual(-1)
        ->and($charts['silhouette']['ticks'][4]['value'])->toBeLessThanOrEqual(1)
        ->and($charts['silhouette']['points'][0]['y'])->toBeGreaterThan($charts['silhouette']['points'][1]['y']);
});
