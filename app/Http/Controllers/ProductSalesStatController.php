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
    public function index(Request $request)
    {
        abort_unless($request->user()->can('view.product.sales'), 403);
        $stats = ProductSalesStat::with('product')
            ->latest()
            ->paginate(10);

        $products = Product::where('status', 'active')
            ->orderBy('title')
            ->get();

        return view('pages.product-sales-stats.index', compact(
            'stats',
            'products'
        ));
    }

    /**
     * Store
     */
    public function store(Request $request)
    {
        abort_unless($request->user()->can('create.product.sales'), 403);
        $validated = $request->validate([
            'source' => 'required|string|max:255',
            'source_reference' => 'required|string|max:2000',
            'verification_status' => 'required|in:verified,unverified',
            'is_simulated' => 'required|boolean',
            'product_id' => [
                'required',
                'exists:products,id',
            ],

            'month' => [
                'required',
                'integer',
                'between:1,12',
            ],

            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:'.now()->year,
            ],

            'total_sold' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        $this->validatePeriod($validated);
        if (ProductSalesStat::where('product_id', $validated['product_id'])->where('year', $validated['year'])->where('month', $validated['month'])->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['product_id' => 'Produk dan periode sudah ada. Gunakan Edit.']);
        }
        ProductSalesStat::firstOrCreate(
            [
                'product_id' => $validated['product_id'],
                'month' => $validated['month'],
                'year' => $validated['year'],
            ],
            [
                'total_sold' => $validated['total_sold'],
                'source' => $validated['source'], 'source_reference' => $validated['source_reference'],
                'verification_status' => $validated['verification_status'], 'is_simulated' => $validated['is_simulated'],
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
        abort_unless($request->user()->can('edit.product.sales'), 403);
        $validated = $request->validate([
            'source' => 'required|string|max:255',
            'source_reference' => 'required|string|max:2000',
            'verification_status' => 'required|in:verified,unverified',
            'is_simulated' => 'required|boolean',
            'product_id' => [
                'required',
                'exists:products,id',
            ],

            'month' => [
                'required',
                'integer',
                'between:1,12',
            ],

            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:'.now()->year,
            ],

            'total_sold' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        $this->validatePeriod($validated);
        $conflict = ProductSalesStat::where('product_id', $validated['product_id'])->where('month', $validated['month'])->where('year', $validated['year'])->whereKeyNot($productSalesStat->id)->exists();
        if ($conflict) {
            throw \Illuminate\Validation\ValidationException::withMessages(['product_id' => 'Produk dan periode sudah memiliki catatan.']);
        }
        $validated['sales_import_batch_id'] = null;
        $productSalesStat->update($validated);

        return back()->with(
            'success',
            'Data berhasil diperbarui.'
        );
    }

    /**
     * Delete
     */
    public function destroy(Request $request, ProductSalesStat $productSalesStat)
    {
        abort_unless($request->user()->can('delete.product.sales'), 403);
        $productSalesStat->delete();

        return back()->with(
            'success',
            'Data berhasil dihapus.'
        );
    }

    private function validatePeriod(array $data): void
    {
        if (sprintf('%04d-%02d', $data['year'], $data['month']) > now()->format('Y-m')) {
            throw \Illuminate\Validation\ValidationException::withMessages(['month' => 'Periode tidak boleh berada di masa depan.']);
        }
    }

    /**
     * Increment penjualan (dipanggil saat checkout)
     */
    public function increment(Product $product, int $qty = 1): void
    {
        $stat = ProductSalesStat::firstOrCreate(
            [
                'product_id' => $product->id,
                'month' => now()->month,
                'year' => now()->year,
            ],
            [
                'total_sold' => 0,
            ]
        );

        $stat->update(['verification_status' => 'unverified', 'sales_import_batch_id' => null]);
        $stat->increment('total_sold', $qty);
    }
}
