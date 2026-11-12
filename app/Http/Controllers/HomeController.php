<?php

namespace App\Http\Controllers;

use App\Models\HeadlineSlide;
use App\Models\Product;
use App\Services\CompareService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $headlines = HeadlineSlide::where('is_active', true)
            ->orderBy('position')
            ->get();

        $discounts = Product::where('status', 'active')->whereHas('discounts', fn ($query) => $query
            ->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today()))
            ->with('discounts')
            ->get();
        /**
         * TOP PENJUALAN
         */
        $salesPeriod = \App\Models\ProductSalesStat::verifiedReal()->select('year', 'month')->orderByDesc('year')->orderByDesc('month')->first();
        $topSales = $salesPeriod ? Product::query()
            ->where('status', 'active')
            ->whereHas('salesStats', fn ($q) => $q->verifiedReal()->where('year', $salesPeriod->year)->where('month', $salesPeriod->month)->where('total_sold', '>', 0))
            ->withSum(['salesStats' => fn ($q) => $q->verifiedReal()->where('year', $salesPeriod->year)->where('month', $salesPeriod->month)], 'total_sold')
            ->orderByDesc('sales_stats_sum_total_sold')
            ->limit(8)
            ->get() : collect();

        $recommendations = (new \App\Services\ProductRecommendations)->get();
        $recommendedProducts = $recommendations['products'];
        $recommendationDescription = $recommendations['description'];

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
            'salesPeriod',
            'recommendedProducts',
            'recommendationDescription',
            'products'
        ));
    }

    public function gallery(Request $request, \App\Services\CatalogueQuery $catalogue)
    {
        $filters = $request->validate([
            'price_min' => 'nullable|integer|min:0',
            'price_max' => 'nullable|integer|min:0',
            'sort' => 'nullable|in:newest,oldest,best,worst',
            'year' => ['nullable', 'regex:/^(unknown|19[0-9]{2}|20[0-9]{2})$/'],
        ]);
        if (isset($filters['price_min'], $filters['price_max']) && $filters['price_max'] < $filters['price_min']) {
            throw \Illuminate\Validation\ValidationException::withMessages(['price_max' => 'Harga maksimum harus lebih besar atau sama dengan harga minimum.']);
        }
        $products = $catalogue->build($filters)->paginate(12)->withQueryString();
        $productionYears = Product::where('status', 'active')->whereNotNull('production_year')
            ->distinct()->orderByDesc('production_year')->pluck('production_year');

        return view('pages.home.gallery', compact('products', 'productionYears'));
    }

    public function compare(Request $request, CompareService $service)
    {
        // Validasi & sanitasi ids
        $ids = collect(explode(',', (string) $request->ids))
            ->filter(fn ($id) => is_numeric($id))
            ->unique()
            ->take(2)
            ->values();

        // Validasi jumlah produk
        if ($ids->count() !== 2) {

            return view('pages.home.compare.error', [
                'products' => collect(),
                'error' => 'Pilih tepat 2 produk untuk dibandingkan',
            ]);
        }

        // Ambil produk + eager load
        $report = \App\Models\ProductSalesStat::verifiedReal()->whereIn('product_id', $ids)->orderByDesc('year')->orderByDesc('month')->first();
        $salesFilter = fn ($q) => $q->verifiedReal()->where('year', $report?->year ?? 0)->where('month', $report?->month ?? 0)->where('source', $report?->source ?? '');
        $products = Product::query()
            ->whereIn('id', $ids)
            ->where('status', 'active')
            ->with([
                'specificationScores.specification.group',
                'specifications.specification',
            ])
            ->withSum(['salesStats as sold' => $salesFilter], 'total_sold')
            ->withCount(['salesStats' => $salesFilter])
            ->withAvg(['ratings as rating' => fn ($q) => $q->real()], 'rating')
            ->get()
            ->keyBy('id');

        // Pastikan produk ditemukan
        if ($products->count() !== 2) {

            return view('pages.home.compare.error', [
                'products' => collect(),
                'error' => 'Produk tidak ditemukan',
            ]);
        }

        // Mapping product
        $productA = $products->get($ids[0]);
        $productB = $products->get($ids[1]);

        // Safety check
        if (! $productA || ! $productB) {

            return view('pages.home.compare.error', [
                'products' => collect(),
                'error' => 'Produk compare tidak valid',
            ]);
        }

        // Compare engine
        $groups = \App\Models\SpecificationGroup::orderBy('id')->pluck('name', 'id')->all();
        $result = $service->compare($productA, $productB, $groups);

        return view('pages.home.compare', [
            'salesPeriodLabel' => $report ? sprintf('%02d/%d', $report->month, $report->year).' · '.$report->source : 'Belum ada laporan terverifikasi',
            'productA' => $productA,
            'productB' => $productB,
            'percentA' => $result['percentA'],
            'percentB' => $result['percentB'],
            'metricsA' => $result['metricsA'] ?? [],
            'metricsB' => $result['metricsB'] ?? [],
            'reasons' => $result['reasons'],
            'labels' => $result['labels'],
            'hasData' => $result['hasData'],
            'hasDemoData' => $result['hasDemoData'],
            'winner' => $result['winner'],
            'comparisonStatus' => $result['comparisonStatus'],
            'specificationRows' => $result['specificationRows'],
            'scoreCriteria' => $result['scoreCriteria'],
            'missingGroups' => $result['missingGroups'],
            'comparedCount' => $result['comparedCount'],
        ]);
    }

    public function contact()
    {
        return view('pages.home.contact');
    }

    public function show(Product $product)
    {
        abort_unless($product->status === 'active', 404);
        $product->load([
            'category',
            'specifications.specification.group',
            'ratings' => fn ($q) => $q->real()->with('user'),
            'salesStat',
        ]);

        // Statistik Produk
        $averageRating = round($product->ratings->avg('rating') ?? 0, 1);
        $totalRatings = $product->ratings->count();
        $sale = $product->salesStats()->verifiedReal()->orderByDesc('year')->orderByDesc('month')->first();
        $totalSold = $sale?->total_sold;
        $salesPeriodLabel = $sale ? sprintf('%02d/%d', $sale->month, $sale->year) : 'Belum ada laporan terverifikasi';
        $totalViews = $product->clicks()->count();

        // Hanya komentar yang sudah approve
        $comments = $product->comments()
            ->where('status', 'approved')
            ->where('comment', 'not like', '[SIMULASI PENGUJIAN]%')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
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
                'user' => $comment->user,
                'comment' => $comment->comment,
                'status' => $comment->status,
                'rating' => optional($ratingMap->get($comment->user_id))->rating ?? 0,
                'created_at' => $comment->created_at,
            ];
        });

        return view('pages.home.show', compact(
            'product',
            'comments',
            'averageRating',
            'totalRatings',
            'totalSold',
            'salesPeriodLabel',
            'totalViews',
            'ratingPercent',
            'reviews'
        ));
    }
}
