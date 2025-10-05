<?php

use App\Livewire\Admin\Roles\RoleForm;
use App\Livewire\Admin\Roles\RoleIndex;
use App\Livewire\Admin\Tenants\TenantForm;
use App\Livewire\Admin\Tenants\TenantIndex;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
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
        Route::get('roles/{role}/edit', RoleForm::class)->name('roles.edit')->can('update', 'role');
    });
// Tenants CRUD
    Route::middleware('can:viewAny,App\\Models\\Tenant')->group(function () {
        Route::get('/tenants', TenantIndex::class)->name('tenants.index');
        Route::get('/tenants/create', TenantForm::class)->name('tenants.create')->can('create', App\Models\Tenant::class);
        Route::get('/tenants/{tenant}/edit', TenantForm::class)->name('tenants.edit')->can('update', App\Models\Tenant::class);
    });

});
Route::impersonate();
require __DIR__.'/auth.php';
