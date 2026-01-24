<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\SpecificationGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'finalScore'])
            ->latest()
            ->paginate(15);
        $categories = Category::all();

        $specificationGroups = SpecificationGroup::with('specifications')->get();

        // buat map default spec: [id => '']
        $specMap = [];
        foreach ($specificationGroups as $group) {
            foreach ($group->specifications as $spec) {
                $specMap[$spec->id] = '';
            }
        }

        return view('pages.products.index', compact('products', 'categories', 'specificationGroups', 'specMap'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_id' => 'required|exists:categories,id',
            'specifications' => 'array',
            'specifications.*' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $validated) {

            // IMAGE
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            $product = Product::create([
                'title' => $validated['title'],
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'category_id' => $validated['category_id'],
                'image' => $imagePath,
                'status' => 'disable'
            ]);

            // SPECIFICATIONS
            if (!empty($validated['specifications'])) {
                foreach ($validated['specifications'] as $specId => $value) {
                    if ($value !== null && $value !== '') {
                        ProductSpecification::create([
                            'product_id' => $product->id,
                            'specification_id' => $specId,
                            'value' => $value
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Product berhasil ditambahkan');
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
            'status' => 'sometimes|in:active,disable',
            'category_id' => 'sometimes|exists:categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        DB::transaction(function () use ($validated, $request, $product) {

            // Handle image update
            if ($request->hasFile('image')) {

                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                $validated['image'] = $request->file('image')
                    ->store('products', 'public');
            }

            /**
             * AUTO STATUS LOGIC
             */
            if (array_key_exists('stock', $validated)) {
                $validated['status'] = $validated['stock'] > 0 ? 'active' : 'disable';
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
