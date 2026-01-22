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
        $headlines = HeadlineSlide::where('is_active', true)->orderBy('position')->get();
        $discounts = Discount::all();
        /**
         * TOP PENJUALAN
         * Berdasarkan total_sold
         */
        $topSales = Product::with('salesStats')
            ->whereHas('salesStats')
            ->orderByDesc(
                ProductSalesStat::select('total_sold')
                    ->whereColumn('product_sales_stats.product_id', 'products.id')
            )
            ->limit(8)
            ->get();

        /**
         *  REKOMENDASI PRODUK
         * Berdasarkan final_score
         */
        $recommendedProducts = Product::with('finalScore')
            ->whereHas('finalScore')
            ->orderByDesc(
                ProductFinalScore::select('final_score')
                    ->whereColumn('product_final_scores.product_id', 'products.id')
            )
            ->limit(8)
            ->get();
    
        return view('pages.home.index', compact(
            'headlines',
            'discounts',
            'topSales',
            'recommendedProducts'
        ));
    }
}
