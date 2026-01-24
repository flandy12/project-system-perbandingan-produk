<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductClick;
use App\Models\WebsiteVisit;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function productClick(Product $product, Request $request)
    {
        ProductClick::create([
            'product_id' => $product->id,
            'user_id'    => auth()->id(), // null kalau guest
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent(), 0, 500),
            'clicked_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }


    public function pageView()
    {
        WebsiteVisit::create([
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->header('referer'),
            'visited_at' => now(),
        ]);
    }
}
