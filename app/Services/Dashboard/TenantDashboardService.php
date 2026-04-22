<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\GymClass;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TenantDashboardService
{
    private const CACHE_TTL_MINUTES = 5;

    public function __construct(
        private ?int $tenantId = null
    ) {
        $this->tenantId = $tenantId ?? tenant()?->id ?? auth()->user()?->tenant_id;
    }

    public static function forTenant(?int $tenantId = null): self
    {
        return new self($tenantId);
    }

    /**
     * Get date range [start, end] for the given period.
     */
    public function getDateRange(string $period): array
    {
        $now = now();

        return match ($period) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'quarter' => [$now->copy()->startOfQuarter(), $now->copy()->endOfQuarter()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    /**
     * Revenue and transactions for the given period.
     */
    public function getRevenueStats(string $period = 'month'): array
    {
        $cacheKey = "dashboard.revenue.{$this->tenantId}.{$period}";

        return Cache::remember($cacheKey, self::CACHE_TTL_MINUTES * 60, function () use ($period) {
            [$start, $end] = $this->getDateRange($period);

            $paymentsQuery = Payment::query()
                ->where('tenant_id', $this->tenantId)
                ->where('status', 'succeeded')
                ->where('type', 'payment')
                ->whereBetween('created_at', [$start, $end]);

            $transactions = (int) $paymentsQuery->count();

            $sumSucceededDkk = (int) Payment::query()
                ->where('tenant_id', $this->tenantId)
                ->where('status', 'succeeded')
                ->where('type', 'payment')
                ->where('currency', 'DKK')
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount');

            $sumRefundsDkk = (int) Payment::query()
                ->where('tenant_id', $this->tenantId)
                ->where('status', 'refunded')
                ->whereIn('type', ['refund', 'partial_refund'])
                ->where('currency', 'DKK')
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount');

            $revenueDkk = max(0, $sumSucceededDkk - $sumRefundsDkk);

            return [
                'total_transactions' => $transactions,
                'total_revenue_dkk' => $revenueDkk,
            ];
        });
    }

    /**
     * Bookings stats. Active = future class bookings; Completed = check-ins (all-time).
     */
    public function getBookingsStats(string $period = 'month'): array
    {
        $cacheKey = "dashboard.bookings.{$this->tenantId}";

        return Cache::remember($cacheKey, self::CACHE_TTL_MINUTES * 60, function () {
            $now = now();

            $activeBookings = (int) DB::table('gym_class_user')
                ->join('classes', 'gym_class_user.gym_class_id', '=', 'classes.id')
                ->where('classes.tenant_id', $this->tenantId)
                ->where('classes.class_start', '>=', $now)
                ->count();

            $completedBookings = (int) DB::table('check_ins')
                ->where('tenant_id', $this->tenantId)
                ->whereNotNull('gym_class_id')
                ->whereNotNull('checked_at')
                ->count();

            return [
                'total_bookings_active' => $activeBookings,
                'total_bookings_completed' => $completedBookings,
            ];
        });
    }

    /**
     * Subscribers by plan (active subscriptions).
     */
    public function getSubscribersByPlan(): array
    {
        $cacheKey = "dashboard.subscribers.{$this->tenantId}";

        return Cache::remember($cacheKey, self::CACHE_TTL_MINUTES * 60, function () {
            $tenantId = $this->tenantId;
            $rows = Subscription::query()
                ->withoutGlobalScope('tenant')
                ->where('subscriptions.tenant_id', $tenantId)
                ->where('subscriptions.plan_type', 'subscription')
                ->whereIn('subscriptions.status', ['active', 'trialing'])
                ->join('plans', function ($join) use ($tenantId) {
                    $join->on('subscriptions.stripe_price_id', '=', 'plans.stripe_price_id')
                        ->where('plans.tenant_id', '=', $tenantId);
                })
                ->select('plans.name as name', DB::raw('count(*) as cnt'))
                ->groupBy('plans.name')
                ->get();

            $result = [];
            foreach ($rows as $row) {
                $result[(string) ($row->name ?? __('Unknown'))] = (int) $row->cnt;
            }

            return $result;
        });
    }

    /**
     * Upcoming classes (next 7 days).
     */
    public function getUpcomingClasses(int $days = 7): Collection
    {
        return GymClass::query()
            ->with(['classType', 'trainer', 'participants', 'trials'])
            ->where('tenant_id', $this->tenantId)
            ->where('class_start', '>=', now())
            ->where('class_start', '<=', now()->addDays($days))
            ->orderBy('class_start')
            ->limit(10)
            ->get();
    }

    /**
     * Recent payments (last 10).
     */
    public function getRecentActivity(int $limit = 10): Collection
    {
        return Payment::query()
            ->with('user')
            ->where('tenant_id', $this->tenantId)
            ->whereIn('type', ['payment', 'refund', 'partial_refund'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Revenue chart data (daily totals for the period).
     */
    public function getRevenueChartData(string $period = 'month'): array
    {
        [$start, $end] = $this->getDateRange($period);

        $rows = Payment::query()
            ->where('tenant_id', $this->tenantId)
            ->where('status', 'succeeded')
            ->where('type', 'payment')
            ->where('currency', 'DKK')
            ->whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $refunds = Payment::query()
            ->where('tenant_id', $this->tenantId)
            ->where('status', 'refunded')
            ->whereIn('type', ['refund', 'partial_refund'])
            ->where('currency', 'DKK')
            ->whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $data = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $dateStr = $current->toDateString();
            $labels[] = $current->format('d/m');
            $gross = $rows->firstWhere('date', $dateStr)?->total ?? 0;
            $refund = $refunds->get($dateStr)?->total ?? 0;
            $data[] = max(0, (int) $gross - (int) $refund) / 100;
            $current->addDay();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Bookings chart data (daily counts for the period).
     */
    public function getBookingsChartData(string $period = 'month'): array
    {
        [$start, $end] = $this->getDateRange($period);

        $rows = DB::table('gym_class_user')
            ->join('classes', 'gym_class_user.gym_class_id', '=', 'classes.id')
            ->where('classes.tenant_id', $this->tenantId)
            ->whereBetween('classes.class_start', [$start, $end])
            ->select(DB::raw('DATE(classes.class_start) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $data = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $dateStr = $current->toDateString();
            $labels[] = $current->format('d/m');
            $data[] = (int) ($rows->get($dateStr)?->count ?? 0);
            $current->addDay();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Classes this user is assigned as trainer for today.
     */
    public function getTrainerClassesToday(User $user): Collection
    {
        return GymClass::query()
            ->with(['classType', 'participants', 'trials'])
            ->where('tenant_id', $this->tenantId)
            ->where('trainer_id', $user->id)
            ->whereDate('class_start', now()->toDateString())
            ->orderBy('class_start')
            ->get();
    }

    /**
     * Clear cache for this tenant (e.g. after data changes).
     */
    public function clearCache(): void
    {
        Cache::forget("dashboard.revenue.{$this->tenantId}.today");
        Cache::forget("dashboard.revenue.{$this->tenantId}.week");
        Cache::forget("dashboard.revenue.{$this->tenantId}.month");
        Cache::forget("dashboard.revenue.{$this->tenantId}.quarter");
        Cache::forget("dashboard.revenue.{$this->tenantId}.year");
        Cache::forget("dashboard.bookings.{$this->tenantId}.today");
        Cache::forget("dashboard.bookings.{$this->tenantId}.week");
        Cache::forget("dashboard.bookings.{$this->tenantId}.month");
        Cache::forget("dashboard.bookings.{$this->tenantId}.quarter");
        Cache::forget("dashboard.bookings.{$this->tenantId}.year");
        Cache::forget("dashboard.subscribers.{$this->tenantId}");
    }
}
