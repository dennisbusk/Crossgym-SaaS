<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\PaymentResource;
use App\Models\Payment;
use App\Services\Stripe\StripeService;
use Illuminate\Http\Request;

class PaymentController extends BaseApiController
{
    protected string $model = Payment::class;

    protected string $resource = PaymentResource::class;

    public function index(Request $request)
    {
        $query = Payment::query();

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('stripe_id', 'like', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($u) use ($searchTerm) {
                        $u->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%");
                    });
            });
        }

        return PaymentResource::collection($query->get());
    }

    public function getPortalUrl(Request $request)
    {
        $user = $request->user();

        if (! $user->stripe_customer_id) {
            return response()->json(['message' => __('User has no Stripe customer ID')], 422);
        }

        $returnUrl = $request->input('return_url', config('app.url'));

        $stripeService = StripeService::forTenant($user->tenant);
        $session = $stripeService->createPortalSession($user->stripe_customer_id, $returnUrl);

        return response()->json(['url' => $session['url']]);
    }

    public function getCheckoutUrl(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'price_id' => 'required|string',
            'success_url' => 'nullable|string',
            'cancel_url' => 'nullable|string',
            'mode' => 'nullable|string|in:payment,subscription',
        ]);

        $mode = $request->input('mode', 'subscription');
        $stripeService = StripeService::forTenant($user->tenant);

        $params = [
            'price_id' => $request->price_id,
            'success_url' => $request->input('success_url'),
            'cancel_url' => $request->input('cancel_url'),
            'user_id' => $user->id,
            'customer_email' => $user->email,
        ];

        if ($user->stripe_customer_id) {
            $params['customer_id'] = $user->stripe_customer_id;
        }

        if ($mode === 'subscription') {
            $session = $stripeService->createSubscriptionCheckout($params);
        } else {
            // Adjust for createPayment which expects line_items
            $params['line_items'] = [[
                'price' => $request->price_id,
                'quantity' => 1,
            ]];
            $session = $stripeService->createPayment($params);
        }

        return response()->json(['url' => $session['url']]);
    }
}
