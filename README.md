# CrossGym SaaS — Stripe Connect Hosted Onboarding

This app integrates Stripe Connect to onboard Tenants using Stripe’s Hosted Onboarding, configured for the Destination Charges model (platform handles payments; tenants take legal responsibility for payouts/refunds).


## Prerequisites
- PHP 8.2
- Composer dependencies installed: `composer install`
- Node.js for Vite dev: `npm install`
- DB configured in `.env`
- Stripe account (Test Mode)


## Environment
Add these to your `.env` (Test Mode values):

```
STRIPE_SECRET=sk_test_xxx
STRIPE_CONNECT_CLIENT_ID=ca_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
```

Additional optional per-tenant keys (if you plan to store tenant-specific API keys):

```
# Optional per-tenant override columns exist:
# stripe_public_key, stripe_secret_key, stripe_webhook_secret
```

Config keys are in `config/services.php` under `stripe`.


## Database
Run migrations to ensure required columns exist on `tenants`:

```
php artisan migrate
```

Columns added include:
- stripe_connect_account_id (nullable string)
- stripe_connect_refresh_token (nullable string)
- stripe_connect_access_token (nullable string)
- stripe_connect_email (nullable string)
- stripe_connect_onboarded (boolean, default false)
- stripe_connect_charges_enabled (boolean, default false)
- stripe_connect_payouts_enabled (boolean, default false)
- stripe_public_key (nullable string)
- stripe_secret_key (nullable string)
- stripe_webhook_secret (nullable string)


## Dev Run
- One command dev (PHP server, queue listener, Vite):

```
composer run dev
```

Or run individually:

```
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```


## Stripe Connect — Hosted Onboarding Flow
Routes are defined in `routes/web.php` under the `stripe/connect` prefix:

- GET `/stripe/connect/start` — Create Stripe Account + AccountLink and redirect to Hosted Onboarding
- GET `/stripe/connect/refresh` — Re-open onboarding if the link expired
- GET `/stripe/connect/return` — Return URL after onboarding; sync account status
- GET `/stripe/connect/callback` — Optional OAuth callback support (for Standard Connect)

To start onboarding as a tenant admin, visit your Dashboard and click “Forbind med Stripe”.

The controller delegates to `App\Services\Stripe\StripeConnectService`:
- `createAccountLink(Tenant)` — Creates an Express account (if needed) and an AccountLink
- `handleOAuthCallback(Request)` — Token exchange for OAuth (compatibility)
- `updateTenantAccountStatus(string)` — Pulls account status and updates tenant flags


## Tenant Dashboard Button
On the admin dashboard (Livewire component view), a button appears for tenant admins when not onboarded:

- Label: “Forbind med Stripe”
- Route: `stripe.connect.start`
- When completed, dashboard shows: “Stripe er forbundet!”


## Webhooks
A central webhook endpoint exists for all Stripe events:

- POST `/stripe/webhook` (legacy)
- POST `/webhook/stripe` (preferred per spec)

Both are wired to `App\Http\Controllers\Stripe\StripeWebhookController@handle`.

Logging:
- Dedicated log channel: `storage/logs/stripe.log`
- DB log table: `stripe_webhook_logs` (via model `StripeWebhookLog`)

Handled events include (among others):
- `account.updated` — Updates tenant flags: charges/payouts enabled, onboarded
- `payment_intent.succeeded` / `payment_intent.payment_failed`
- `customer.subscription.*`

Stripe’s signature is validated using `STRIPE_WEBHOOK_SECRET`.


## Destination Charges (Payments)
The platform creates PaymentIntents and routes funds to the tenant using `transfer_data.destination`.

Example (PHP):

```php
\Stripe\PaymentIntent::create([
    'amount' => 5000, // in øre
    'currency' => 'dkk',
    'payment_method_types' => ['card'],
    'transfer_data' => [
        'destination' => $tenant->stripe_connect_account_id,
    ],
]);
```

A helper method is available:
- `App\Services\Stripe\StripeService::createDestinationChargeIntent($tenant, $amount, 'dkk', $params)`


## Local Test Instructions (Stripe Test Mode)
1. Start the app (see Dev Run above).
2. Stripe CLI:
   ```
   npm install -g stripe
   stripe login
   stripe listen --forward-to localhost:8000/webhook/stripe
   ```
3. Ensure you’re logged in as a tenant admin, go to Dashboard, click “Forbind med Stripe”.
4. Complete Stripe’s onboarding forms (test data).
5. After return, verify `tenants` table updated (`stripe_connect_account_id`, `stripe_connect_onboarded`, `charges_enabled`, `payouts_enabled`).
6. Trigger a test event (optional):
   ```
   stripe trigger account.updated
   ```
   Check `storage/logs/stripe.log` and DB `stripe_webhook_logs`.
7. Test a destination charge (optional):
   - Use the helper method or simple PaymentIntent example above with a test card.


## QA Checklist
- Migrations verified
- Onboarding flow redirects correctly (start → return)
- Tokens and account ID stored when using OAuth callback
- Dashboard shows “Stripe er forbundet!” when onboarded
- Destination charge works in Test Mode
- Webhooks received and logged
- Return & refresh URLs verified
