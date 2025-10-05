Project Development Guidelines (CrossGym SaaS)

Scope
These notes capture project-specific practices that speed up local setup, testing, and day-to-day development for this Laravel 12 + Livewire/Volt + Flux UI app. They assume familiarity with Laravel and PHP ecosystems and focus on what is unique or easy to miss in this codebase.

Build and Configuration
- PHP/Composer
  - PHP 8.2 is required (composer.json: "php": "^8.2").
  - Install dependencies: composer install
  - Post-install scripts will copy .env if needed and run package discovery. No DB migrations are run automatically on composer install; see below.

- Environment
  - Base env: .env is present in repo context; adjust APP_URL and others as needed.
  - For local keys: php artisan key:generate
  - Queues: Local dev typically uses sync; tests override to sync via phpunit.xml.
  - Email: Use array mailer in tests (phpunit.xml) and any local mailer in .env for dev.

- Database
  - App dev DB is defined in .env. Use sqlite/mysql as preferred in dev.
  - Tests are configured to use sqlite in-memory by default, via phpunit.xml:
    - DB_CONNECTION=sqlite, DB_DATABASE=:memory:
  - Migrations: php artisan migrate. If using sqlite file locally, ensure the file exists (see composer post-create-project-cmd for the pattern).

- Frontend/Vite/Tailwind
  - Tailwind v4 is used; don’t use removed utilities. Import syntax is @import "tailwindcss".
  - Run Vite in dev: npm run dev (or use the dev script below). If assets don’t appear, rebuild.

- One-command Dev Workflow (concurrently)
  - composer run dev launches:
    - php artisan serve
    - php artisan queue:listen --tries=1
    - npm run dev
  - Useful during full-stack development; otherwise, run processes individually.

Testing
- Framework
  - Test runner is Pest v3 with pest-plugin-laravel.
  - phpunit.xml config sets a clean test environment (array cache, array mailer, sync queue, sqlite :memory: database). No external services should be needed for unit/feature tests.

- Running tests
  - All tests: php artisan test
  - Single file: php artisan test tests/Feature/SomeTest.php
  - Filter by name: php artisan test --filter="users can logout"
  - Composer script also available: composer test (clears config then runs tests)

- Livewire/Volt and HTTP testing
  - Use Livewire::test(Component::class) (see tests/Feature/Auth/AuthenticationTest.php for examples).
  - Prefer route() with named routes; this app defines auth routes in routes/auth.php and web routes in routes/web.php.
  - Authentication helpers are available (actingAs, assertAuthenticated, assertGuest).

- Database behavior in tests
  - Transactions are not globally wrapped. If a test needs persistence across multiple requests, rely on the in-memory sqlite and model factories. Use DatabaseTransactions if you add stateful integration tests against a file-based database.

- Example minimal test
  - A passing sanity test was verified locally:
    - File (temporary): tests/Feature/SanityTest.php
    - Content:
      it('project sanity check passes', function () {
          expect(true)->toBeTrue();
      });
    - Run: php artisan test tests/Feature/SanityTest.php
    - Result: PASS (1 assertion). The file was removed after verification to keep the repo clean.

Adding New Tests
- Use Pest style by default: php artisan make:test --pest Feature/UserCanXyzTest
- Prefer Feature tests for HTTP and Livewire flows; Unit tests for pure PHP logic.
- Data setup
  - Use Model factories; check Database\Factories for existing patterns.
  - Avoid hitting external APIs; mock where necessary (Pest mocking helpers are available via pest-plugin-laravel).
- Livewire tips
  - For Livewire v3: state updates are deferred by default; use wire:model.live in Blade when you need real-time updates. In tests, set() mirrors server-side property updates.
  - Dispatch events with $this->dispatch() (not emit/dispatchBrowserEvent).

Code Style & Tooling
- Formatting: Run Pint (PSR-12) before committing. For staged changes only: vendor/bin/pint --dirty
- Naming: Use descriptive methods and properties (see conventions in app/ and tests/).
- Enums: TitleCase for enum keys if/when used.
- Configuration access: Use config() helpers; do not call env() outside config files.

Auth and Routing Notes
- Auth routes are split in routes/auth.php with Fortify-backed flows and Livewire screens:
  - Login, Register, ForgotPassword, ResetPassword, VerifyEmail
  - A small admin route group exists under /admin; adjust policies/guards as needed.
- If you add routes, prefer named routes and keep new auth-related routes in routes/auth.php to match current structure.

Flux UI + Tailwind Conventions
- Prefer Flux UI components (free edition) for Livewire UIs: e.g., <flux:button variant="primary" />.
- Tailwind v4 utility replacements apply (e.g., bg-black/50 instead of bg-opacity-50).
- Keep dark mode parity with existing pages if you add new UI.

Performance/Queues
- Use queued jobs for time-consuming tasks. In local dev, queue:listen runs in the dev script; in tests, queue is sync per phpunit.xml.

Debug/Dev Aids
- Laravel Boost is available as a dev dependency; use its tools where appropriate during interactive sessions.
- Browser/live reload issues typically resolve with npm run dev restart; Vite manifest errors usually mean a missing build.

CI and Misc
- A GitHub workflow exists for tests (.github/workflows/tests.yml). Match its PHP and dependency matrix if you add jobs.

Operational Notes for Contributors
- Keep to the existing directory conventions and the streamlined Laravel 12 structure (no app/Http/Middleware/, commands auto-discover, bootstrap/app.php wiring).
- When creating new models, consider adding factories and seeders as needed for tests.
- Prefer Eloquent relationships and resources over raw queries or manual JSON shaping.
