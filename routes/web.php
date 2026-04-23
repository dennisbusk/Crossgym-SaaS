<?php

use App\Http\Controllers\Stripe\StripeConnectController;
use App\Http\Controllers\Stripe\StripeWebhookController;
use App\Http\Controllers\SuperadminController;
use App\Livewire\Admin\Classes\ClassForm;
use App\Livewire\Admin\Classes\ClassIndex;
use App\Livewire\Admin\Classes\ClassShow;
use App\Livewire\Admin\ClassTypes\ClassTypeForm;
use App\Livewire\Admin\ClassTypes\ClassTypeIndex;
use App\Livewire\Admin\ClassTypes\ClassTypeShow;
use App\Livewire\Admin\Colors\ColorIndex;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\EmailTemplates\TemplateForm;
use App\Livewire\Admin\EmailTemplates\TemplateIndex;
use App\Livewire\Admin\Plans\PlanIndex;
use App\Livewire\Admin\Plans\PlanShow;
use App\Livewire\Admin\Roles\ManagePermissions as RoleManagePermissions;
use App\Livewire\Admin\Roles\RoleForm;
use App\Livewire\Admin\Roles\RoleIndex;
use App\Livewire\Admin\Roles\RoleShow;
use App\Livewire\Admin\TenantChooseSubscription;
use App\Livewire\Admin\Tenants\TenantForm;
use App\Livewire\Admin\Tenants\TenantIndex;
use App\Livewire\Admin\Tenants\TenantShow;
use App\Livewire\Admin\Users\ManagePermissions as UserManagePermissions;
use App\Livewire\Admin\Users\UserForm;
use App\Livewire\Admin\Users\UserIndex;
use App\Livewire\Admin\Users\UserShow;
use App\Livewire\Profile\Billing;
use App\Livewire\Profile\Bookings;
use App\Livewire\Profile\Password;
use App\Livewire\Profile\Settings;
use App\Livewire\Profile\TwoFactor;
use App\Livewire\Profile\WorkoutLogForm;
use App\Livewire\Profile\WorkoutLogIndex;
use App\Livewire\SuperAdmin\Dashboard as SuperAdminDashboard;
use App\Livewire\SuperAdmin\Settings\General as SuperAdminSettingsGeneral;
use App\Models\ClassType;
use App\Models\GymClass;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Stripe webhook endpoints (public, no CSRF, rate limited)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->middleware('throttle:120,1')
    ->name('stripe.webhook');
Route::post('/webhook/stripe', [StripeWebhookController::class, 'handle'])
    ->middleware('throttle:120,1')
    ->name('stripe.webhook.alt');

// Stripe Connect Hosted Onboarding
Route::middleware(['auth'])
    ->prefix('stripe/connect')
    ->name('stripe.connect.')
    ->group(function () {
        Route::get('/start', [StripeConnectController::class, 'start'])->name('start');
        Route::get('/callback', [StripeConnectController::class, 'callback'])->name('callback');
        Route::get('/refresh', [StripeConnectController::class, 'refresh'])->name('refresh');
        Route::get('/return', [StripeConnectController::class, 'return'])->name('return');
    });

Route::middleware(['auth', 'terms.accepted'])->group(function () {

    // Tenant onboarding route
    Route::get('/onboarding', \App\Livewire\Tenant\Onboarding::class)
        ->name('tenant.onboarding');

    // Dashboard
    Route::middleware('can:viewAny,App\\Models\\Dashboard')->group(function () {
        Route::get('dashboard', Dashboard::class)->name('dashboard');
    });

    // Calendar
    Route::middleware('can:viewAny,App\\Models\\Calendar')->group(function () {
        Route::get('calendar', \App\Livewire\Admin\Calendar::class)->name('calendar');
    });

    // Workout Logs
    Route::get('workout-logs', WorkoutLogIndex::class)->name('workout-logs.index');
    Route::get('workout-logs/create', WorkoutLogForm::class)->name('workout-logs.create');
    Route::get('workout-logs/{workoutLog}', \App\Livewire\Profile\WorkoutLogShow::class)->name('workout-logs.show');
    Route::get('workout-logs/{workoutLog}/edit', WorkoutLogForm::class)->name('workout-logs.edit');
});

Route::middleware(['auth'])->group(function () {
    Volt::route('/terms-acceptance', 'auth.terms-acceptance')->name('terms.acceptance');
});

