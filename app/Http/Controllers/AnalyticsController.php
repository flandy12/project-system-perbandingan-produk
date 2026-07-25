<?php

namespace App\Http\Controllers;

use App\Models\ProductClick;
use App\Models\WebsiteVisit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    public function index()
    {
        $totalVisits = Cache::remember(
            'dashboard.total_visits',
            now()->addMinutes(5),
            fn() => WebsiteVisit::count()
        );

        $uniqueVisitors = Cache::remember(
            'dashboard.unique_visitors',
            now()->addMinutes(5),
            fn() => WebsiteVisit::distinct('ip_address')->count('ip_address')
        );

        $totalClicks = Cache::remember(
            'dashboard.total_clicks',
            now()->addMinutes(5),
            fn() => ProductClick::count()
        );

        $dailyVisits = Cache::remember(
            'dashboard.daily_visits',
            now()->addMinutes(5),
            fn() => WebsiteVisit::select(
                DB::raw('DATE(visited_at) as date'),
                DB::raw('COUNT(*) as total')
            )
                ->where('visited_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
        );

        $topProducts = Cache::remember(
            'dashboard.top_products',
            now()->addMinutes(5),
            fn() => ProductClick::select(
                'products.id',
                'products.title',
                DB::raw('COUNT(product_clicks.id) as total')
            )
                ->join('products', 'products.id', '=', 'product_clicks.product_id')
                ->groupBy('products.id', 'products.title')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
        );

        return view('dashboard', compact(
            'totalVisits',
            'uniqueVisitors',
            'totalClicks',
            'dailyVisits',
            'topProducts'
        ));
    }
}
