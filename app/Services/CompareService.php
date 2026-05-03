<?php

namespace App\Services;

use App\Models\Product;

class CompareService
{
    /**
     * Entry point: hitung metrics (per kategori), percent, dan reasons.
     */
    public function compare(Product $a, Product $b): array
    {
        // Ambil metrics per kategori (0–100)
        [$metricsA, $metricsB] = $this->metricsByCategory($a, $b);

        // Score total = sum kategori (bisa ditambah weight nanti)
        $scoreA = array_sum($metricsA);
        $scoreB = array_sum($metricsB);

        $total = max($scoreA + $scoreB, 1);
        $percentA = ($scoreA / $total) * 100;
        $percentB = ($scoreB / $total) * 100;

        // Reasons otomatis dari data
        $reasons = $this->buildReasons($a, $b);

        return [
            'metricsA' => $metricsA,
            'metricsB' => $metricsB,
            'percentA' => $percentA,
            'percentB' => $percentB,
            'reasons'  => $reasons,
        ];
    }

    /**
     * Hitung metrics per kategori (kualitas, daya, fitur, konektivitas)
     * dengan normalisasi antar dua produk.
     */
    protected function metricsByCategory(Product $a, Product $b): array
    {
        // Map spec by key_id
        $mapA = $a->specs->keyBy('spec_key_id');
        $mapB = $b->specs->keyBy('spec_key_id');

        // Gabungkan semua key yang ada di A/B
        $allKeyIds = collect($mapA->keys())
            ->merge($mapB->keys())
            ->unique()
            ->values();

        // Kategori yang ingin ditampilkan di radar (konsisten dengan UI)
        $categories = ['kualitas', 'daya', 'fitur', 'konektivitas'];

        // Penampung skor per kategori (nilai 0..1)
        $bucketA = [];
        $bucketB = [];

        foreach ($allKeyIds as $keyId) {
            $specA = $mapA[$keyId] ?? null;
            $specB = $mapB[$keyId] ?? null;

            // Hanya bandingkan jika dua-duanya ada
            if (!$specA || !$specB) continue;

            $key = $specA->key ?? $specB->key; // relasi SpecKey
            if (!$key || !$key->category) continue;

            $catName = strtolower($key->category->name); // 'fitur', dll
            if (!in_array($catName, $categories)) continue;

            $aVal = (float) $specA->value;
            $bVal = (float) $specB->value;

            $min = min($aVal, $bVal);
            $max = max($aVal, $bVal);

            // Normalisasi 0..1
            $na = $this->normalize($aVal, $min, $max);
            $nb = $this->normalize($bVal, $min, $max);

            // Jika lower-better (misal weight), dibalik
            if (!$key->is_higher_better) {
                $na = 1 - $na;
                $nb = 1 - $nb;
            }

            $bucketA[$catName][] = $na;
            $bucketB[$catName][] = $nb;
        }

        // Final: rata-rata per kategori (0..100)
        $metricsA = [];
        $metricsB = [];

        foreach ($categories as $cat) {
            $metricsA[$cat] = isset($bucketA[$cat]) && count($bucketA[$cat]) > 0
                ? (array_sum($bucketA[$cat]) / count($bucketA[$cat])) * 100
                : 0;

            $metricsB[$cat] = isset($bucketB[$cat]) && count($bucketB[$cat]) > 0
                ? (array_sum($bucketB[$cat]) / count($bucketB[$cat])) * 100
                : 0;
        }

        return [$metricsA, $metricsB];
    }

    /**
     * Reasons otomatis berdasarkan tiap spec_key.
     */
    protected function buildReasons(Product $a, Product $b): array
    {
        $reasons = [];

        $mapA = $a->specs->keyBy('spec_key_id');
        $mapB = $b->specs->keyBy('spec_key_id');

        $allKeyIds = collect($mapA->keys())
            ->merge($mapB->keys())
            ->unique()
            ->values();

        foreach ($allKeyIds as $keyId) {
            $specA = $mapA[$keyId] ?? null;
            $specB = $mapB[$keyId] ?? null;

            if (!$specA || !$specB) continue;

            $key = $specA->key ?? $specB->key;
            if (!$key) continue;

            $name = ucfirst($key->name);
            $unit = $key->unit ?? '';

            $aVal = (float) $specA->value;
            $bVal = (float) $specB->value;

            $better = $key->is_higher_better;
            $bWin = $better ? ($bVal > $aVal) : ($bVal < $aVal);

            if ($bWin) {
                // Contoh: "Ram lebih baik (8GB vs 4GB)"
                $reasons[] = "{$name} lebih baik ({$bVal}{$unit} vs {$aVal}{$unit})";
            }
        }

        // fallback kalau tidak ada reason
        if (empty($reasons)) {
            $reasons[] = "Perbedaan tidak signifikan berdasarkan spesifikasi utama.";
        }

        return $reasons;
    }

    protected function normalize(float $v, float $min, float $max): float
    {
        if (($max - $min) == 0) return 0.0;
        return ($v - $min) / ($max - $min);
    }
}