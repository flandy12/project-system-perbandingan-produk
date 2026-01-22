<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\SpecificationGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'finalScore'])
            ->latest()
            ->paginate(15);
        $categories = Category::all();

        $specificationGroups = SpecificationGroup::with('specifications')
            ->orderBy('name')
            ->get();

        return view('pages.products.index', compact('products', 'categories', 'specificationGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_id' => 'required|exists:categories,id',
            'specifications' => 'array',
            'specifications.*.id' => 'exists:specifications,id',
            'specifications.*.value' => 'required'
        ]);

        DB::transaction(function () use ($request) {

            // =============================
            // HANDLE IMAGE UPLOAD
            // =============================
            $imagePath = null;

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store(
                    'products',
                    'public'
                );
            }

            $product = Product::create([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
                'category_id' => $request->category_id,
                'production_year' => $request->production_year,
                'image' => $imagePath, // 🔥 SIMPAN PATH IMAGE
                'status' => 'disable'
            ]);

            foreach ($request->specifications ?? [] as $spec) {
                ProductSpecification::create([
                    'product_id' => $product->id,
                    'specification_id' => $spec['id'],
                    'value' => $spec['value']
                ]);
            }
        });
        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted');
    }

    public function show(Product $product)
    {

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'status' => 'sometimes|in:enable,disable',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        DB::transaction(function () use ($validated, $request, $product) {

            // Handle image update
            if ($request->hasFile('image')) {

                // Hapus image lama jika ada
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                // Upload image baru
                $validated['image'] = $request->file('image')
                    ->store('products', 'public');
            }

            $product->update($validated);
        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Product berhasil diperbarui');
    }

    public function activate(Product $product)
    {
        if ($product->finalScore === null) {
            return response()->json([
                'message' => 'Product score not calculated'
            ], 422);
        }

        $product->update(['status' => 'active']);

        return response()->json(['message' => 'Product activated']);
    }
}
