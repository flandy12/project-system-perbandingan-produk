<?php

namespace App\Http\Middleware;

use App\Models\WebsiteVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackWebsiteVisit
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // Hanya halaman tertentu
            if (!in_array($request->route()?->getName(), ['home', 'gallery'])) {
                return $next($request);
            }

            // Hanya GET
            if (!$request->isMethod('GET')) {
                return $next($request);
            }

            // Exclude admin & api
            if ($request->is('admin/*') || $request->is('api/*')) {
                return $next($request);
            }

            // Bot filter
            if (Str::contains(strtolower($request->userAgent()), ['bot', 'crawl', 'spider'])) {
                return $next($request);
            }

            // Anti spam (1 visit / 30 menit / IP)
            $exists = WebsiteVisit::where('ip_address', $request->ip())
                ->where('visited_at', '>=', now()->subMinutes(30))
                ->exists();

            if (!$exists) {
                WebsiteVisit::create([
                    'user_id'    => auth()->id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => substr($request->userAgent(), 0, 500),
                    'visited_at' => now(),
                ]);
            }

            return $next($request);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
