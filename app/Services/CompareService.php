<?php

namespace App\Services;

use App\Models\Product;

class CompareService
{
    public function compare(Product $a, Product $b, array $groups = []): array
    {
        $scoresA = app(VerifiedComparisonScores::class)->forProduct($a)->keyBy('specification_id');
        $scoresB = app(VerifiedComparisonScores::class)->forProduct($b)->keyBy('specification_id');
        $bucketsA = $bucketsB = [];
        $advantagesA = $advantagesB = [];
        $comparedCount = 0;
        $hasDemoData = false;
        $scoreCriteria = [];

        foreach ($scoresA as $id => $scoreA) {
            $scoreB = $scoresB->get($id);
            $spec = $scoreA->specification;
            if (! $scoreB || ! $spec || ! $spec->group || str_starts_with($spec->name, 'Simulasi:')) {
                continue;
            }

            $valueA = $scoreA->normalized_score;
            $valueB = $scoreB->normalized_score;
            if ($scoreA->evidence_reference !== null && $scoreB->evidence_reference !== null
                && abs($scoreA->evidence_reference - $scoreB->evidence_reference) > 0.000001) {
                continue;
            }
            if (! is_numeric($valueA) || ! is_numeric($valueB)
                || $valueA < 0 || $valueA > 100 || $valueB < 0 || $valueB > 100) {
                continue;
            }

            $groupId = $spec->specification_group_id;
            $groups[$groupId] ??= $spec->group->name;
            $bucketsA[$groupId][] = (float) $valueA;
            $bucketsB[$groupId][] = (float) $valueB;
            $comparedCount++;
            $scoreCriteria[] = ['name' => $spec->name, 'group' => $spec->group->name,
                'rawA' => $scoreA->raw_value, 'rawB' => $scoreB->raw_value, 'unit' => $spec->unit,
                'referenceA' => $scoreA->evidence_reference ?? $a->reference_metadata['specifications'][$id]['reference'] ?? null,
                'referenceB' => $scoreB->evidence_reference ?? $b->reference_metadata['specifications'][$id]['reference'] ?? null,
                'a' => (float) $valueA, 'b' => (float) $valueB];
            $hasDemoData = $hasDemoData || str_starts_with($spec->name, 'Simulasi:');

            $unit = $spec->unit ? " {$spec->unit}" : '';
            $rawA = is_numeric($scoreA->raw_value) ? rtrim(rtrim(number_format((float) $scoreA->raw_value, 2, '.', ''), '0'), '.') : null;
            $rawB = is_numeric($scoreB->raw_value) ? rtrim(rtrim(number_format((float) $scoreB->raw_value, 2, '.', ''), '0'), '.') : null;
            $detailA = $rawA !== null && $rawB !== null ? " Nilai asli: {$rawA}{$unit} vs {$rawB}{$unit}." : '';
            $detailB = $rawA !== null && $rawB !== null ? " Nilai asli: {$rawB}{$unit} vs {$rawA}{$unit}." : '';

            if ($valueA > $valueB) {
                $advantagesA[] = "Skor {$spec->name} lebih tinggi ({$valueA} vs {$valueB}).{$detailA}";
            } elseif ($valueB > $valueA) {
                $advantagesB[] = "Skor {$spec->name} lebih tinggi ({$valueB} vs {$valueA}).{$detailB}";
            }
        }

        $metricsA = $metricsB = [];
        foreach ($groups as $id => $name) {
            $metricsA[] = isset($bucketsA[$id]) ? array_sum($bucketsA[$id]) / count($bucketsA[$id]) : null;
            $metricsB[] = isset($bucketsB[$id]) ? array_sum($bucketsB[$id]) / count($bucketsB[$id]) : null;
        }

        $availableA = array_filter($metricsA, fn ($value) => $value !== null);
        $availableB = array_filter($metricsB, fn ($value) => $value !== null);
        $scoreA = $availableA ? array_sum($availableA) / count($availableA) : null;
        $scoreB = $availableB ? array_sum($availableB) / count($availableB) : null;
        $hasData = $comparedCount > 0;
        $differentTypes = $a->comparison_type && $b->comparison_type && $a->comparison_type !== $b->comparison_type;
        $insufficient = count($availableA) < 2;
        $status = ! $hasData ? 'empty' : ($differentTypes ? 'different_type' : ($insufficient ? 'insufficient' : (abs($scoreA - $scoreB) < 0.000001 ? 'tie' : 'winner')));
        $winner = $status === 'winner' ? ($scoreA > $scoreB ? 'a' : 'b') : null;
        $reasons = match ($status) {
            'empty' => ['Belum ada skor spesifikasi yang tersedia pada kedua produk.'],
            'different_type' => ['Jenis perangkat berbeda. Grafik memperlihatkan kemampuan bersama, tetapi tidak menentukan pemenang keseluruhan.'],
            'insufficient' => ['Data bersama baru mencakup satu kelompok. Belum cukup untuk menentukan pemenang keseluruhan.'],
            'tie' => ['Skor rata-rata pada data bersama sama; kemampuan yang belum terdokumentasi tidak ikut dinilai.'],
            default => $winner === 'a' ? $advantagesA : $advantagesB,
        };

        return [
            'labels' => array_values($groups),
            'metricsA' => $metricsA,
            'metricsB' => $metricsB,
            'percentA' => $scoreA,
            'percentB' => $scoreB,
            'hasData' => $hasData,
            'hasDemoData' => $hasDemoData,
            'missingGroups' => array_values(array_filter(array_values($groups), fn ($name, $index) => $metricsA[$index] === null, ARRAY_FILTER_USE_BOTH)),
            'comparedCount' => $comparedCount,
            'winner' => $winner,
            'comparisonStatus' => $status,
            'reasons' => $reasons,
            'specificationRows' => $this->specificationRows($a, $b),
            'scoreCriteria' => $scoreCriteria,
        ];
    }

