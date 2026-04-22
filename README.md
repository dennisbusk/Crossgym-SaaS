# CrossGym SaaS — Stripe Connect Hosted Onboarding

This app integrates Stripe Connect to onboard Tenants using Stripe’s Hosted Onboarding, configured for the Destination Charges model (platform handles payments; tenants take legal responsibility for payouts/refunds).


## Prerequisites
- PHP 8.2
- Composer dependencies installed: `composer install`
- Node.js for Vite dev: `npm install`
- DB configured in `.env`
- Stripe account (Test Mode)


## Environment
Validate required keys: `php artisan env:validate` (use `--strict` to fail on missing keys).

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


## Scheduler & Queue (Class attendance seat release)
This app automatically frees seats for participants who did not check in by class start.

- A queued job `App\Jobs\ReleaseUncheckedSeatsJob` runs every minute via the scheduler and detaches all non–checked-in participants for classes that have just started (with a small lookback window):

  - Scheduler wiring is in `routes/console.php`.
  - Requires the Laravel scheduler to be running: `php artisan schedule:work`.
  - Requires a queue worker in non-sync environments: e.g. `php artisan queue:work`.

- Local/dev defaults
  - Tests use the `sync` queue driver (see `phpunit.xml`).
  - For development you can also run `composer run dev` which includes a queue listener.

- Production recommendations
  - Use a robust queue driver like Redis or database (set `QUEUE_CONNECTION=redis` or `database`).
  - Ensure a process supervisor (e.g. Supervisor, systemd) manages both `schedule:work` and the queue worker.

### Manual backfill/trigger command
You can trigger seat release on demand with an Artisan command:

```
php artisan classes:release-unchecked --lookback=10
```

The `--lookback` option (minutes) controls how far back from "now" to scan for classes that just started. Default is 10 minutes.


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
- Dedicated log channel: `storage/logs/stripe.log` — all Stripe-related errors, rate limits, and payment failures
- DB log table: `stripe_webhook_logs` — each webhook event with payload, status, and error (key for debugging retries/failures)

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
