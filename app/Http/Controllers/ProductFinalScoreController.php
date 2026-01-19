<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductFinalScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductFinalScoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $scores = ProductFinalScore::with('product')
            ->orderByDesc('final_score')
            ->paginate(10);

        $products = Product::orderBy('title')->get();

        return view('pages.product-final-scores.index', compact('scores', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'           => 'required|exists:products,id|unique:product_final_scores,product_id',
            'specification_score'  => 'required|numeric|min:0',
            'click_score'          => 'required|numeric|min:0',
            'sales_score'          => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data) {
            $finalScore = $this->calculateFinalScore(
                $data['specification_score'],
                $data['click_score'],
                $data['sales_score']
            );

            ProductFinalScore::create([
                'product_id'          => $data['product_id'],
                'specification_score' => $data['specification_score'],
                'click_score'         => $data['click_score'],
                'sales_score'         => $data['sales_score'],
                'final_score'         => $finalScore,
                'calculated_at'       => now(),
            ]);
        });

        return redirect()
            ->route('product-final-scores.index')
            ->with('success', 'Final score berhasil dibuat');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductFinalScore $productFinalScore)
    {
        $data = $request->validate([
            'specification_score' => 'required|numeric|min:0',
            'click_score'         => 'required|numeric|min:0',
            'sales_score'         => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($data, $productFinalScore) {
            $finalScore = $this->calculateFinalScore(
                $data['specification_score'],
                $data['click_score'],
                $data['sales_score']
            );

            $productFinalScore->update([
                'specification_score' => $data['specification_score'],
                'click_score'         => $data['click_score'],
                'sales_score'         => $data['sales_score'],
                'final_score'         => $finalScore,
                'calculated_at'       => now(),
            ]);
        });

        return redirect()
            ->route('product-final-scores.index')
            ->with('success', 'Final score berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductFinalScore $productFinalScore)
    {
        $productFinalScore->delete();

        return redirect()
            ->route('product-final-scores.index')
            ->with('success', 'Final score berhasil dihapus');
    }

    /**
     * Hitung final score
     * (sementara versi sederhana, bisa dikembangkan)
     */
    private function calculateFinalScore(
        float $specification,
        float $click,
        float $sales
    ): float {
        return round(
            $specification + $click + $sales,
            2
        );
    }
}
