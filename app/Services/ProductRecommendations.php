<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProductRecommendations
{
    public function get(): array
    {
        $service = app(PublishedStbRecommendations::class);
        $runs = $service->runs();
        if ($runs->isNotEmpty()) {
            return ['products' => $runs->flatMap(fn ($run) => $service->products($run))->take(8)->values(),
                'description' => 'Pilihan dari setiap kelompok K-Means yang diterbitkan. Dalam kelompok yang sama: indeks fitur tertinggi, lalu penjualan bulanan, lalu harga observasi terendah. Kelompok bukan peringkat mutu. Lihat periode, sumber, dan cakupan pada rincian analisis.'];
        }

        return $this->select(Product::where('status', 'active')->get());
    }

    public function select(Collection $products): array
    {
        $eligible = $products->filter(fn ($p) => ($p->sales_stats_sum_total_sold ?? 0) > 0 || ($p->ratings_count ?? 0) >= 3);
        if ($eligible->isNotEmpty()) {
            $selected = $eligible->sort(function ($a, $b) {
                $sales = ($b->sales_stats_sum_total_sold ?? 0) <=> ($a->sales_stats_sum_total_sold ?? 0);
                $ratingA = ($a->ratings_count ?? 0) >= 3 ? $a->ratings_avg_rating : -1;
                $ratingB = ($b->ratings_count ?? 0) >= 3 ? $b->ratings_avg_rating : -1;

                return $sales ?: ($ratingB <=> $ratingA) ?: (($b->ratings_count ?? 0) <=> ($a->ratings_count ?? 0)) ?: ($a->id <=> $b->id);
            })->take(8)->values();
            foreach ($selected as $product) {
                $sold = (int) ($product->sales_stats_sum_total_sold ?? 0);
                $product->setAttribute('recommendation_reason', $sold > 0 ? number_format($sold, 0, ',', '.').' unit terjual · data aplikasi' : 'Rating dari '.$product->ratings_count.' ulasan');
            }

            return ['products' => $selected, 'description' => 'Diurutkan berdasarkan total unit terjual dari seluruh periode statistik penjualan di aplikasi. Jika jumlahnya sama, rating dengan minimal 3 ulasan menjadi pembeda. Statistik ini dapat diinput admin; bukan angka penjualan resmi Matrix.'];
        }

        // A representative catalogue selection, with no unsupported quality ranking.
        $selected = $products->sortBy('id')->groupBy(fn ($p) => $p->comparison_type ?? 'other')
            ->flatMap(fn ($group) => $group->take(2))->take(8)->values();
        $types = ['terrestrial' => 'TV digital antena UHF', 'satellite' => 'TV satelit parabola', 'combo' => 'Antena UHF dan parabola', 'android' => 'Aplikasi dan streaming internet'];
        foreach ($selected as $product) {
            $product->setAttribute('recommendation_reason', $types[$product->comparison_type] ?? 'Pilihan katalog');
        }

        return ['products' => $selected, 'description' => 'Data penjualan dan ulasan belum cukup untuk menentukan peringkat. Berikut pilihan katalog dari beberapa jenis perangkat untuk membantu memilih sesuai kebutuhan.'];
    }
}
