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
            'category_id' => 'required|exists:categories,id',
            'specifications' => 'array',
            'specifications.*.id' => 'exists:specifications,id',
            'specifications.*.value' => 'required'
        ]);

        DB::transaction(function () use ($request) {

            $product = Product::create([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
                'category_id' => $request->category_id,
                'production_year' => $request->production_year,
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

        return response()->json(['message' => 'Product created'], 201);
    }

    public function show(Product $product)
    {
        return $product->load([
            'category',
            'specifications.specification.group',
            'finalScore'
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'title' => 'sometimes|required',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
        ]);

        $product->update($request->only([
            'title',
            'description',
            'price',
            'stock',
            'status'
        ]));

        return response()->json(['message' => 'Product updated']);
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
