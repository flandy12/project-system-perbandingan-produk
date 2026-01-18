<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Product;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $discounts = Discount::with('product')
            ->latest()
            ->paginate(15);

        $products = Product::all();

        return view('pages.discounts.index', compact('discounts', 'products'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        return view('pages.discounts.index', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'percentage' => ['required', 'integer', 'min:1', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        Discount::create($validated);

        return redirect()
            ->route('discount.index')
            ->with('success', 'Discount berhasil ditambahkan');
    }

    public function edit(Discount $discount)
    {
        $products = Product::orderBy('name')->get();
        return view('discount.index', compact('discount', 'products'));
    }

    public function update(Request $request, Discount $discount)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'percentage' => ['required', 'integer', 'min:1', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $discount->update($validated);

        return redirect()
            ->route('discount.index')
            ->with('success', 'Discount berhasil diupdate');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();

        return redirect()
            ->route('discount.index')
            ->with('success', 'Discount berhasil dihapus');
    }
}
