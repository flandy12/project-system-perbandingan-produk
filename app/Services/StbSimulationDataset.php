<?php

namespace App\Services;

use App\Models\Product;
use Carbon\CarbonImmutable;

/** Builds isolated snapshots; never writes prices, sales, specifications, or catalogue products. */
class StbSimulationDataset
{
    public function build(int $year, int $month, string $type, string $scenario = 'balanced'): array
    {
        if (! array_key_exists($scenario, config('stb_simulation.scenarios')) || ! isset(config('stb_analysis.types')[$type])) {
            throw new \InvalidArgumentException('Skenario atau jenis perangkat tidak tersedia.');
        }
        $products = Product::where('status', 'active')->where('comparison_type', $type)
            ->with('specifications.specification')->orderBy('id')->get();
        $median = $products->filter(fn ($p) => $p->price !== null && (float) $p->price > 0)->median('price')
            ?? config('stb_simulation.fallback_prices.'.$type);
        $rows = [];
        foreach ($products as $product) {
            $features = app(ProductFeatureIndex::class)->evaluate($product);
            $assumptions = [];
            $weighted = 0;
            $weights = 0;
            foreach ($features['criteria'] as &$criterion) {
                $criterion['is_simulated'] = $criterion['reason'] !== null;
                if ($criterion['is_simulated']) {
                    $value = config('stb_simulation.assumed_features.'.$criterion['key']);
                    $assumptions[] = $criterion['name'].': nilai uji '.$value.' '.($criterion['unit'] ?? '').'; bukan spesifikasi terverifikasi.';
                    $criterion['value'] = (float) $value;
                    $criterion['score'] = min(100, (float) $value / $criterion['reference'] * 100);
                    $criterion['sources'] = [['type' => 'simulation', 'note' => 'Asumsi skenario '.config('stb_simulation.version')]];
                    $criterion['reason'] = null;
                }
                $weighted += $criterion['score'] * $criterion['weight'];
                $weights += $criterion['weight'];
            }
            unset($criterion);
            $features['score'] = $weights > 0 ? round($weighted / $weights, 2) : null;
            $features['is_simulated'] = count($assumptions) > 0;
            $features['ready'] = $weights > 0;
            $price = $product->price !== null && (float) $product->price > 0 ? (float) $product->price : (float) $median;
            $priceReference = $product->reference_metadata['price_reference'] ?? [];
            $priceNote = $product->price !== null && (float) $product->price > 0
                ? 'Acuan harga katalog saat pembuatan skenario. Bukan harga historis periode simulasi.'
                : 'Harga uji menggunakan median harga katalog pada jenis perangkat yang sama; bukan harga produk terverifikasi.';
            $assumptions[] = $priceNote;
            // Stable per-code pseudo-random quantities: fixtures, not estimates of real demand.
            $hash = hexdec(substr(hash('sha256', config('stb_simulation.seed').'|'.$product->product_code), 0, 7));
            $base = ($hash % 6 === 0) ? 0 : 15 + ($hash % 106);
            $multiplier = match ($scenario) {
                'low' => 0.35, 'high' => 2.0, default => 1.0
            };
            $units = (int) round($base * $multiplier);
            $assumptions[] = 'Penjualan sintetis deterministik: dasar 0 atau 15–120 unit; pengali '.$multiplier.'. Bukan prediksi atau laporan toko.';
            $rows[] = ['product_id' => $product->id, 'code' => $product->product_code, 'title' => $product->title,
                'values' => [$price, $features['score'], (float) $units], 'is_simulated' => true, 'assumptions' => $assumptions,
                'price' => ['id' => null, 'observed_on' => CarbonImmutable::create($year, $month, 1)->endOfMonth()->toDateString(),
                    'source' => 'SIMULASI — acuan katalog / median', 'reference' => ($priceReference['url'] ?? 'Tidak ada sumber harga produk').'; '.$priceNote],
                'sales' => ['id' => null, 'source' => 'SIMULASI — '.config('stb_simulation.scenarios.'.$scenario), 'reference' => 'Seed '.config('stb_simulation.seed').'; formula skenario, tanpa laporan penjualan nyata'],
                'features' => $features];
        }

        return ['version' => 'php-stb-minmax-lloyd-v1', 'captured_at' => now()->toIso8601String(),
            'is_simulated' => true, 'scenario' => $scenario, 'scenario_version' => config('stb_simulation.version'), 'seed' => config('stb_simulation.seed'),
            'year' => $year, 'month' => $month, 'type' => $type, 'sales_source' => 'SIMULASI — '.config('stb_simulation.scenarios.'.$scenario),
            'columns' => ['Harga uji (Rp)', 'Indeks fitur uji (0–100)', 'Penjualan sintetis (unit)'],
            'price_policy' => 'Seluruh harga periode adalah nilai uji berbasis harga katalog saat ini atau median sejenis. Tanggal adalah tanggal skenario, bukan pengamatan historis.',
            'feature_policy' => 'Fitur bersumber dipertahankan dalam snapshot; fitur kosong diisi asumsi eksplisit untuk uji. Tidak mengubah spesifikasi asli.',
            'rows' => $rows, 'excluded' => [], 'total' => $products->count()];
    }
}
