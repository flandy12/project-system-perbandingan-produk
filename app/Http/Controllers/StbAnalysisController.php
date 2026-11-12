<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPriceObservation;
use App\Models\ProductSalesStat;
use App\Models\StbAnalysisRun;
use App\Services\PublishedStbRecommendations;
use App\Services\StbAnalysisDataset;
use App\Services\StbKMeans;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StbAnalysisController extends Controller
{
    private function filters(Request $request): array
    {
        $data = $request->validate(['year' => 'nullable|integer|between:2000,'.now()->year,
            'month' => 'nullable|integer|between:1,12', 'type' => ['nullable', Rule::in(array_keys(config('stb_analysis.types')))],
            'source' => 'nullable|string|max:255', 'k' => 'nullable|integer|between:2,6']);
        $last = CarbonImmutable::now()->subMonthNoOverflow();
        $year = (int) ($data['year'] ?? $last->year);
        $month = (int) ($data['month'] ?? $last->month);
        if (CarbonImmutable::create($year, $month, 1)->endOfMonth()->gte(now()->startOfDay())) {
            throw ValidationException::withMessages(['month' => 'Gunakan bulan yang sudah selesai agar cakupan penjualan bulanan konsisten.']);
        }

        return ['year' => $year, 'month' => $month, 'type' => $data['type'] ?? 'terrestrial', 'source' => $data['source'] ?? '', 'k' => isset($data['k']) ? (int) $data['k'] : null];
    }

    public function index(Request $request, StbAnalysisDataset $builder)
    {
        abort_unless($request->user()->can('view.stb.analysis'), 403);
        $filters = $this->filters($request);
        $dataset = $builder->build($filters['year'], $filters['month'], $filters['type'], $filters['source']);
        $sources = ProductSalesStat::verifiedReal()->where('year', $filters['year'])->where('month', $filters['month'])->distinct()->orderBy('source')->pluck('source');
        $products = Product::where('status', 'active')->orderBy('title')->get();
        $observations = ProductPriceObservation::latest('observed_on')->with('product')->limit(25)->get();
        $runs = StbAnalysisRun::latest()->limit(20)->get();

        return view('pages.stb-analysis.index', compact('filters', 'dataset', 'sources', 'products', 'observations', 'runs'));
    }

    public function price(Request $request)
    {
        abort_unless($request->user()->can('create.stb.analysis'), 403);
        $data = $request->validate(['product_id' => ['required', Rule::exists('products', 'id')->where('status', 'active')],
            'price' => 'required|numeric|min:0.01|max:999999999999', 'observed_on' => 'required|date_format:Y-m-d|after_or_equal:2000-01-01|before_or_equal:today',
            'source' => 'required|string|max:255', 'source_reference' => 'required|string|max:2000', 'confirmed' => 'accepted']);
        unset($data['confirmed']);
        ProductPriceObservation::create($data + ['user_id' => $request->user()->id]);

        return back()->with('status', 'Bukti harga disimpan. Harga katalog tidak diubah.');
    }

    public function store(Request $request, StbAnalysisDataset $builder, StbKMeans $engine)
    {
        abort_unless($request->user()->can('create.stb.analysis'), 403);
        $filters = $this->filters($request);
        if (! $filters['source']) {
            throw ValidationException::withMessages(['source' => 'Pilih satu sumber laporan penjualan terverifikasi.']);
        }
        $snapshot = $builder->build($filters['year'], $filters['month'], $filters['type'], $filters['source']);
        $result = null;
        $status = 'insufficient';
        try {
            $result = $engine->analyze(array_column($snapshot['rows'], 'values'), $filters['k']);
            $status = 'ready';
        } catch (\InvalidArgumentException $exception) {
            $snapshot['blocking_reason'] = $exception->getMessage();
        }
        $snapshot['requested_k'] = $filters['k'];
        $snapshot['fingerprint'] = PublishedStbRecommendations::fingerprint($snapshot);
        $run = StbAnalysisRun::create(['user_id' => $request->user()->id, 'year' => $filters['year'], 'month' => $filters['month'],
            'device_type' => $filters['type'], 'sales_source' => $filters['source'], 'status' => $status, 'snapshot' => $snapshot, 'result' => $result]);

        return redirect()->route('stb-analysis.show', $run);
    }

    public function simulate(Request $request, \App\Services\StbSimulationDataset $builder, StbKMeans $engine)
    {
        abort_unless($request->user()->can('create.stb.analysis'), 403);
        $filters = $this->filters($request);
        $data = $request->validate(['scenario' => ['required', Rule::in(array_keys(config('stb_simulation.scenarios')))]]);
        $snapshot = $builder->build($filters['year'], $filters['month'], $filters['type'], $data['scenario']);
        $result = null;
        $status = 'insufficient';
        try {
            $result = $engine->analyze(array_column($snapshot['rows'], 'values'), $filters['k']);
            $status = 'ready';
        } catch (\InvalidArgumentException $exception) {
            $snapshot['blocking_reason'] = $exception->getMessage();
        }
        $snapshot['requested_k'] = $filters['k'];
        $snapshot['fingerprint'] = PublishedStbRecommendations::fingerprint($snapshot);
        $run = StbAnalysisRun::create(['user_id' => $request->user()->id, 'year' => $filters['year'], 'month' => $filters['month'],
            'device_type' => $filters['type'], 'sales_source' => $snapshot['sales_source'], 'status' => $status,
            'snapshot' => $snapshot, 'result' => $result, 'is_simulated' => true]);

        return redirect()->route('stb-analysis.show', $run);
    }

    public function export(Request $request, StbAnalysisRun $run)
    {
        abort_unless($request->user()->can('view.stb.analysis'), 403);
        $data = ['analysis_id' => $run->id, 'is_simulated' => $run->is_simulated, 'status' => $run->status,
            'created_at' => $run->created_at->toIso8601String(), 'snapshot' => $run->snapshot, 'result' => $run->result];

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }, 'STB-'.($run->is_simulated ? 'SIMULASI-' : 'ANALISIS-').$run->id.'.json', ['Content-Type' => 'application/json']);
    }

    public function show(Request $request, StbAnalysisRun $run)
    {
        abort_unless($request->user()->can('view.stb.analysis'), 403);
        $current = app(PublishedStbRecommendations::class)->valid($run);
        $charts = app(\App\Services\StbAnalysisCharts::class)->build($run);

        return view('pages.stb-analysis.show', compact('run', 'current', 'charts'));
    }

    public function publish(Request $request, StbAnalysisRun $run, PublishedStbRecommendations $service)
    {
        abort_unless($request->user()->can('publish.stb.analysis'), 403);
        if ($run->is_simulated || ($run->snapshot['is_simulated'] ?? false)) {
            throw ValidationException::withMessages(['run' => 'Hasil simulasi tidak boleh diterbitkan sebagai rekomendasi nyata.']);
        }
        if (! $service->valid($run)) {
            throw ValidationException::withMessages(['run' => 'Dataset belum siap atau berubah. Jalankan analisis baru sebelum publikasi.']);
        }
        if ($run->result['silhouette'] <= 0) {
            throw ValidationException::withMessages(['run' => 'Pemisahan kelompok lemah. Periksa dataset sebelum menerbitkan rekomendasi.']);
        }
        // Keep one current publication per device type.
        \Illuminate\Support\Facades\DB::transaction(function () use ($run, $request) {
            Product::where('comparison_type', $run->device_type)->lockForUpdate()->get();
            StbAnalysisRun::where('device_type', $run->device_type)->whereNotNull('published_at')->update(['published_at' => null, 'published_by' => null]);
            $run->update(['published_at' => now(), 'published_by' => $request->user()->id]);
        });

        return back()->with('status', 'Hasil diterbitkan untuk rekomendasi pelanggan.');
    }

    public function unpublish(Request $request, StbAnalysisRun $run)
    {
        abort_unless($request->user()->can('publish.stb.analysis'), 403);
        $run->update(['published_at' => null, 'published_by' => null]);

        return back()->with('status', 'Publikasi rekomendasi dihentikan. Riwayat analisis tetap tersimpan.');
    }

    public function publicIndex(PublishedStbRecommendations $service)
    {
        $runs = $service->runs();

        return view('pages.stb-analysis.public', compact('runs'));
    }
}