Route::get('/manifest.json', function () {
    $tenant = tenant();

    $appName = $tenant?->app_name ?? $tenant?->name ?? config('app.name', 'CrossGym');
    $iconUrl = $tenant && $tenant->icon_path ? asset('storage/'.$tenant->icon_path) : asset('favicon.svg');
    $themeColor = $tenant?->theme_color ?? '#000000';
    $backgroundColor = $tenant?->background_color ?? '#ffffff';

    return response()->json([
        'id' => '/',
        'name' => $appName,
        'short_name' => $tenant?->app_name ?? $tenant?->name ?? 'CrossGym',
        'description' => 'Crossgym SaaS Platform',
        'icons' => [
            [
                'src' => $iconUrl,
                'sizes' => '192x192',
                'type' => $tenant && $tenant->icon_path ? 'image/'.pathinfo($tenant->icon_path, PATHINFO_EXTENSION) : 'image/svg+xml',
                'purpose' => 'any',
            ],
            [
                'src' => $iconUrl,
                'sizes' => '512x512',
                'type' => $tenant && $tenant->icon_path ? 'image/'.pathinfo($tenant->icon_path, PATHINFO_EXTENSION) : 'image/svg+xml',
                'purpose' => 'any',
            ],
            [
                'src' => $iconUrl,
                'sizes' => '192x192',
                'type' => $tenant && $tenant->icon_path ? 'image/'.pathinfo($tenant->icon_path, PATHINFO_EXTENSION) : 'image/svg+xml',
                'purpose' => 'maskable',
            ],
            [
                'src' => $iconUrl,
                'sizes' => '512x512',
                'type' => $tenant && $tenant->icon_path ? 'image/'.pathinfo($tenant->icon_path, PATHINFO_EXTENSION) : 'image/svg+xml',
                'purpose' => 'maskable',
            ],
        ],
        'start_url' => '/',
        'display' => 'standalone',
        'theme_color' => $themeColor,
        'background_color' => $backgroundColor,
        'orientation' => 'portrait',
        'scope' => '/',
    ]);
})->name('manifest');

