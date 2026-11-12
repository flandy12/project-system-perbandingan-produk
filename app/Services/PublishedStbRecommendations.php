<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StbAnalysisRun;

class PublishedStbRecommendations
{
    public static function fingerprint(array $snapshot): string
    {
        return hash('sha256', json_encode($snapshot['rows']));
    }

    public function valid(StbAnalysisRun $run): bool
    {
        if ($run->is_simulated || ($run->snapshot['is_simulated'] ?? false) || $run->status !== 'ready' || ! $run->result || ! $run->result['converged']) {
            return false;
        }
        $current = app(StbAnalysisDataset::class)->build($run->year, $run->month, $run->device_type, $run->sales_source);

        return self::fingerprint($current) === self::fingerprint($run->snapshot);
    }

    public function runs(): \Illuminate\Support\Collection
    {
        // Never silently fall back to an older publication when the latest snapshot is stale.
        return StbAnalysisRun::where('is_simulated', false)->whereNotNull('published_at')->orderByDesc('published_at')->orderByDesc('id')->get()
            ->unique('device_type')->filter(fn ($run) => $this->valid($run))->values();
    }

    public function products(StbAnalysisRun $run): \Illuminate\Support\Collection
    {
        if ($run->is_simulated || ($run->snapshot['is_simulated'] ?? false)) {
            return collect();
        }
        $products = Product::whereIn('id', array_column($run->snapshot['rows'], 'product_id'))->where('status', 'active')->get()->keyBy('id');
        $selected = collect();
        foreach ($run->result['centroids_raw'] as $cluster => $center) {
            $members = collect($run->snapshot['rows'])->filter(fn ($row, $i) => $run->result['assignments'][$i] === $cluster)
                ->sort(fn ($a, $b) => ($b['values'][1] <=> $a['values'][1]) ?: ($b['values'][2] <=> $a['values'][2]) ?: ($a['values'][0] <=> $b['values'][0]) ?: ($a['product_id'] <=> $b['product_id']));
            foreach ($members->take(2) as $row) {
                if (! $product = $products->get($row['product_id'])) {
                    continue;
                }
                $product->setAttribute('recommendation_reason', 'Kelompok '.($cluster + 1).' · fitur '.number_format($row['values'][1], 1, ',', '.').'/100 · '.number_format($row['values'][2], 0, ',', '.').' unit pada '.sprintf('%02d/%d', $run->month, $run->year).' · harga observasi Rp '.number_format($row['values'][0], 0, ',', '.'));
                $selected->push($product);
            }
        }

        return $selected;
    }
}
