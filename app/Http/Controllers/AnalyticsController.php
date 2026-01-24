<?php

namespace App\Http\Controllers;

use App\Models\ProductClick;
use App\Models\WebsiteVisit;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        // KPI
        $totalVisits = WebsiteVisit::count();
        $uniqueVisitors = WebsiteVisit::distinct('ip_address')->count('ip_address');
        $totalClicks = ProductClick::count();

        // Line chart: visit per day (30 hari)
        $dailyVisits = WebsiteVisit::select(
                DB::raw('DATE(visited_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->where('visited_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Bar chart: top product clicks
        $topProducts = ProductClick::select(
                'products.title',
                DB::raw('COUNT(product_clicks.id) as total')
            )
            ->join('products', 'products.id', '=', 'product_clicks.product_id')
            ->groupBy('products.title')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'totalVisits',
            'uniqueVisitors',
            'totalClicks',
            'dailyVisits',
            'topProducts'
        ));
    }
}
