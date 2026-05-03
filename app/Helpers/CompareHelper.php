<?php

namespace App\Helpers;

class CompareHelper
{

    public static function calculateScore($product, $context)
    {
        // NORMALISASI
        $priceScore = 1 - self::normalize($product->price, $context['min_price'], $context['max_price']);
        $ratingScore = self::normalize($product->rating, 0, 5);
        $soldScore = self::normalize($product->sold, 0, $context['max_sold']);

        // BOBOT
        return ($priceScore * 0.4) +
            ($ratingScore * 0.4) +
            ($soldScore * 0.2);
    }

    public static function normalize($v, $min, $max)
    {
        return ($max - $min) == 0 ? 0 : ($v - $min) / ($max - $min);
    }

    public static function scoreByCategory($productA, $productB)
    {
        // kumpulkan semua spec yang sama
        $mapA = $productA->specs->keyBy('spec_key_id');
        $mapB = $productB->specs->keyBy('spec_key_id');

        $scoresA = [];
        $scoresB = [];

        $allKeys = collect($mapA->keys())->merge($mapB->keys())->unique();

        foreach ($allKeys as $keyId) {
            $a = $mapA[$keyId]->value ?? null;
            $b = $mapB[$keyId]->value ?? null;

            if ($a === null || $b === null) continue;

            $key = $mapA[$keyId]->key ?? $mapB[$keyId]->key;
            $cat = $key->category->name;
            $higherBetter = $key->is_higher_better;

            $min = min($a, $b);
            $max = max($a, $b);

            $na = self::normalize($a, $min, $max);
            $nb = self::normalize($b, $min, $max);

            // jika lower better (misal weight), dibalik
            if (!$higherBetter) {
                $na = 1 - $na;
                $nb = 1 - $nb;
            }

            $scoresA[$cat][] = $na;
            $scoresB[$cat][] = $nb;
        }

        // rata-rata per kategori (0–100)
        $finalA = [];
        $finalB = [];

        foreach (['kualitas', 'daya', 'fitur', 'konektivitas'] as $cat) {
            $finalA[$cat] = isset($scoresA[$cat]) ? array_sum($scoresA[$cat]) / count($scoresA[$cat]) * 100 : 0;
            $finalB[$cat] = isset($scoresB[$cat]) ? array_sum($scoresB[$cat]) / count($scoresB[$cat]) * 100 : 0;
        }

        return [$finalA, $finalB];
    }
}