Route::middleware(['auth', 'terms.accepted'])->group(function () {

    Route::redirect('profile', 'profile/settings');

    // Alias for tests expecting /settings/profile with name profile.profile
    Route::get('/settings/profile', Settings::class)->name('profile.profile');

    Route::get('/subscription', TenantChooseSubscription::class)
        ->name('tenant.subscription');

    Route::get('profile/settings', Settings::class)->name('profile.settings');
    Route::get('profile/password', Password::class)->name('profile.password');
    Route::get('profile/billing', Billing::class)->name('profile.billing');
    Route::get('profile/bookings', Bookings::class)->name('profile.bookings');

    Route::get('profile/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    // Roles CRUD
    Route::middleware('can:viewAny,App\\Models\\Role')->group(function () {
        Route::get('roles', RoleIndex::class)->name('roles.index');
        Route::get('roles/create', RoleForm::class)->name('roles.create')->can('create', Role::class);
        Route::get('roles/{role}', RoleShow::class)->name('roles.show')->can('view', 'role');
        Route::get('roles/{role}/edit', RoleForm::class)->name('roles.edit')->can('update', 'role');
        Route::get('roles/{role}/permissions', RoleManagePermissions::class)->name('roles.permissions')->can('update', 'role');
    });
    // Tenants CRUD
    Route::middleware('can:viewAny,App\\Models\\Tenant')->group(function () {
        Route::get('/tenants', TenantIndex::class)->name('tenants.index');
        Route::get('/tenants/create', TenantForm::class)->name('tenants.create')->can('create', Tenant::class);
        Route::get('/tenants/{tenant}', TenantShow::class)->name('tenants.show')->can('view', 'tenant');
        Route::get('/tenants/{tenant}/edit', TenantForm::class)->name('tenants.edit')->can('update', 'tenant');
    });
    // Users CRUD
    Route::middleware('can:viewAny,App\\Models\\User')->group(function () {
        Route::get('/users', UserIndex::class)->name('users.index');
        Route::get('/users/create', UserForm::class)->name('users.create')->can('create', User::class);
        Route::get('/users/{user}', UserShow::class)->name('users.show')->can('view', 'user');
        Route::get('/users/{user}/edit', UserForm::class)->name('users.edit')->can('update', 'user');
        Route::get('/users/{user}/permissions', UserManagePermissions::class)->name('users.permissions')->can('update', 'user');
    });
    // Class Types CRUD
    Route::middleware('can:viewAny,App\\Models\\ClassType')->group(function () {
        Route::get('/class-types', ClassTypeIndex::class)->name('class-types.index');
        Route::get('/class-types/create', ClassTypeForm::class)->name('class-types.create')->can('create', ClassType::class);
        Route::get('/class-types/{classType}', ClassTypeShow::class)->name('class-types.show')->can('view', 'classType');
        Route::get('/class-types/{classType}/edit', ClassTypeForm::class)->name('class-types.edit')->can('update', 'classType');
    });
    // Classes CRUD
    Route::middleware('can:viewAny,App\\Models\\GymClass')->group(function () {
        Route::get('/classes', ClassIndex::class)->name('classes.index');
        Route::get('/classes/create', ClassForm::class)->name('classes.create')->can('create', GymClass::class);
        Route::get('/classes/{gymClass}', ClassShow::class)->name('classes.show')->can('view', 'gymClass');
        Route::get('/classes/{gymClass}/edit', ClassForm::class)->name('classes.edit')->can('update', 'gymClass');
        Route::post('/admin/wod/stream', [\App\Http\Controllers\WodController::class, 'stream'])->name('wod.stream');
        Route::post('/admin/wod/stream-refine', [\App\Http\Controllers\WodController::class, 'streamRefine'])->name('wod.stream-refine');
    });
    // Plans CRUD
    Route::middleware('can:viewAny,App\\Models\\Plan')->group(function () {
        Route::get('/plans', PlanIndex::class)->name('plans.index');
        Route::get('/plans/create', \App\Livewire\Admin\Plans\PlanForm::class)->name('plans.create')->middleware('connectedToStripe')->can('create', Plan::class);
        Route::get('/plans/{plan}', PlanShow::class)->name('plans.show')->can('view', 'plan');
        Route::get('/plans/{plan}/edit', \App\Livewire\Admin\Plans\PlanForm::class)->name('plans.edit')->middleware('connectedToStripe')->can('update', 'plan');
    });
    // Subscriptions Index
    Route::middleware('can:viewAny,App\\Models\\Subscription')->group(function () {
        Route::get('/subscriptions', \App\Livewire\Admin\Subscriptions\SubscriptionIndex::class)->name('subscriptions.index');
    });
    // Payments Index
    Route::middleware('can:viewAny,App\\Models\\Payment')->group(function () {
        Route::get('/payments', \App\Livewire\Admin\Payments\PaymentIndex::class)->name('payments.index');
    });

    // Retention Index
    Route::middleware('can:viewAny,App\\Models\\Retention')->group(function () {
        Route::get('/retention', \App\Livewire\Admin\Retention\RetentionIndex::class)->name('retention.index');
    });

    // Email Templates
    Route::middleware('can:viewAny,App\\Models\\EmailTemplate')->group(function () {
        Route::get('/email-templates', TemplateIndex::class)->name('admin.email-templates.index');
        Route::get('/email-templates/{template}/edit', TemplateForm::class)->name('admin.email-templates.edit');
    });

    // Email Logs Index
    Route::middleware('can:viewAny,App\\Models\\EmailLog')->group(function () {
        Route::get('/email-logs', \App\Livewire\Admin\EmailLogs\EmailLogIndex::class)->name('email-logs.index');
    });
    // Stripe Webhook Logs Index
    Route::middleware('can:viewAny,App\\Models\\StripeWebhookLog')->group(function () {
        Route::get('/stripe-webhook-logs', \App\Livewire\Admin\StripeWebhookLogs\StripeWebhookLogIndex::class)->name('stripe-webhook-logs.index');
    });

    // AI Coach Settings (Admin only)
    Route::middleware('can:viewAny,App\\Models\\AICoachSettings')->group(function () {
        Route::get('/admin/ai-coach-settings', \App\Livewire\Admin\AICoachSettingsForm::class)->name('ai-coach-settings.index');
    });

    // Colors Overview
    Route::middleware('can:viewAny,App\\Models\\Color')->group(function () {
        Route::get('/colors', ColorIndex::class)->name('colors.index');
    });

});
Route::impersonate();
// SuperAdmin routes (central, not tenant-scoped)
Route::middleware(['auth', 'can:viewDashboard,App\Models\SuperAdmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', SuperAdminDashboard::class)->name('dashboard');
    Route::get('/settings/general', SuperAdminSettingsGeneral::class)->name('settings.general');
});
Route::get('/superadmin/switch-login', [SuperadminController::class, 'switchTenant'])->name('superadmin.switch-tenant');

Route::get('/email/track-open/{trackingId}', [App\Http\Controllers\EmailTrackingController::class, 'open'])->name('email.track-open');

require __DIR__.'/auth.php';
