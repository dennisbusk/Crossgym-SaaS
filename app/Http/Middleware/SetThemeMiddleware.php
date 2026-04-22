<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class SetThemeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Get the "theme" cookie
        $themeCookie = $request->cookie('theme');

        // Default theme values
        $theme = [
            'mode' => 'light',
        ];

        // Try to decode JSON cookie if it exists
        if ($themeCookie) {
            $decoded = json_decode($themeCookie, true);
            if (is_array($decoded)) {
                $theme = array_merge($theme, $decoded);
            }
        }

        // Make it globally available in all Blade views
        View::share('theme', $theme);

        // Optionally also attach it to the request (for backend use)
        $request->attributes->set('theme', $theme);

        // dd($theme);
        return $next($request);
    }
}
