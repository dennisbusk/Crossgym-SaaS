# Project Development Guidelines (CrossGym SaaS)

## Scope
These notes capture project-specific practices that speed up local setup, testing, and day-to-day development for this Laravel 12 + Livewire/Volt + Flux UI app.  
They assume familiarity with Laravel and PHP ecosystems and focus on what is unique or easy to miss in this codebase.

---

## Build and Configuration
- **PHP/Composer**
    - PHP 8.2 is required (`composer.json: "php": "^8.2"`).
    - Install dependencies: `composer install`
    - Post-install scripts will copy `.env` if needed and run package discovery.  
      No DB migrations are run automatically on `composer install`; see below.

- **Environment**
    - Base env: `.env` is present in repo context; adjust `APP_URL` and others as needed.
    - For local keys: `php artisan key:generate`
    - Queues: Local dev typically uses `sync`; tests override to `sync` via `phpunit.xml`.
    - Email: Use `array` mailer in tests (`phpunit.xml`) and any local mailer in `.env` for dev.

- **Database**
    - App dev DB is defined in `.env`. Use sqlite/mysql as preferred in dev.
    - Tests are configured to use sqlite in-memory by default, via `phpunit.xml`:
        - `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`
    - Migrations: `php artisan migrate`.  
      If using sqlite file locally, ensure the file exists (see composer `post-create-project-cmd` for the pattern).

- **Frontend/Vite/Tailwind**
    - Tailwind v4 is used; don’t use removed utilities. Import syntax is `@import "tailwindcss"`.
    - Run Vite in dev: `npm run dev` (or use the dev script below). If assets don’t appear, rebuild.

- **One-command Dev Workflow (concurrently)**
    - `composer run dev` launches:
        - `php artisan serve`
        - `php artisan queue:listen --tries=1`
        - `npm run dev`
    - Useful during full-stack development; otherwise, run processes individually.

---

## Testing
- **Framework**
    - Test runner is Pest v3 with `pest-plugin-laravel`.
    - `phpunit.xml` config sets a clean test environment (array cache, array mailer, sync queue, sqlite :memory: database).  
      No external services should be needed for unit/feature tests.

- **Running tests**
    - All tests: `php artisan test`
    - Single file: `php artisan test tests/Feature/SomeTest.php`
    - Filter by name: `php artisan test --filter="users can logout"`
    - Composer script also available: `composer test` (clears config then runs tests)

- **Livewire/Volt and HTTP testing**
    - Use `Livewire::test(Component::class)` (see `tests/Feature/Auth/AuthenticationTest.php` for examples).
    - Prefer `route()` with named routes; this app defines auth routes in `routes/auth.php` and web routes in `routes/web.php`.
    - Authentication helpers are available (`actingAs`, `assertAuthenticated`, `assertGuest`).

- **Database behavior in tests**
    - Transactions are not globally wrapped.  
      If a test needs persistence across multiple requests, rely on the in-memory sqlite and model factories.  
      Use `DatabaseTransactions` if you add stateful integration tests against a file-based database.

- **Example minimal test**
    - A passing sanity test was verified locally:
        - File (temporary): `tests/Feature/SanityTest.php`
        - Content:
          ```php
          it('project sanity check passes', function () {
              expect(true)->toBeTrue();
          });
          ```
        - Run: `php artisan test tests/Feature/SanityTest.php`
        - Result: PASS (1 assertion). The file was removed after verification to keep the repo clean.

---

## Adding New Tests
- Use Pest style by default:  
  `php artisan make:test --pest Feature/UserCanXyzTest`
- Prefer Feature tests for HTTP and Livewire flows; Unit tests for pure PHP logic.
- **Data setup**
    - Use Model factories; check `Database\Factories` for existing patterns.
    - Avoid hitting external APIs; mock where necessary (`pest-plugin-laravel` mocking helpers are available).
- **Livewire tips**
    - For Livewire v3: state updates are deferred by default; use `wire:model.live` in Blade when you need real-time updates.  
      In tests, `set()` mirrors server-side property updates.
    - Dispatch events with `$this->dispatch()` (not `emit` / `dispatchBrowserEvent`).

---

## Code Style & Tooling
- **Formatting:** Run Pint (PSR-12) before committing.  
  For staged changes only: `vendor/bin/pint --dirty`
