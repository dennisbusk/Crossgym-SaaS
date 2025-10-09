<?php

use App\Http\Controllers\Stripe\StripeConnectController;
use App\Http\Controllers\Stripe\StripeWebhookController;
use App\Livewire\Admin\Roles\RoleForm;
use App\Livewire\Admin\Roles\RoleIndex;
use App\Livewire\Admin\Roles\RoleShow;
use App\Livewire\Admin\Roles\ManagePermissions as RoleManagePermissions;
use App\Livewire\Admin\Tenants\TenantForm;
use App\Livewire\Admin\Tenants\TenantIndex;
use App\Livewire\Admin\Tenants\TenantShow;
use App\Livewire\Admin\Users\UserForm;
use App\Livewire\Admin\Users\UserIndex;
use App\Livewire\Admin\Users\UserShow;
use App\Livewire\Admin\Users\ManagePermissions as UserManagePermissions;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Admin\ClassTypes\ClassTypeForm;
use App\Livewire\Admin\ClassTypes\ClassTypeIndex;
use App\Livewire\Admin\ClassTypes\ClassTypeShow;
use App\Livewire\Admin\Classes\ClassForm;
use App\Livewire\Admin\Classes\ClassIndex;
use App\Livewire\Admin\Classes\ClassShow;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Stripe webhook endpoint (public)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

Route::middleware(['auth'])->group(function () {
    Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');
    // Stripe Connect flow
    Route::get('/admin/stripe/connect', [StripeConnectController::class, 'connect'])->name('stripe.connect');
    Route::get('/admin/stripe/connect/callback', [StripeConnectController::class, 'callback'])->name('stripe.connect.callback');
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('settings/two-factor', TwoFactor::class)
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
        Route::get('roles/create', RoleForm::class)->name('roles.create')->can('create', 'App\\Models\\Role');
        Route::get('roles/{role}', RoleShow::class)->name('roles.show')->can('view', 'role');
        Route::get('roles/{role}/edit', RoleForm::class)->name('roles.edit')->can('update', 'role');
        Route::get('roles/{role}/permissions', RoleManagePermissions::class)->name('roles.permissions')->can('update', 'role');
    });
// Tenants CRUD
    Route::middleware('can:viewAny,App\\Models\\Tenant')->group(function () {
        Route::get('/tenants', TenantIndex::class)->name('tenants.index');
        Route::get('/tenants/create', TenantForm::class)->name('tenants.create')->can('create', App\Models\Tenant::class);
        Route::get('/tenants/{tenant}', TenantShow::class)->name('tenants.show')->can('view', App\Models\Tenant::class);
        Route::get('/tenants/{tenant}/edit', TenantForm::class)->name('tenants.edit')->can('update', App\Models\Tenant::class);
    });
// Users CRUD
    Route::middleware('can:viewAny,App\\Models\\User')->group(function () {
        Route::get('/users', UserIndex::class)->name('users.index');
        Route::get('/users/create', UserForm::class)->name('users.create')->can('create', App\Models\User::class);
        Route::get('/users/{user}', UserShow::class)->name('users.show')->can('view', 'user');
        Route::get('/users/{user}/edit', UserForm::class)->name('users.edit')->can('update', 'user');
        Route::get('/users/{user}/permissions', UserManagePermissions::class)->name('users.permissions')->can('update', 'user');
    });
// Class Types CRUD
    Route::middleware('can:viewAny,App\\Models\\ClassType')->group(function () {
        Route::get('/class-types', ClassTypeIndex::class)->name('class-types.index');
        Route::get('/class-types/create', ClassTypeForm::class)->name('class-types.create')->can('create', App\Models\ClassType::class);
        Route::get('/class-types/{classType}', ClassTypeShow::class)->name('class-types.show')->can('view', 'classType');
        Route::get('/class-types/{classType}/edit', ClassTypeForm::class)->name('class-types.edit')->can('update', 'classType');
    });
// Classes CRUD
    Route::middleware('can:viewAny,App\\Models\\GymClass')->group(function () {
        Route::get('/classes', ClassIndex::class)->name('classes.index');
        Route::get('/classes/create', ClassForm::class)->name('classes.create')->can('create', App\Models\GymClass::class);
        Route::get('/classes/{gymClass}', ClassShow::class)->name('classes.show')->can('view', 'gymClass');
        Route::get('/classes/{gymClass}/edit', ClassForm::class)->name('classes.edit')->can('update', 'gymClass');
    });

});
Route::impersonate();
require __DIR__.'/auth.php';
