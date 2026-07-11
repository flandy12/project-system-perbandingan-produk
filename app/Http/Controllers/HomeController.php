<?php

namespace App\Http\Controllers;

use App\Helpers\CompareHelper;
use App\Models\Discount;
use App\Models\HeadlineSlide;
use App\Models\Product;
use App\Models\ProductFinalScore;
use App\Models\ProductSalesStat;
use App\Services\CompareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $headlines = HeadlineSlide::where('is_active', true)
            ->orderBy('position')
            ->get();

        $discounts = Product::whereHas('discounts')
            ->with('discounts')
            ->get();
        /**
         * TOP PENJUALAN
         */
        $topSales = Product::select('products.*')
            ->join('product_sales_stats', 'product_sales_stats.product_id', '=', 'products.id')
            ->where('products.status', 'active')
            ->orderByDesc('product_sales_stats.total_sold')
            ->limit(8)
            ->get();

        /**
         * REKOMENDASI PRODUK
         * Rekomendasi produk ini berdasarkan nilai final_score yang ada di tabel:
         */
        $recommendedProducts = Product::select('products.*')
            ->join('product_final_scores', 'product_final_scores.product_id', '=', 'products.id')
            ->where('products.status', 'active')
            ->orderByDesc('product_final_scores.final_score')
            ->limit(8)
            ->get();

        /**
         * LIST PRODUK (DENGAN PAGINATION)
         */
        $products = Product::where('status', 'active')
            ->latest()
            ->paginate(12);

        return view('pages.home.index', compact(
            'headlines',
            'discounts',
            'topSales',
            'recommendedProducts',
            'products'
        ));
    }

    public function gallery(Request $request)
    {
        $request->validate([
            'price_min' => 'nullable|integer|min:0',
            'price_max' => 'nullable|integer|min:0',
            'sort' => 'nullable|in:newest,oldest,best,worst',
            'date' => 'nullable|date',
        ]);

        $products = Product::query()
        ->when($request->price_min, function ($q) use ($request) {
            $q->where('products.price', '>=', $request->price_min);
        })
        ->when($request->price_max, function ($q) use ($request) {
            $q->where('products.price', '<=', $request->price_max);
        })
        ->when($request->year, function ($q) use ($request) {
            $q->whereYear('products.created_at', $request->year);
        })
        ->when($request->sort, function ($q) use ($request) {

            switch ($request->sort) {

                case 'newest':
                    $q->orderBy('products.created_at', 'desc');
                    break;

                case 'oldest':
                    $q->orderBy('products.created_at', 'asc');
                    break;
            }

        }, function ($q) {
            $q->latest('products.created_at');
        })
        ->paginate(12);

        // $products = Product::query()->select(
        //     'products.*',
        //     DB::raw('COUNT(product_clicks.id) as total_clicks')
        // )->leftJoin(
        //     'product_clicks',
        //     'products.id',
        //     '=',
        //     'product_clicks.product_id'
        // )->when($request->price_min, function ($q) use ($request) {
        //     $q->where('products.price', '>=', $request->price_min);
        // })->when($request->price_max, function ($q) use ($request) {
        //     $q->where('products.price', '<=', $request->price_max);
        // }) // FILTER TANGGAL
        //     ->when($request->year, function ($q) use ($request) {
        //         $q->whereYear('products.created_at', $request->year);
        //     })

        //     // GROUP BY WAJIB
        //     ->groupBy('products.id')

        //     // SORTING
        //     ->when($request->sort, function ($q) use ($request) {

        //         switch ($request->sort) {

        //             case 'newest':
        //                 $q->orderBy('products.created_at', 'desc');
        //                 break;

        //             case 'oldest':
        //                 $q->orderBy('products.created_at', 'asc');
        //                 break;

        //             case 'best':
        //                 $q->orderByDesc('total_clicks');
        //                 break;

        //             case 'worst':
        //                 $q->orderBy('total_clicks', 'asc');
        //                 break;
        //         }
        //     }, function ($q) {

        //         $q->latest('products.created_at');
        //     })

        //     ->paginate(12);

        return view('pages.home.gallery', compact('products'));
    }

    public function compare(Request $request, CompareService $service)
    {
        // Validasi & sanitasi ids
        $ids = collect(explode(',', (string) $request->ids))
            ->filter(fn($id) => is_numeric($id))
            ->unique()
            ->take(2)
            ->values();

        // Validasi jumlah produk
        if ($ids->count() !== 2) {

            return view('pages.home.compare.error', [
                'products' => collect(),
                'error' => 'Pilih tepat 2 produk untuk dibandingkan'
            ]);
        }

        // Ambil produk + eager load
        $products = Product::query()
            ->whereIn('id', $ids)
            ->with([
                'specifications.specification.category'
            ])
            ->get()
            ->keyBy('id');

        // Pastikan produk ditemukan
        if ($products->count() !== 2) {

            return view('pages.home.compare.error', [
                'products' => collect(),
                'error' => 'Produk tidak ditemukan'
            ]);
        }

        // Mapping product
        $productA = $products->get($ids[0]);
        $productB = $products->get($ids[1]);

        // Safety check
        if (!$productA || !$productB) {

            return view('pages.home.compare.error', [
                'products' => collect(),
                'error' => 'Produk compare tidak valid'
            ]);
        }

        // Compare engine
        $result = $service->compare($productA, $productB);

        return view('pages.home.compare', [
            'productA' => $productA,
            'productB' => $productB,
            'percentA' => $result['percentA'] ?? 0,
            'percentB' => $result['percentB'] ?? 0,
            'metricsA' => $result['metricsA'] ?? [],
            'metricsB' => $result['metricsB'] ?? [],
            'reasons'  => $result['reasons'] ?? [],
        ]);
    }

    public function contact()
    {
        return view('pages.home.contact');
    }

    public function show(Product $product)
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei'];
        $ratings = [4.1, 4.2, 4.4, 4.6, 4.8];

        $product->load([
            'category',
            'ratings.user'
        ]);
        $totalRatings = $product->ratings()->count();

        $averageRating = round(
            $product->ratings()->avg('rating'),
            1
        );

        $ratingPercent = [];

        for ($i = 1; $i <= 5; $i++) {

            $count = $product->ratings()
                ->where('rating', $i)
                ->count();

            $ratingPercent[$i] = $totalRatings
                ? round(($count / $totalRatings) * 100)
                : 0;
        }
        return view('pages.home.show', [
            'product' => $product,
            'averageRating' => $averageRating,
            'totalRatings' => $totalRatings,
            'ratingPercent' => $ratingPercent,
            'months' => $months,
            'ratings' => $ratings
        ]);
    }
}
