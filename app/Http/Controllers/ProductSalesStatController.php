<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSalesStat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductSalesStatController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year'  => 'nullable|integer|min:2000|max:' . now()->year,
        ]);

        $month = $validated['month'] ?? now()->month;
        $year  = $validated['year'] ?? now()->year;

        $sales = Product::select(
            'products.id',
            'products.name',
            'products.price',
            'products.thumbnail',
            'product_sales_stats.total_sold'
        )
            ->join('product_sales_stats', 'product_sales_stats.product_id', '=', 'products.id')
            ->where('product_sales_stats.month', $month)
            ->where('product_sales_stats.year', $year)
            ->where('products.status', 'active')
            ->orderByDesc('product_sales_stats.total_sold')
            ->paginate(10)
            ->withQueryString();

        // Ambil daftar tahun unik dari DB untuk dropdown
        $availableYears = ProductSalesStat::select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('pages.top.index', compact(
            'sales',
            'month',
            'year',
            'availableYears'
        ));
    }

    /**
     * Increment penjualan produk (dipanggil saat checkout sukses)
     */
    public function increment(Product $product, int $qty = 1): void
    {
        $now = Carbon::now();

        DB::transaction(function () use ($product, $now, $qty) {

            ProductSalesStat::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'month'      => $now->month,
                    'year'       => $now->year,
                ],
                []
            );

            ProductSalesStat::where([
                'product_id' => $product->id,
                'month'      => $now->month,
                'year'       => $now->year,
            ])->increment('total_sold', $qty);
        });
    }

    /**
     * Ambil top penjualan bulan tertentu
     */
    public function topByMonth(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2000|max:' . now()->year,
            'limit' => 'nullable|integer|min:1|max:50'
        ]);

        $limit = $validated['limit'] ?? 8;

        $products = Product::select('products.*')
            ->join('product_sales_stats', 'product_sales_stats.product_id', '=', 'products.id')
            ->where('product_sales_stats.month', $validated['month'])
            ->where('product_sales_stats.year', $validated['year'])
            ->where('products.status', 'active')
            ->orderByDesc('product_sales_stats.total_sold')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Ambil top penjualan sepanjang tahun
     */
    public function topByYear(Request $request)
    {
        $validated = $request->validate([
            'year'  => 'required|integer|min:2000|max:' . now()->year,
            'limit' => 'nullable|integer|min:1|max:50'
        ]);

        $limit = $validated['limit'] ?? 8;

        $products = Product::select('products.*')
            ->join('product_sales_stats', 'product_sales_stats.product_id', '=', 'products.id')
            ->where('product_sales_stats.year', $validated['year'])
            ->where('products.status', 'active')
            ->selectRaw('products.*, SUM(product_sales_stats.total_sold) as total_year_sold')
            ->groupBy('products.id')
            ->orderByDesc('total_year_sold')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}
