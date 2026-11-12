<?php

namespace App\Services;

use App\Models\StbAnalysisRun;

/** SVG geometry only: reads the stored snapshot, never runs clustering or queries products. */
class StbAnalysisCharts
{
    public const COLORS = ['#2563eb', '#ea580c', '#059669', '#9333ea', '#0891b2', '#be123c'];

    public function build(StbAnalysisRun $run): ?array
    {
        if ($run->status !== 'ready' || ! $run->result || empty($run->snapshot['rows'])) {
            return null;
        }
        $rows = $run->snapshot['rows'];
        $result = $run->result;
        $prices = array_column(array_column($rows, 'values'), 0);
        $units = array_column(array_column($rows, 'values'), 2);
        $span = max(1000, max($prices) - min($prices));
        $xMin = max(0, min($prices) - $span * 0.08);
        $xMax = max($prices) + $span * 0.08;
        $yMax = max(1, ceil(max($units) * 1.1));
        $points = [];
        foreach ($rows as $i => $row) {
            $cluster = $result['assignments'][$i];
            $points[] = ['code' => $row['code'], 'title' => $row['title'], 'cluster' => $cluster,
                'color' => self::COLORS[$cluster % count(self::COLORS)], 'values' => $row['values'],
                'x' => $this->scale($row['values'][0], $xMin, $xMax, 80, 555),
                'y' => $this->scale($row['values'][2], 0, $yMax, 245, 35),
                'bar_width' => $this->scale($row['values'][1], 0, 100, 0, 425), 'bar_y' => 35 + $i * 32];
        }
        $evaluation = $result['evaluation'];
        $silhouettes = array_column($evaluation, 'silhouette');
        $sMin = max(-1, floor(min($silhouettes) * 10 - 1) / 10);
        $sMax = min(1, ceil(max($silhouettes) * 10 + 1) / 10);

        return ['points' => $points, 'bar_height' => max(140, count($rows) * 32 + 75),
            'legend' => array_map(fn ($c) => ['cluster' => $c, 'color' => self::COLORS[$c % count(self::COLORS)],
                'count' => count(array_filter($result['assignments'], fn ($label) => $label === $c))], range(0, $result['k'] - 1)),
            'x_ticks' => $this->ticks($xMin, $xMax, 80, 555), 'y_ticks' => $this->ticks(0, $yMax, 245, 35),
            'sse' => $this->curve($evaluation, 'sse', 0, max(0.1, ceil(max(array_column($evaluation, 'sse')) * 10) / 10), $result['k']),
            'silhouette' => $this->curve($evaluation, 'silhouette', $sMin, $sMax, $result['k'])];
    }

    private function curve(array $evaluation, string $metric, float $min, float $max, int $chosen): array
    {
        $ks = array_column($evaluation, 'k');
        $kMin = min($ks);
        $kMax = max($ks);
        $points = array_map(fn ($row) => ['k' => $row['k'], 'value' => $row[$metric], 'selected' => $row['k'] === $chosen,
            'x' => $this->scale($row['k'], $kMin, $kMax, 80, 555), 'y' => $this->scale($row[$metric], $min, $max, 245, 35)], $evaluation);

        return ['points' => $points, 'path' => implode(' ', array_map(fn ($p) => $p['x'].','.$p['y'], $points)),
            'ticks' => $this->ticks($min, $max, 245, 35), 'selected_x' => $this->scale($chosen, $kMin, $kMax, 80, 555)];
    }

    private function ticks(float $min, float $max, float $start, float $end): array
    {
        return array_map(fn ($i) => ['value' => $min + ($max - $min) * $i / 4,
            'position' => $start + ($end - $start) * $i / 4], range(0, 4));
    }

    private function scale(float $value, float $min, float $max, float $start, float $end): float
    {
        return round($max > $min ? $start + ($value - $min) / ($max - $min) * ($end - $start) : ($start + $end) / 2, 3);
    }
}
