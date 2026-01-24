<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\HeadlineSlide;
use App\Models\Product;
use App\Models\ProductFinalScore;
use App\Models\ProductSalesStat;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $headlines = HeadlineSlide::where('is_active', true)
            ->orderBy('position')
            ->get();

        $discounts = Discount::all();

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

    public function  gallery()
    {
        $products = Product::all();

        return view('pages/home/gallery', compact('products'));
    }
}