- **Naming:** Use descriptive methods and properties (see conventions in `app/` and `tests/`).
- **Enums:** TitleCase for enum keys if/when used.
- **Configuration access:** Use `config()` helpers; do not call `env()` outside config files.

---

## Auth and Routing Notes
- Auth routes are split in `routes/auth.php` with Fortify-backed flows and Livewire screens:
    - Login, Register, ForgotPassword, ResetPassword, VerifyEmail
    - A small admin route group exists under `/admin`; adjust policies/guards as needed.
- If you add routes, prefer named routes and keep new auth-related routes in `routes/auth.php` to match current structure.

---

## Flux UI + Tailwind Conventions
- Prefer Flux UI components (free edition) for Livewire UIs:  
  Example: `<flux:button variant="primary" />`.
- Tailwind v4 utility replacements apply (e.g., `bg-black/50` instead of `bg-opacity-50`).
- Keep dark mode parity with existing pages if you add new UI.

---

## Index View Guidelines
- All “Index” views (lists) must use a **Flux table component**.
- The **last column must always contain action buttons**, right-aligned.
- Each row should include edit, view, and delete buttons as icon-only (`variant="ghost"`).
- Filtering, search input, and pagination should be placed above the table.
- Example:

```blade
<div class="space-y-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">{{ __('Users') }}</h1>
        
    </div>

    @if (session('status'))
        <div class="rounded-md bg-green-50 p-3 text-green-700">{{ __(session('status')) }}</div>
    @endif
    
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <div class="p-4 flex w-full justify-end items-center">
            <div class="flex items-center gap-2 justify-self-end">
                <x-flowbite.button class="hover:cursor-pointer" wire:click="export" variant="primary">
                    {{ __('Export') }}
                </x-flowbite.button>
                <x-flowbite.button tag="a" href="{{ route('users.create') }}" variant="primary">
                    {{ __('New User') }}
                </x-flowbite.button>
            </div>
        </div>
        <x-flowbite.table>
            <x-flowbite.table.head class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <x-flowbite.table.head.row>
                <x-flowbite.table.head.cell class="p-4">
                    <div class="flex items-center">
                        <input id="checkbox-all-search" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="checkbox-all-search" class="sr-only">checkbox</label>
                    </div>
                </x-flowbite.table.head.cell>
            <x-flowbite.table.head.cell>{{ __('ID') }}</x-flowbite.table.head.cell>
            <x-flowbite.table.head.cell>{{ __('Name') }}</x-flowbite.table.head.cell>
            <x-flowbite.table.head.cell>{{ __('Email') }}</x-flowbite.table.head.cell>
            <x-flowbite.table.head.cell>{{ __('Role') }}</x-flowbite.table.head.cell>
            <x-flowbite.table.head.cell>{{ __('Tenant') }}</x-flowbite.table.head.cell>
            <x-flowbite.table.head.cell class="text-right">{{ __('Actions') }}</x-flowbite.table.head.cell>
            </x-flowbite.table.head.row>
        </x-flowbite.table.head>

        <x-flowbite.table.body>
            @forelse ($users as $user)
                <x-flowbite.table.body.row>
                    <x-flowbite.table.body.cell class="w-4 p-4">
                        <div class="flex items-center">
                            <input id="checkbox-table-search-1" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <label for="checkbox-table-search-1" class="sr-only">checkbox</label>
                        </div>
                    </x-flowbite.table.body.cell>
                    <x-flowbite.table.body.cell>{{ $user->id }}</x-flowbite.table.body.cell>
                    <x-flowbite.table.body.cell>{{ $user->name }}</x-flowbite.table.body.cell>
                    <x-flowbite.table.body.cell>{{ $user->email }}</x-flowbite.table.body.cell>
                    <x-flowbite.table.body.cell>{{ $user->role?->name }}</x-flowbite.table.body.cell>
                    <x-flowbite.table.body.cell>{{ $user->tenant?->name }}</x-flowbite.table.body.cell>
                    <x-flowbite.table.body.cell class="text-right space-x-2">
                        <x-flowbite.button icon="eye" href="{{ route('users.show', $user) }}" variant="ghost" />
                        <x-flowbite.button icon="pencil-square" href="{{ route('users.edit', $user) }}" variant="ghost" />
                        <x-flowbite.button icon="trash" wire:click="delete({{ $user->id }})" variant="ghost" />
                    </x-flowbite.table.body.cell>
                </x-flowbite.table.body.row>
            @empty
                <x-flowbite.table.body.row class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 ">
                    <x-flowbite.table.body.cell class="w-4 p-4" colspan="6">{{ __('No users found.') }}</x-flowbite.table.body.cell>
                </x-flowbite.table.body.row>
            @endforelse
        </x-flowbite.table.body>
    </x-flowbite.table>
</div>

```
- **Export button:**
    - Place a Flux button above the table, next to "New" or search input.
    - The button should trigger a Livewire method that calls a `Maatwebsite\Excel` export class.
    - Example:
      ```blade
      <flux:button wire:click="export" variant="primary">
          {{ __('Export') }}
      </flux:button>
      ```
    - In the Livewire component:
      ```php
      use Maatwebsite\Excel\Facades\Excel;
      use App\Exports\UsersExport;
  
      public function export()
      {
          return Excel::download(new UsersExport, 'users.xlsx');
      }
      ```
      
