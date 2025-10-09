<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\Stripe\StripeTenantClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class WebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');
        $connectedAccount = $request->headers->get('Stripe-Account');

        // Identify tenant by Stripe-Account header or fallback to domain/current tenant
        $tenant = null;
        if ($connectedAccount) {
            $tenant = Tenant::where('stripe_connect_account_id', $connectedAccount)->first();
        }
        $tenantClient = new StripeTenantClient($tenant);

        $secret = $tenantClient->webhookSecret();
        if (!$secret) {
            Log::warning('Stripe webhook secret missing');
            return response('No webhook secret configured', 400);
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $secret
            );
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        } catch (UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        }

        // Persist raw payload for debugging
        try {
            $dir = storage_path('app/stripe-webhooks');
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            $filename = $dir . DIRECTORY_SEPARATOR . now()->format('Ymd_His_u') . '_' . ($event->type ?? 'unknown') . '.json';
            file_put_contents($filename, $payload);
        } catch (\Throwable $e) {
            Log::warning('Failed to store webhook payload', ['error' => $e->getMessage()]);
        }

        // Handle events (stubs)
        $type = $event->type ?? null;
        switch ($type) {
            case 'checkout.session.completed':
            case 'invoice.payment_succeeded':
            case 'invoice.payment_failed':
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
            case 'payment_intent.succeeded':
            case 'payment_intent.payment_failed':
            case 'charge.refunded':
            case 'account.updated':
                // Dispatch job for async processing if desired
                // Bus::dispatch(new HandleStripeEventJob($tenant?->id, $event->toArray()));
                break;
            default:
                // ignore
                break;
        }

        return response('ok', 200);
    }
}