    private function specificationRows(Product $a, Product $b): array
    {
        if (! $a->relationLoaded('specifications') || ! $b->relationLoaded('specifications')) {
            return [];
        }
        $public = fn (Product $product) => $product->specifications->filter(function ($item) use ($product) {
            $reference = $product->reference_metadata['specifications'][$item->specification_id] ?? null;

            return $item->specification && $reference && ($reference['public'] ?? true);
        })->keyBy('specification_id');
        $valuesA = $public($a);
        $valuesB = $public($b);
        $rows = [];
        foreach ($valuesA->keys()->merge($valuesB->keys())->unique() as $id) {
            $entryA = $valuesA->get($id);
            $entryB = $valuesB->get($id);
            $spec = ($entryA ?? $entryB)->specification;
            $verified = function ($entry, $product) use ($id) {
                $proof = $product->reference_metadata['specifications'][$id] ?? [];
                if (! $entry || ! array_key_exists('verified_value', $proof)) {
                    return ! $product->exists;
                }

                return is_numeric($entry->value) && is_numeric($proof['verified_value'])
                    ? abs((float) $entry->value - (float) $proof['verified_value']) < 0.000001
                    : trim((string) $entry->value) === trim((string) $proof['verified_value']);
            };
            $format = fn ($entry, $product) => ! $entry ? 'Belum tersedia' : (! $verified($entry, $product) ? 'Belum diverifikasi ulang' : ($spec->data_type === 'boolean'
                ? ($entry->value == '1' ? 'Didukung' : 'Tidak didukung')
                : $entry->value.($spec->unit ? ' '.$spec->unit : '')));
            $advantage = null;
            // Only directly comparable quantities establish an advantage. Missing facts never mean absence.
            if ($entryA && $entryB && $verified($entryA, $a) && $verified($entryB, $b) && is_numeric($entryA->value) && is_numeric($entryB->value)
                && in_array($spec->name, ['Jumlah port USB', 'Resolusi keluaran maksimum', 'RAM terverifikasi', 'Penyimpanan internal terverifikasi'], true)
                && (float) $entryA->value !== (float) $entryB->value) {
                $advantage = $entryA->value > $entryB->value ? 'a' : 'b';
            }
            $rows[] = ['name' => $spec->name, 'a' => $format($entryA, $a), 'b' => $format($entryB, $b), 'advantage' => $advantage];
        }

        return $rows;
    }
}
