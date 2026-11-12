<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SalesImportBatch;
use App\Services\SalesImport;
use Illuminate\Http\Request;

class SalesImportController extends Controller
{
    public function index(Request $request, \App\Services\ProductFeatureIndex $featureIndex)
    {
        $request->user()->can('view.product.sales') || abort(403);
        $filters = $request->validate(['year' => 'nullable|integer|between:2000,'.now()->year, 'month' => 'nullable|integer|between:1,12']);
        $year = (int) ($filters['year'] ?? now()->year);
        $year = $year ?: now()->year;
        $month = (int) ($filters['month'] ?? now()->month);
        abort_unless($month >= 1 && $month <= 12, 422);
        $products = Product::with(['specifications.specification', 'salesStats' => fn ($q) => $q->where('year', $year)->where('month', $month)])->where('status', 'active')->orderBy('title')->get();
        $featureIndices = $products->mapWithKeys(fn ($product) => [$product->id => $featureIndex->evaluate($product)]);
        $batches = SalesImportBatch::where('user_id', $request->user()->id)->latest()->limit(10)->get();

        return view('pages.sales-import.index', compact('products', 'batches', 'year', 'month', 'featureIndices'));
    }

    public function template(Request $request)
    {
        $request->user()->can('view.product.sales') || abort(403);

        return response()->download(base_path('outputs/stb-sales-20261006/Template-Penjualan-STB.xlsx'));
    }

    public function preview(Request $request, SalesImport $import)
    {
        $request->user()->can('create.product.sales') || abort(403);
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx', 'max:5120']]);
        $file = $request->file('file');
        $rows = $import->read($file->getRealPath());
        $batch = SalesImportBatch::create(['user_id' => $request->user()->id, 'filename' => $file->getClientOriginalName(), 'file_hash' => hash_file('sha256', $file->getRealPath()), 'rows' => $rows]);

        return redirect()->route('sales-import.show', $batch);
    }

    public function show(Request $request, SalesImportBatch $batch)
    {
        $request->user()->can('view.product.sales') || abort(403);
        abort_unless($batch->user_id === $request->user()->id, 403);

        return view('pages.sales-import.preview', compact('batch'));
    }

    public function apply(Request $request, SalesImportBatch $batch, SalesImport $import)
    {
        $request->user()->can('create.product.sales') || abort(403);
        abort_unless($batch->user_id === $request->user()->id, 403);
        $summary = $import->apply($batch);

        return redirect()->route('sales-import.show', $batch)->with('success', $summary['inserted'].' baris disimpan; '.$summary['skipped'].' duplikat dilewati.');
    }
}
