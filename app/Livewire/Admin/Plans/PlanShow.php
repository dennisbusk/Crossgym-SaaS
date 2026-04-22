<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Plans;

use App\Exports\PlanUsersExport;
use App\Models\ClassType;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Stripe\StripeTenantClient;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class PlanShow extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public Plan $plan;

    /**
     * @var array<int, array{id:int,name:string}>
     */
    public array $allowedClassTypes = [];

    // Listing state
    public string $search = '';

    public int $perPage = 10;

    /** @var array<int,bool> */
    public array $selected = [];

    public bool $selectAllPage = false;

    // Bulk change state
    public ?int $targetPlanId = null;

    public bool $confirmingBulkChange = false;

    public bool $includeAllFiltered = false; // apply to all filtered users (not only selected on page)

    /**
     * Other tenant plans to switch to
     *
     * @var array<int, array{id:int,name:string,stripe_price_id:string|null}>
     */
    public array $otherPlans = [];

    public function mount(Plan $plan): void
    {
        $this->authorize('view', $plan);
        $this->plan = $plan;

        $meta = (array) ($this->plan->metadata ?? []);
        $ids = $meta['allowed_class_type_ids'] ?? [];
        if (is_string($ids)) {
            $decoded = json_decode($ids, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $ids = $decoded;
            } else {
                $ids = [];
            }
        }
        $ids = array_map('intval', (array) $ids);

        if ($ids) {
            $this->allowedClassTypes = ClassType::query()
                ->when(tenant(), fn ($q) => $q->where('tenant_id', tenant()->id))
                ->whereIn('id', $ids)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(function (ClassType $ct) {
                    $name = $ct->getTranslation('name', app()->getLocale()) ?? (string) ($ct->name['da'] ?? $ct->name['en'] ?? '');

                    return ['id' => $ct->id, 'name' => (string) $name];
                })->all();
        }

        // Load other plans from same tenant for target selection
        $this->otherPlans = Plan::query()
            ->when(tenant(), fn ($q) => $q->where('tenant_id', tenant()->id))
            ->where('id', '!=', $this->plan->id)
            ->orderBy('name')
            ->get(['id', 'name', 'stripe_price_id'])
            ->map(fn (Plan $p) => ['id' => $p->id, 'name' => (string) $p->name, 'stripe_price_id' => $p->stripe_price_id])
            ->all();
    }

    public function toggleSelectAllPage(): void
    {
        $this->selectAllPage = ! $this->selectAllPage;
        foreach ($this->getUsers()->items() as $u) {
            $this->selected[$u->id] = $this->selectAllPage;
        }
    }

    /**
     * Paginator of users on this plan (tenant-scoped)
     */
    public function getUsers()
    {
        $tenantId = tenant()?->id;
        $query = User::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereHas('subscription', function ($q) {
                $q->where('plan_type', 'subscription')
                    ->whereIn('status', ['active', 'trialing'])
                    ->where('stripe_price_id', $this->plan->stripe_price_id);
            })
            ->with(['subscription' => function ($q) {
                $q->select(['id', 'user_id', 'stripe_price_id', 'status']);
            }])
            ->orderBy('name');

        if (strlen($this->search) > 0) {
            $s = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $this->search).'%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                    ->orWhere('email', 'like', $s);
            });
        }

        return $query->paginate($this->perPage);
    }

    public function bulkChangeConfirm(): void
    {
        $this->authorize('update', $this->plan);
        $this->confirmingBulkChange = true;
    }

    public function bulkChangeExecute(): void
    {
        $this->authorize('update', $this->plan);

        $target = $this->targetPlanId ? Plan::find($this->targetPlanId) : null;
        if (! $target) {
            session()->flash('error', __('Please select a target plan.'));

            return;
        }
        if (tenant() && $target->tenant_id !== tenant()->id) {
            session()->flash('error', __('Target plan must belong to the current tenant.'));

            return;
        }
        if ($target->id === $this->plan->id) {
            session()->flash('error', __('Target plan must be different from the current plan.'));

            return;
        }

        // Determine which users to process
        $users = collect();
        if ($this->includeAllFiltered) {
            // All filtered users (no pagination)
            $tenantId = tenant()?->id;
            $users = User::query()
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->whereHas('subscription', function ($q) {
                    $q->where('plan_type', 'subscription')
                        ->whereIn('status', ['active', 'trialing'])
                        ->where('stripe_price_id', $this->plan->stripe_price_id);
                })
                ->when(strlen($this->search) > 0, function ($q) {
                    $s = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $this->search).'%';
                    $q->where(function ($qq) use ($s) {
                        $qq->where('name', 'like', $s)->orWhere('email', 'like', $s);
                    });
                })
                ->get(['id', 'name', 'email']);
        } else {
            // Only selected on current and previous pages
            $ids = collect($this->selected)->filter()->keys()->map(fn ($k) => (int) $k)->all();
            if (empty($ids)) {
                session()->flash('error', __('Please select at least one user.'));

                return;
            }
            $users = User::query()->whereIn('id', $ids)->get(['id', 'name', 'email']);
        }

        $count = 0;
        $errors = [];
        $client = (new StripeTenantClient)->client();
        $opts = (new StripeTenantClient)->options();
        $useStripe = function_exists('connectedToStripe') ? connectedToStripe() : false;

        foreach ($users as $user) {
            /** @var Subscription|null $sub */
            $sub = Subscription::query()
                ->where('tenant_id', tenant()?->id)
                ->where('user_id', $user->id)
                ->where('plan_type', 'subscription')
                ->whereIn('status', ['active', 'trialing'])
                ->first();

            if (! $sub) {
                continue; // no active subscription to change
            }

            try {
                if ($useStripe && $sub->stripe_subscription_id && $target->stripe_price_id) {
                    // Retrieve subscription to find item id
                    $stripeSub = $client->subscriptions->retrieve($sub->stripe_subscription_id, ['expand' => ['items.data.price']], $opts);
                    $itemId = $stripeSub->items->data[0]->id ?? null;
                    if ($itemId) {
                        $client->subscriptions->update($sub->stripe_subscription_id, [
                            'items' => [[
                                'id' => $itemId,
                                'price' => $target->stripe_price_id,
                            ]],
                            'proration_behavior' => 'create_prorations',
                        ], $opts);
                    }
                }
            } catch (\Throwable $e) {
                $msg = __('Failed for :name (:id): :err', ['name' => $user->name, 'id' => $user->id, 'err' => $e->getMessage()]);
                $errors[] = $msg;
                Log::warning('Bulk plan change failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }

            // Update local record regardless; webhook will correct if needed
            $sub->update(['stripe_price_id' => $target->stripe_price_id]);
            $count++;
        }

        if ($count > 0) {
            session()->flash('success', __('Plan updated for :count users.', ['count' => $count]));
        }
        if (! empty($errors)) {
            session()->flash('warning', implode("\n", $errors));
        }

        // Refresh listings
        $this->selected = [];
        $this->selectAllPage = false;
        $this->confirmingBulkChange = false;
    }

    public function render(): View
    {
        return view('livewire.admin.plans.show', [
            'users' => $this->getUsers(),
        ]);
    }

    public function export()
    {
        $this->authorize('view', $this->plan);

        return Excel::download(new PlanUsersExport($this->plan, $this->search), 'plan-users.xlsx');
    }
}
