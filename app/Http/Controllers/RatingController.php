<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    /**
     * List rating berdasarkan produk
     */
    public function index(Product $product)
    {
        $ratings = Rating::with('user')
            ->where('product_id', $product->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'average_rating' => round(
                Rating::where('product_id', $product->id)->avg('rating'),
                1
            ),
            'total_ratings' => Rating::where('product_id', $product->id)->count(),
            'data' => $ratings,
        ]);
    }

    /**
     * Simpan atau update rating
     */
    public function store(Request $request)
    {
        abort_unless($request->user()?->fresh()?->is_active, 403);
        $request->validate([
            'product_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('products', 'id')->where('status', 'active'),
            ],
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],
            'review' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        DB::beginTransaction();

        try {

            $rating = Rating::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'product_id' => $request->product_id,
                ],
                [
                    'rating' => $request->rating,
                    'review' => $request->review,
                ]
            );

            $this->updateProductRating($request->product_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rating berhasil disimpan',
                'data' => $rating,
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Ulasan belum tersimpan. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Update rating
     */
    public function update(Request $request, Rating $rating)
    {
        if ($rating->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {

            $rating->update([
                'rating' => $request->rating,
                'review' => $request->review,
            ]);

            $this->updateProductRating($rating->product_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rating berhasil diperbarui',
                'data' => $rating,
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Ulasan belum diperbarui. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Hapus rating
     */
    public function destroy(Rating $rating)
    {
        if ($rating->user_id !== auth()->id()) {
            abort(403);
        }

        $productId = $rating->product_id;

        $rating->delete();

        $this->updateProductRating($productId);

        return response()->json([
            'success' => true,
            'message' => 'Rating berhasil dihapus',
        ]);
    }

    /**
     * Update rata-rata rating produk
     */
    private function updateProductRating($productId)
    {
        $product = Product::find($productId);

        if (! $product) {
            return;
        }

        $product->update([
            'average_rating' => round(
                Rating::where('product_id', $productId)->avg('rating') ?? 0,
                1
            ),
            'total_ratings' => Rating::where('product_id', $productId)->count(),
        ]);
    }
}
