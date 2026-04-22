<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Exports\DashboardStatsExport;
use App\Models\Dashboard as DashboardModel;
use App\Services\Dashboard\TenantDashboardService;
use App\Services\Stripe\StripeTenantClient;
use App\Services\UserSubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\UserDashboardWidget;
use App\Models\Exercise;

class Dashboard extends Component
{
    public string $period = 'month';

    public int $totalTransactions = 0;

    public int $totalRevenueDkk = 0;

    public int $totalBookingsActive = 0;

    public int $totalBookingsCompleted = 0;

    /** @var array<string,int> */
    public array $subscribersByPlan = [];

    /** @var array<int, object> */
    public array $upcomingClasses = [];

    /** @var array<int, object> */
    public array $recentActivity = [];

    /** @var array{labels: array<string>, data: array<float>} */
    public array $revenueChartData = ['labels' => [], 'data' => []];

    /** @var array{labels: array<string>, data: array<int>} */
    public array $bookingsChartData = ['labels' => [], 'data' => []];

    /** @var array<int, object> */
    public array $trainerClassesToday = [];

    public ?array $subscriptionNotice = null;

    protected $listeners = ['widget-removed' => '$refresh'];

    public function addExerciseWidget(int $exerciseId): void
    {
        auth()->user()?->dashboardWidgets()->create([
            'type' => 'exercise_progress',
            'settings' => ['exercise_id' => $exerciseId],
            'order' => auth()->user()->dashboardWidgets()->count(),
        ]);
        $this->dispatch('widget-added');
    }

    public function addPrWidget(): void
    {
        auth()->user()?->dashboardWidgets()->create([
            'type' => 'personal_record',
            'settings' => [],
            'order' => auth()->user()->dashboardWidgets()->count(),
        ]);
        $this->dispatch('widget-added');
    }

    public function mount(): void
    {
        $this->loadData();
    }

    public function updatedPeriod(): void
    {
        $this->loadData();
    }

    protected function loadData(): void
    {
        $user = auth()->user();
        $dashboard = app(DashboardModel::class);
        $service = TenantDashboardService::forTenant();

        $this->totalTransactions = 0;
        $this->totalRevenueDkk = 0;
        $this->totalBookingsActive = 0;
        $this->totalBookingsCompleted = 0;
        $this->subscribersByPlan = [];
        $this->upcomingClasses = [];
        $this->recentActivity = [];
        $this->revenueChartData = ['labels' => [], 'data' => []];
        $this->bookingsChartData = ['labels' => [], 'data' => []];

        try {
            if ($user?->can('view_revenue', $dashboard)) {
                $revenue = $service->getRevenueStats($this->period);
                $this->totalTransactions = $revenue['total_transactions'];
                $this->totalRevenueDkk = $revenue['total_revenue_dkk'];
            }

            if ($user?->can('view_bookings', $dashboard)) {
                $bookings = $service->getBookingsStats($this->period);
                $this->totalBookingsActive = $bookings['total_bookings_active'];
                $this->totalBookingsCompleted = $bookings['total_bookings_completed'];
            }

            if ($user?->can('view_subscribers', $dashboard)) {
                $this->subscribersByPlan = $service->getSubscribersByPlan();
            }

            if ($user?->can('view_upcoming_classes', $dashboard)) {
                $this->upcomingClasses = $service->getUpcomingClasses(7)->all();
            }

            if ($user?->can('view_recent_activity', $dashboard)) {
                $this->recentActivity = $service->getRecentActivity(10)->all();
            }

            if ($user?->can('view_charts', $dashboard)) {
                $this->revenueChartData = $service->getRevenueChartData($this->period);
                $this->bookingsChartData = $service->getBookingsChartData($this->period);
            }

            if ($user?->can('view_trainer_widget', $dashboard) && $user) {
                $this->trainerClassesToday = $service->getTrainerClassesToday($user)->all();
            }
        } catch (\Throwable $e) {
            Log::warning('Admin Dashboard stats failed', ['error' => $e->getMessage()]);
        }

        if ($user) {
            $sub = $user->subscription;
            if ($sub && $sub->plan_type === 'subscription') {
                $needsActionStatuses = ['incomplete', 'incomplete_expired', 'past_due', 'unpaid'];
                if (in_array((string) ($sub->status ?? ''), $needsActionStatuses, true)) {
                    $this->subscriptionNotice = ['status' => (string) $sub->status];
                }
            }
        }
    }

    public function export()
    {
        $this->authorize('view_export', app(DashboardModel::class));

        $service = TenantDashboardService::forTenant();
        $revenue = $service->getRevenueStats($this->period);
        $bookings = $service->getBookingsStats($this->period);
        $subscribers = $service->getSubscribersByPlan();

        return Excel::download(new DashboardStatsExport([
            'total_transactions' => $revenue['total_transactions'],
            'total_revenue_dkk' => $revenue['total_revenue_dkk'],
            'total_bookings_active' => $bookings['total_bookings_active'],
            'total_bookings_completed' => $bookings['total_bookings_completed'],
            'subscribers_by_plan' => $subscribers,
        ]), 'dashboard-stats.xlsx');
    }

    public function completeSubscription()
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $sub = $user->subscription;
        if (! $sub || $sub->plan_type !== 'subscription' || ! $sub->stripe_subscription_id) {
            return null;
        }

        app(UserSubscriptionService::class)->ensureStripeCustomer($user);

        $client = (new StripeTenantClient)->client();
        $opts = (new StripeTenantClient)->options();

        try {
            $stripeSub = $client->subscriptions->retrieve($sub->stripe_subscription_id, ['expand' => ['latest_invoice']], $opts);
            $invoice = $stripeSub->latest_invoice ?? null;
            $hostedUrl = $invoice?->hosted_invoice_url ?? null;
            if ($hostedUrl) {
                return redirect()->away($hostedUrl);
            }
        } catch (\Throwable $e) {
            //
        }

        try {
            $session = $client->billingPortal->sessions->create([
                'customer' => $user->stripe_customer_id,
                'return_url' => route('dashboard'),
            ], $opts);

            if (! empty($session->url)) {
                return redirect()->away($session->url);
            }
        } catch (\Throwable $e) {
            //
        }

        return null;
    }

    #[Layout('components.layouts.app')]
    public function render(): View
    {
        return view('livewire.admin.dashboard', [
            'dashboard' => app(DashboardModel::class),
            'availableExercises' => Exercise::orderBy('name')->get(),
            'userWidgets' => auth()->user()?->dashboardWidgets ?? collect(),
        ]);
    }
}
