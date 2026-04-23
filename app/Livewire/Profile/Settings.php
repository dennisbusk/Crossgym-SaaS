<?php

namespace App\Livewire\Profile;

use App\Models\Dashboard;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Settings extends Component
{
    public string $name = '';

    public string $email = '';

    /** @var array<string, bool> */
    public array $dashboardSettings = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;

        $defaultSettings = [
            'revenue' => true,
            'bookings' => true,
            'subscribers' => true,
            'upcoming_classes' => true,
            'recent_activity' => true,
            'charts' => true,
            'trainer_widget' => true,
            'training_dashboard' => true,
        ];

        if ($user) {
            foreach ($user->dashboardWidgets as $dw) {
                $defaultSettings['dw_'.$dw->id] = true;
            }
        }

        $this->dashboardSettings = array_merge($defaultSettings, $user->dashboard_settings ?? []);
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'dashboardSettings' => ['array'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'dashboard_settings' => $this->dashboardSettings,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function getAvailableWidgetsProperty(): array
    {
        $dashboard = app(Dashboard::class);
        $user = auth()->user();

        $widgets = [
            'revenue' => ['label' => __('Revenue (DKK)'), 'permission' => 'view_revenue'],
            'bookings' => ['label' => __('Bookings'), 'permission' => 'view_bookings'],
            'subscribers' => ['label' => __('Total Subscribers (per Plan)'), 'permission' => 'view_subscribers'],
            'upcoming_classes' => ['label' => __('Upcoming Classes'), 'permission' => 'view_upcoming_classes'],
            'recent_activity' => ['label' => __('Recent Activity'), 'permission' => 'view_recent_activity'],
            'charts' => ['label' => __('Charts'), 'permission' => 'view_charts'],
            'trainer_widget' => ['label' => __('My Classes Today'), 'permission' => 'view_trainer_widget'],
            'training_dashboard' => ['label' => __('My Training Dashboard'), 'permission' => null],
        ];

        $available = array_filter($widgets, function ($widget) use ($user, $dashboard) {
            if ($widget['permission'] === null) {
                return true;
            }

            return $user?->can($widget['permission'], $dashboard);
        });

        if ($user) {
            foreach ($user->dashboardWidgets as $dw) {
                $label = $dw->type === 'personal_record'
                    ? __('Personal Records')
                    : __('Exercise Progress');

                if ($dw->type === 'exercise_progress' && isset($dw->settings['exercise_id'])) {
                    $exercise = \App\Models\Exercise::find($dw->settings['exercise_id']);
                    if ($exercise) {
                        $label .= ': '.$exercise->getTranslation('name', app()->getLocale());
                    }
                }

                $available['dw_'.$dw->id] = [
                    'label' => $label,
                    'permission' => null,
                ];
            }
        }

        return $available;
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}
