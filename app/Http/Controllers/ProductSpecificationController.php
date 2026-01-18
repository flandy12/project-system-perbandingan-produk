<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\Specification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductSpecificationController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductSpecification::with(['product', 'specification']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $products = Product::all();
        $productSpecifications = $query->paginate(10);
        $specifications = Specification::all();

        return view('pages.product-specifications.index', compact('query', 'products', 'productSpecifications', 'specifications'));
    }

    /**
     * Menyimpan spesifikasi produk
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'specification_id' => [
                'required',
                'exists:specifications,id',
                Rule::unique('product_specifications')
                    ->where('product_id', $request->product_id)
            ],
            'value' => ['required', 'string', 'max:255']
        ]);

        $productSpecification = ProductSpecification::create($validated);


        return redirect()
            ->route('product-specifications.index')
            ->with('success', 'Specification berhasil ditambahkan');
    }

    /**
     * Menampilkan detail spesifikasi produk
     */
    public function show(ProductSpecification $productSpecification)
    {
        $productSpecification->load(['product', 'specification']);

        return redirect()
            ->route('product-specifications.index')
            ->with('success', 'Specification berhasil ditambahkan');
    }

    /**
     * Update value spesifikasi produk
     */
    public function update(Request $request, ProductSpecification $productSpecification)
    {
        $validated = $request->validate([
            'value' => ['required', 'string', 'max:255']
        ]);

        $productSpecification->update($validated);

        return redirect()
            ->route('product-specifications.index')
            ->with('success', 'Specification berhasil update');
    }

    /**
     * Hapus spesifikasi dari produk
     */
    public function destroy(ProductSpecification $productSpecification)
    {
        $productSpecification->delete();

        return redirect()
            ->route('product-specifications.index')
            ->with('success', 'Specification berhasil dihapus');
    }
}
