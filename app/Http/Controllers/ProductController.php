<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SpecificationGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'finalScore', 'specifications'])
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
            'description' => 'nullable|string|max:20000',
            'comparison_type' => 'nullable|in:terrestrial,satellite,combo,android',
            'status' => 'sometimes|in:active,disable',
            'official_url' => 'nullable|url:http,https|max:2048',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_id' => 'required|exists:categories,id',
            'specifications' => 'array',
            'specifications.*' => 'nullable|string|max:255',
            'proofs' => 'sometimes|array',
            'proofs.*.url' => 'nullable|url:http,https|max:2048',
            'proofs.*.type' => 'nullable|in:official,retailer,marketplace',
            'proofs.*.confirmed' => 'nullable|boolean',
            'production_year' => 'nullable|integer|between:1900,'.date('Y'),
        ]);

        DB::transaction(function () use ($request, $validated) {

            // IMAGE
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            $product = Product::create([
                'title' => $validated['title'],
                'description' => app(\App\Services\SafeProductHtml::class)->clean($validated['description'] ?? ''),
                'comparison_type' => $validated['comparison_type'] ?? null,
                'official_url' => $validated['official_url'] ?? null,
                'price' => $validated['price'] ?? null,
                'stock' => $validated['stock'],
                'category_id' => $validated['category_id'],
                'image' => $imagePath,
                'production_year' => $validated['production_year'] ?? null,
                'status' => $validated['status'] ?? 'disable',
            ]);

            // SPECIFICATIONS
            app(\App\Services\SaveProductSpecifications::class)->save($product, $validated['specifications'] ?? [], $validated['proofs'] ?? [], $request->user()->id);
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
            'official_url' => 'nullable|url:http,https|max:2048',
            'description' => 'nullable|string|max:20000',
            'comparison_type' => 'sometimes|nullable|in:terrestrial,satellite,combo,android',
            'specifications' => 'sometimes|array',
            'specifications.*' => 'nullable|string|max:255',
            'proofs' => 'sometimes|array',
            'proofs.*.url' => 'nullable|url:http,https|max:2048',
            'proofs.*.type' => 'nullable|in:official,retailer,marketplace',
            'proofs.*.confirmed' => 'nullable|boolean',
            'price' => 'sometimes|nullable|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'status' => 'sometimes|in:active,disable',
            'category_id' => 'sometimes|exists:categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'production_year' => 'nullable|integer|between:1900,'.date('Y'),
        ]);

        DB::transaction(function () use ($validated, $request, $product) {

            // Handle image update
            if ($request->hasFile('image')) {

                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    $oldImage = $product->image;
                    DB::afterCommit(fn () => Storage::disk('public')->delete($oldImage));
                }

                $validated['image'] = $request->file('image')
                    ->store('products', 'public');
            }

            /**
             * AUTO STATUS LOGIC
             */
            if (array_key_exists('stock', $validated) && ! array_key_exists('status', $validated)) {
                $validated['status'] = $validated['stock'] > 0 ? 'active' : 'disable';
            }

            $values = $validated['specifications'] ?? null;
            $proofs = $validated['proofs'] ?? [];
            unset($validated['specifications'], $validated['proofs']);
            if (array_key_exists('description', $validated)) {
                $validated['description'] = app(\App\Services\SafeProductHtml::class)->clean($validated['description'] ?? '');
            }
            $product->update($validated);
            if ($values !== null) {
                app(\App\Services\SaveProductSpecifications::class)->save($product, $values, $proofs, $request->user()->id);
            }
        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Product berhasil diperbarui');
    }

    public function activate(Product $product)
    {
        if ($product->finalScore === null) {
            return response()->json([
                'message' => 'Product score not calculated',
            ], 422);
        }

        $product->update(['status' => 'active']);

        return response()->json(['message' => 'Product activated']);
    }
}
