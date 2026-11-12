<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPriceObservation;
use App\Models\ProductSalesStat;
use Carbon\CarbonImmutable;

class StbAnalysisDataset
{
    public function build(int $year, int $month, string $type, string $source): array
    {
        $start = CarbonImmutable::create($year, $month, 1)->startOfDay();
        $end = $start->endOfMonth();
        $products = Product::where('status', 'active')->where('comparison_type', $type)
            ->with('specifications.specification')->orderBy('id')->get();
        $sales = ProductSalesStat::verifiedReal()->where('year', $year)->where('month', $month)
            ->where('source', $source)->get()->keyBy('product_id');
        $prices = ProductPriceObservation::whereDate('observed_on', '>=', $start->toDateString())->whereDate('observed_on', '<=', $end->toDateString())
            ->orderByDesc('observed_on')->orderByDesc('id')->get()->unique('product_id')->keyBy('product_id');
        $rows = [];
        $excluded = [];
        foreach ($products as $product) {
            $feature = app(ProductFeatureIndex::class)->evaluate($product);
            $price = $prices->get($product->id);
            $sale = $sales->get($product->id);
            $reasons = [];
            if (! $price || (float) $price->price <= 0 || ! trim($price->source) || ! trim($price->source_reference)) {
                $reasons[] = 'Belum ada bukti harga bertanggal pada periode ini.';
            }
            if (! $feature['ready']) {
                $reasons[] = 'Indeks fitur belum lengkap: '.implode(' ', $feature['missing']);
            }
            if (! $sale) {
                $reasons[] = 'Penjualan nyata terverifikasi dari sumber laporan yang dipilih belum tersedia.';
            }
            if ($reasons) {
                $excluded[] = ['product_id' => $product->id, 'code' => $product->product_code, 'title' => $product->title, 'reasons' => $reasons];

                continue;
            }
            $rows[] = ['product_id' => $product->id, 'code' => $product->product_code, 'title' => $product->title,
                'values' => [(float) $price->price, $feature['score'], (float) $sale->total_sold],
                'price' => ['id' => $price->id, 'observed_on' => $price->observed_on->toDateString(), 'source' => $price->source, 'reference' => $price->source_reference],
                'sales' => ['id' => $sale->id, 'source' => $sale->source, 'reference' => $sale->source_reference], 'features' => $feature];
        }

        return ['version' => 'php-stb-minmax-lloyd-v1', 'captured_at' => now()->toIso8601String(),
            'year' => $year, 'month' => $month, 'type' => $type, 'sales_source' => $source,
            'columns' => ['Harga tercatat (Rp)', 'Indeks fitur (0–100)', 'Penjualan bulanan (unit)'],
            'price_policy' => 'Harga observasi terakhir dalam bulan terpilih, tidak termasuk ongkir; bukan rata-rata harga transaksi. Diskon hanya masuk jika tercatat dalam observasi.',
            'feature_policy' => 'Kemampuan terdokumentasi saat analisis; tidak membuktikan kondisi firmware pada bulan historis.',
            'rows' => $rows, 'excluded' => $excluded, 'total' => $products->count()];
    }
}
