<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSpecificationScore;
use App\Models\Specification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductSpecificationScoreController extends Controller
{
    /**
     * List score spesifikasi produk
     */
    public function index(Request $request)
    {
        $query = ProductSpecificationScore::with(['product', 'specification']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $products = Product::all();
        $specifications = Specification::all();
        $productSpecificationScores = $query->paginate(10);

        return view('pages.product-specification-scores.index', compact('query', 'products', 'specifications', 'productSpecificationScores'));
    }

    /**
     * Simpan score spesifikasi produk
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'specification_id' => [
                'required',
                'exists:specifications,id',
                Rule::unique('product_specification_scores')
                    ->where('product_id', $request->product_id)
            ],
            'raw_value' => ['required', 'numeric'],
            'normalized_score' => ['required', 'numeric', 'between:0,100']
        ]);

        ProductSpecificationScore::create($validated);

        return redirect()
            ->route('product-specification-scores.index')
            ->with('success', 'Score spesifikasi produk berhasil ditambahkan');
    }

    /**
     * Detail score
     */
    public function show(ProductSpecificationScore $productSpecificationScore)
    {
        $productSpecificationScore->load(['product', 'specification']);

        return redirect()
            ->route('product-specification-scores.index')
            ->with('success', 'Score spesifikasi produk berhasil ditambahkan');
    }

    /**
     * Update score (hanya value & normalized_score)
     */
    public function update(Request $request, ProductSpecificationScore $productSpecificationScore)
    {
        $validated = $request->validate([
            'raw_value' => ['required', 'numeric'],
            'normalized_score' => ['required', 'numeric', 'between:0,100']
        ]);

        $productSpecificationScore->update($validated);

        return redirect()
            ->route('product-specification-scores.index')
            ->with('success', 'Score spesifikasi produk berhasil diupdate');
    }

    /**
     * Hapus score spesifikasi produk
     */
    public function destroy(ProductSpecificationScore $productSpecificationScore)
    {
        $productSpecificationScore->delete();

        return redirect()
            ->route('product-specification-scores.index')
            ->with('success', 'Score spesifikasi produk berhasil dihapus');
    }
}
