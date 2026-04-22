<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class connectedToStripeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! connectedToStripe()) {
            return redirect()->back()->with('error', __('Stripe Connect not onboarded').'<a href="'.route('stripe.connect.start').'" class="inline-flex items-center px-4 py-2 bg-primary text-white font-semibold rounded-lg hover:bg-primary/80 transition">'.__('Forbind med Stripe').'</a>');
        }

        return $next($request);
    }
}
