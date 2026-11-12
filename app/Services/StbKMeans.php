<?php

namespace App\Services;

use InvalidArgumentException;

/** Pure PHP: fixed three columns, equal weights, deterministic farthest-first restarts. */
class StbKMeans
{
    public function analyze(array $values, ?int $requestedK = null): array
    {
        $n = count($values);
        if ($n < 3 || $n > 500) {
            throw new InvalidArgumentException('Analisis membutuhkan 3–500 produk lengkap dalam satu jenis dan periode.');
        }
        foreach ($values as $row) {
            if (count($row) !== 3) {
                throw new InvalidArgumentException('Dataset harus memiliki tiga variabel.');
            }
            foreach ($row as $value) {
                if (! is_numeric($value) || ! is_finite((float) $value) || (float) $value < 0) {
                    throw new InvalidArgumentException('Nilai harus berupa angka terbatas dan tidak negatif.');
                }
            }
        }
        $ranges = [];
        $normalized = [];
        for ($j = 0; $j < 3; $j++) {
            $column = array_map(fn ($row) => (float) $row[$j], $values);
            $ranges[] = ['min' => min($column), 'max' => max($column), 'constant' => min($column) === max($column)];
        }
        foreach ($values as $row) {
            $normalized[] = array_map(fn ($j) => $ranges[$j]['constant'] ? 0.0 : ((float) $row[$j] - $ranges[$j]['min']) / ($ranges[$j]['max'] - $ranges[$j]['min']), range(0, 2));
        }
        $distinct = count(array_unique(array_map(fn ($row) => json_encode($row), $normalized)));
        $maxK = min(6, $n - 1, $distinct);
        if ($maxK < 2 || ($requestedK !== null && ($requestedK < 2 || $requestedK > $maxK))) {
            throw new InvalidArgumentException('K harus 2–'.$maxK.', lebih kecil dari jumlah produk dan tidak melebihi jumlah vektor berbeda.');
        }
        $candidates = [];
        for ($k = 2; $k <= $maxK; $k++) {
            $best = null;
            // Different initial points without global RNG state. Keep best converged SSE.
            for ($restart = 0; $restart < min(10, $n); $restart++) {
                $fit = $this->fit($normalized, $k, (int) floor($restart * $n / min(10, $n)));
                if ($fit['converged'] && ($best === null || $fit['sse'] < $best['sse'])) {
                    $best = $fit;
                }
            }
            if ($best !== null) {
                $best['silhouette'] = $this->silhouette($normalized, $best['assignments'], $k);
                $candidates[$k] = $best;
            }
        }
        if (! $candidates || ($requestedK !== null && ! isset($candidates[$requestedK]))) {
            throw new InvalidArgumentException('Proses belum konvergen; hasil tidak dapat diterbitkan.');
        }
        $chosen = $requestedK;
        if ($chosen === null) {
            foreach ($candidates as $k => $fit) {
                if ($chosen === null || $fit['silhouette'] > $candidates[$chosen]['silhouette'] + 1e-12) {
                    $chosen = $k;
                }
            }
        }
        $result = $candidates[$chosen];
        $result['k'] = $chosen;
        $result['selection'] = $requestedK === null ? 'Silhouette tertinggi di antara K yang diuji; nilai sama memilih K lebih kecil.' : 'K dipilih pengelola.';
        $result['ranges'] = $ranges;
        $result['normalized'] = $normalized;
        $result['centroids_raw'] = array_map(fn ($center) => array_map(fn ($j) => $center[$j] * ($ranges[$j]['max'] - $ranges[$j]['min']) + $ranges[$j]['min'], range(0, 2)), $result['centroids']);
        $result['evaluation'] = array_map(fn ($k) => ['k' => $k, 'sse' => $candidates[$k]['sse'], 'silhouette' => $candidates[$k]['silhouette']], array_keys($candidates));
        $result['warnings'] = [];
        if ($n < 10) {
            $result['warnings'][] = 'Cakupan kurang dari 10 produk: hasil eksploratif dan belum cukup untuk generalisasi.';
        }
        foreach ($ranges as $j => $range) {
            if ($range['constant']) {
                $result['warnings'][] = 'Variabel '.($j + 1).' konstan: dinormalisasi menjadi 0 dan tidak membedakan kelompok.';
            }
        }
        if ($result['silhouette'] <= 0) {
            $result['warnings'][] = 'Pemisahan kelompok lemah (Silhouette ≤ 0).';
        }

        return $result;
    }

    private function fit(array $points, int $k, int $first): array
    {
        $centers = [$points[$first]];
        while (count($centers) < $k) {
            $farthest = 0;
            $largest = -1;
            foreach ($points as $i => $point) {
                $d = min(array_map(fn ($c) => $this->distance($point, $c), $centers));
                if ($d > $largest) {
                    $largest = $d;
                    $farthest = $i;
                }
            }
            $centers[] = $points[$farthest];
        }
        $previous = null;
        $converged = false;
        for ($iteration = 1; $iteration <= 100; $iteration++) {
            $labels = [];
            foreach ($points as $point) {
                $distances = array_map(fn ($c) => $this->distance($point, $c), $centers);
                $labels[] = array_search(min($distances), $distances, true);
            }
            $counts = array_count_values($labels);
            for ($c = 0; $c < $k; $c++) {
                if (isset($counts[$c])) {
                    continue;
                }
                $move = null;
                $largest = -1;
                foreach ($points as $i => $point) {
                    if (($counts[$labels[$i]] ?? 0) <= 1) {
                        continue;
                    }
                    $d = $this->distance($point, $centers[$labels[$i]]);
                    if ($d > $largest) {
                        $largest = $d;
                        $move = $i;
                    }
                }
                if ($move === null) {
                    return ['converged' => false];
                }
                $counts[$labels[$move]]--;
                $labels[$move] = $c;
                $counts[$c] = 1;
            }
            $next = array_fill(0, $k, [0.0, 0.0, 0.0]);
            foreach ($points as $i => $point) {
                for ($j = 0; $j < 3; $j++) {
                    $next[$labels[$i]][$j] += $point[$j] / $counts[$labels[$i]];
                }
            }
            $centers = $next;
            if ($labels === $previous) {
                $converged = true;
                break;
            }
            $previous = $labels;
        }
        $distances = array_map(fn ($i) => $this->distance($points[$i], $centers[$labels[$i]]), array_keys($points));

        return ['assignments' => $labels, 'centroids' => $centers, 'sse' => array_sum($distances), 'distances' => array_map('sqrt', $distances), 'iterations' => min($iteration, 100), 'converged' => $converged];
    }

    private function distance(array $a, array $b): float
    {
        return array_sum(array_map(fn ($j) => ($a[$j] - $b[$j]) ** 2, range(0, 2)));
    }

    private function silhouette(array $points, array $labels, int $k): float
    {
        $scores = [];
        foreach ($points as $i => $point) {
            $groups = array_fill(0, $k, []);
            foreach ($points as $j => $other) {
                if ($i !== $j) {
                    $groups[$labels[$j]][] = sqrt($this->distance($point, $other));
                }
            }
            $own = $groups[$labels[$i]];
            if (! $own) {
                $scores[] = 0.0;

                continue;
            }
            $a = array_sum($own) / count($own);
            unset($groups[$labels[$i]]);
            $b = min(array_map(fn ($g) => array_sum($g) / count($g), $groups));
            $scores[] = max($a, $b) > 0 ? ($b - $a) / max($a, $b) : 0.0;
        }

        return array_sum($scores) / count($scores);
    }
}
