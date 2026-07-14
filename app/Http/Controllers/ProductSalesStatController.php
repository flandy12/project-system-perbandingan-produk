<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSalesStat;
use Illuminate\Http\Request;

class ProductSalesStatController extends Controller
{
    /**
     * Display listing
     */
    public function index()
    {
        $stats = ProductSalesStat::with('product')
            ->latest()
            ->paginate(10);

        $products = Product::where('status', 'active')
            ->orderBy('title')
            ->get();

        return view('product-sales-stats.index', compact(
            'stats',
            'products'
        ));
    }

    /**
     * Store
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'exists:products,id'
            ],

            'month' => [
                'required',
                'integer',
                'between:1,12'
            ],

            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:' . now()->year
            ],

            'total_sold' => [
                'required',
                'integer',
                'min:0'
            ],
        ]);

        ProductSalesStat::updateOrCreate(
            [
                'product_id' => $validated['product_id'],
                'month'      => $validated['month'],
                'year'       => $validated['year'],
            ],
            [
                'total_sold' => $validated['total_sold'],
            ]
        );

        return back()->with(
            'success',
            'Data penjualan berhasil disimpan.'
        );
    }

    /**
     * Update
     */
    public function update(Request $request, ProductSalesStat $productSalesStat)
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'exists:products,id'
            ],

            'month' => [
                'required',
                'integer',
                'between:1,12'
            ],

            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:' . now()->year
            ],

            'total_sold' => [
                'required',
                'integer',
                'min:0'
            ],
        ]);

        $productSalesStat->update($validated);

        return back()->with(
            'success',
            'Data berhasil diperbarui.'
        );
    }

    /**
     * Delete
     */
    public function destroy(ProductSalesStat $productSalesStat)
    {
        $productSalesStat->delete();

        return back()->with(
            'success',
            'Data berhasil dihapus.'
        );
    }

    /**
     * Increment penjualan (dipanggil saat checkout)
     */
    public function increment(Product $product, int $qty = 1): void
    {
        $stat = ProductSalesStat::firstOrCreate(
            [
                'product_id' => $product->id,
                'month'      => now()->month,
                'year'       => now()->year,
            ],
            [
                'total_sold' => 0,
            ]
        );

        $stat->increment('total_sold', $qty);
    }
}
