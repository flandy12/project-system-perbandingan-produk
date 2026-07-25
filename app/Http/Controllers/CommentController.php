<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Product;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comments = Comment::with([
            'user:id,name',
            'commentable',
            'parent:id,comment',
        ])
            ->latest()
            ->paginate(10);

        return view('pages.comments.index', compact('comments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::orderBy('title')->get();

        $parents = Comment::whereNull('parent_id')
            ->orderByDesc('id')
            ->get();

        return view('comments.create', compact('products', 'parents'));
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'comment'   => 'required|string|max:2000',
            'rating' => 'required|integer|between:1,5',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $product->comments()->create([
            'user_id'   => Auth::id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'comment'   => $validated['comment'],
            'status'    => 'pending', // atau pending
        ]);

        $exists = Rating::where('product_id', $product->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($exists) {
            return back()->with('error', 'Anda sudah memberikan rating untuk produk ini. Silakan edit rating Anda.');
        }


        return back()->with('success', 'Komentar berhasil dikirim.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        $comment->load([
            'user',
            'product',
            'parent',
            'replies.user'
        ]);

        return view('comments.show', compact('comment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        $products = Product::orderBy('title')->get();

        $parents = Comment::where('id', '!=', $comment->id)
            ->whereNull('parent_id')
            ->get();

        return view('comments.edit', compact(
            'comment',
            'products',
            'parents'
        ));
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'parent_id'  => ['nullable', 'exists:comments,id'],
            'comment'    => ['required', 'string', 'max:2000'],
            'status'     => ['required', 'in:pending,approved,rejected'],
        ]);

        DB::transaction(function () use ($validated, $comment) {

            $comment->update([
                'product_id' => $validated['product_id'],
                'parent_id'  => $validated['parent_id'] ?? null,
                'comment'    => $validated['comment'],
                'status'     => $validated['status'],
            ]);
        });

        return redirect()
            ->route('comments.index')
            ->with('success', 'Comment updated successfully.');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Comment $comment)
    {
        DB::transaction(function () use ($comment) {

            $comment->delete();
        });

        return redirect()
            ->route('comments.index')
            ->with('success', 'Comment deleted successfully.');
    }

    public function approve(Comment $comment)
    {
        $comment->update([
            'status' => 'approved',
        ]);

        return back()->with('success', 'Komentar berhasil disetujui.');
    }

    public function reject(Comment $comment)
    {
        $comment->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Komentar berhasil ditolak.');
    }
}
