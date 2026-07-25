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
        $topSales = Product::query()
            ->where('status', 'active')
            ->withSum('salesStats', 'total_sold')
            ->orderByDesc('sales_stats_sum_total_sold')
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
            'ratings.user',
            'salesStat',
        ]);

        // Statistik Produk
        $averageRating = round($product->ratings->avg('rating') ?? 0, 1);
        $totalRatings  = $product->ratings->count();
        $totalSold = $product->salesStats()->sum('total_sold');
        $totalViews    = $product->clicks()->count();

        // Hanya komentar yang sudah approve
        $comments = $product->comments()
            ->where('status', 'approved')
            ->with('user')
            ->latest()
            ->paginate(10);

        // Distribusi Rating
        $ratingPercent = [];

        for ($i = 1; $i <= 5; $i++) {
            $count = $product->ratings
                ->where('rating', $i)
                ->count();

            $ratingPercent[$i] = $totalRatings > 0
                ? round(($count / $totalRatings) * 100)
                : 0;
        }

        // Mapping rating berdasarkan user
        $ratingMap = $product->ratings->keyBy('user_id');

        $reviews = $comments->through(function ($comment) use ($ratingMap) {
            return (object) [
                'user'       => $comment->user,
                'comment'    => $comment->comment,
                'status'     => $comment->status,
                'rating'     => optional($ratingMap->get($comment->user_id))->rating ?? 0,
                'created_at' => $comment->created_at,
            ];
        });

        return view('pages.home.show', compact(
            'product',
            'comments',
            'averageRating',
            'totalRatings',
            'totalSold',
            'totalViews',
            'ratingPercent',
            'months',
            'ratings',
            'reviews'
        ));
    }
}
