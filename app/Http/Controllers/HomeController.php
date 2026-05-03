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

    public function  gallery(Request $request)
    {

        $request->validate([
            'price_min' => 'nullable|integer|min:0',
            'price_max' => 'nullable|integer|min:0',
            'sort' => 'nullable|in:newest,oldest,best,worst',
            'date' => 'nullable|date',
        ]);

        $products = Product::query()

            // FILTER HARGA
            ->when($request->price_min, function ($q) use ($request) {
                $q->where('price', '>=', (int) $request->price_min);
            })
            ->when($request->price_max, function ($q) use ($request) {
                $q->where('price', '<=', (int) $request->price_max);
            })

            // FILTER TANGGAL
            ->when($request->date, function ($q) use ($request) {
                $q->whereDate('created_at', $request->date);
            })

            // SORTING
            ->when($request->sort, function ($q) use ($request) {
                switch ($request->sort) {
                    case 'newest':
                        $q->orderBy('created_at', 'desc');
                        break;
                    case 'oldest':
                        $q->orderBy('created_at', 'asc');
                        break;
                    case 'best':
                        $q->orderBy('rating', 'desc'); // pastikan ada field ini
                        break;
                    case 'worst':
                        $q->orderBy('rating', 'asc');
                        break;
                }
            })

            ->latest()
            ->paginate(12);

        return view('pages/home/gallery', compact('products'));
    }

    public function compare(Request $request, CompareService $service)
    {
        // Validasi & sanitasi ids
        $ids = collect(explode(',', (string)$request->ids))
            ->filter(fn($id) => is_numeric($id))
            ->take(2)
            ->values()
            ->toArray();

        if (count($ids) !== 2) {
            abort(400, 'Harus 2 produk valid');
        }

        // Ambil produk + eager load specs
        $products = Product::whereIn('id', $ids)
            ->with(['specifications.specification.category'])
            ->get()
            ->keyBy('id');

        if ($products->count() !== 2) {
            abort(404, 'Produk tidak ditemukan');
        }

        $productA = $products[$ids[0]];
        $productB = $products[$ids[1]];

        // Jalankan engine
        $result = $service->compare($productA, $productB);

        return view('pages/home/compare', [
            'productA' => $productA,
            'productB' => $productB,
            'percentA' => $result['percentA'],
            'percentB' => $result['percentB'],
            'metricsA' => $result['metricsA'],
            'metricsB' => $result['metricsB'],
            'reasons'  => $result['reasons'],
        ]);
    }
}
