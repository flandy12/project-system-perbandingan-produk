<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductClick;
use App\Models\StbAnalysisRun;
use App\Models\WebsiteVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['days' => 'nullable|integer|in:7,30,90']);
        $days = (int) ($data['days'] ?? 30);
        $start = now()->startOfDay()->subDays($days - 1);
        $end = now();

        // Use one consistent window for cards, timeline and popular products.
        $visits = WebsiteVisit::whereBetween('visited_at', [$start, $end]);
        $totalVisits = (clone $visits)->count();
        $uniqueVisitors = (clone $visits)->distinct('ip_address')->count('ip_address');
        $totalClicks = ProductClick::whereBetween('clicked_at', [$start, $end])->count();
        $recorded = (clone $visits)->select(DB::raw('DATE(visited_at) as date'), DB::raw('COUNT(*) as total'))
            ->groupBy('date')->orderBy('date')->pluck('total', 'date');
        $dailyVisits = collect(range(0, $days - 1))->map(fn ($offset) => [
            'date' => $start->copy()->addDays($offset)->format('d/m'),
            'total' => (int) ($recorded[$start->copy()->addDays($offset)->toDateString()] ?? 0),
        ]);
        $topProducts = ProductClick::select('products.id', 'products.title', DB::raw('COUNT(product_clicks.id) as total'))
            ->join('products', 'products.id', '=', 'product_clicks.product_id')->whereBetween('product_clicks.clicked_at', [$start, $end])
            ->groupBy('products.id', 'products.title')->orderByDesc('total')->limit(5)->get();
        $catalogue = ['total' => Product::count(), 'active' => Product::where('status', 'active')->count(),
            'missing_price' => Product::whereNull('price')->count(), 'missing_link' => Product::whereNull('official_url')->orWhere('official_url', '')->count()];
        $runs = $request->user()->can('view.stb.analysis') ? StbAnalysisRun::latest()->limit(4)->get() : collect();

        return view('dashboard', compact(
            'days', 'start', 'end', 'catalogue', 'runs',
            'totalVisits',
            'uniqueVisitors',
            'totalClicks',
            'dailyVisits',
            'topProducts'
        ));
    }
}