## Design rules
- Use `variant="ghost"` for inline buttons.
- Use `text-right` for the actions column.
- No clickable rows — only explicit action buttons.
- Consistent padding (`px-4 py-3`).
- Avoid modals or dropdowns for simple actions unless multiple contextual options exist.

## Behavior
- Clicking a row should not navigate automatically – keep explicit buttons for clarity.
- Optional: Add search, filter, or pagination above the table in a standard layout:

```blade
<div class="flex justify-between items-center mb-4">
    <flux:input placeholder="{{ __('Search...') }}" wire:model.live="search" />
    <flux:button wire:click="create" variant="primary">{{ __('New') }} {{ $modelLabel }}</flux:button>
</div>
```

## Translation Guidelines
- **Default language:** da
- Always wrap display text in `{{ __('...') }}` — in Blade, Livewire, and PHP.
- Add new strings to `resources/lang/da.json` automatically when new UI text is introduced.
- **Model fields:**  
  All fields like `name`, `description`, `title`, `content`, etc. must be translatable using a JSON or table-based translation system (e.g., `spatie/laravel-translatable`).

## Performance/Queues
- Use queued jobs for time-consuming tasks.
- In local dev, `queue:listen` runs in the dev script; in tests, queue is `sync` per `phpunit.xml`.

## Debug/Dev Aids
- Laravel Boost is available as a dev dependency; use its tools where appropriate during interactive sessions.
- Browser/live reload issues typically resolve with `npm run dev` restart; Vite manifest errors usually mean a missing build.

## CI and Misc
- GitHub workflows: see `.github/workflows/lint.yml` for code style checks.  
  If you add a tests workflow, create `.github/workflows/tests.yml` and match its PHP and dependency matrix accordingly.

## Operational Notes for Contributors
- Follow the streamlined Laravel 12 structure:
    - Middleware lives in `app/Http/Middleware` and is registered in `bootstrap/app.php`
    - Commands auto-discover
    - Bootstrapping via `bootstrap/app.php`
- When creating new models, always:
    - Add `tenant_id` and `belongsTo(Tenant::class)`
    - Add factories and seeders for testing
    - Ensure translatable fields are defined if applicable
- Prefer Eloquent relationships and resources over raw queries or manual JSON shaping.

## Junie Implementation Checklist
When creating a new CRUD component, ensure:

1. **Index View**
    - Uses `flux:table`.
    - Actions column is last and right-aligned.
    - Edit/View/Delete buttons use `variant="ghost"`.
    - Search, filters, and pagination placed above table.
    - **Export button** is present above the table and calls a Maatwebsite Excel export class.

2. **Create/Edit Views**
    - All labels, buttons, and texts wrapped in `{{ __('...') }}`.
    - Flux UI components used consistently.
    - Save and Cancel buttons placed at bottom-right.

3. **Model Conventions**
    - `tenant_id` field exists.
    - `belongsTo(Tenant::class)` relationship.
    - Fields like `name`, `description`, etc. marked as translatable.

4. **Translations**
    - Add new strings to `lang/da.json`.
    - Default language is `da`.
    - Wrap all Blade, Livewire, and PHP output with `{{ __('...') }}`.

5. **Code Style**
    - Follows Laravel Pint formatting.
    - Strict typing enabled (`declare(strict_types=1);`).
    - No inline JS in Blade; Alpine.js used for interactivity.

6. **Testing**
    - Feature tests for Livewire/HTTP flows.
    - Unit tests for pure PHP logic.
    - Use factories for test data.
    - Avoid hitting external APIs; mock if needed.
