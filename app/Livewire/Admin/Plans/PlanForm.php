<?php

declare( strict_types=1 );

namespace App\Livewire\Admin\Plans;

use App\Models\ClassType;
use App\Models\Plan;
use App\Services\Stripe\StripePlanService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Rule;
use Livewire\Component;

class PlanForm extends Component {

    use AuthorizesRequests;

    public ?Plan $plan = null;

    #[Rule( 'required|string|max:255' )]
    public string $name = '';

    // amount entered in DKK (or selected currency) as decimal string, converted to minor units on save
    #[Rule( 'required|string' )]
    public string $amount = '';

    #[Rule( 'required|string|size:3' )]
    public string $currency = 'DKK';

    // 'subscription' or 'one_off'
    #[Rule( 'required|in:subscription,one_off' )]
    public string $plan_type = 'subscription';

    // Only used for subscriptions
    #[Rule( 'nullable|in:day,week,month,year' )]
    public string $interval = 'month';

    #[Rule( 'nullable|integer|min:0' )]
    public ?int $weekly_booking_limit = null;

    #[Rule( 'nullable|integer|min:0' )]
    public ?int $total_booking_credits = null; // primarily for one_off

    /**
     * @var array<int>
     */
    #[Rule( 'array' )]
    public array $allowed_class_type_ids = [];

    /**
     * @var array<int, array{id:int,name:string}>
     */
    public array $classTypes = [];

    public function mount( $plan = null ): void {
        $this->plan = $plan instanceof Plan ? $plan : new Plan();

        if ( $this->plan && $this->plan->exists ) {
            $this->authorize('update', $this->plan);
            $this->name     = (string) $this->plan->name;
            $this->currency = strtoupper((string) ( $this->plan->currency ?? 'DKK' ));
            // Show amount as decimal (e.g., 199.00)
            $this->amount   = number_format(( (int) $this->plan->amount ) / 100, 2, '.', '');
            $this->interval = (string) ( $this->plan->interval ?? 'month' );

            $meta                        = (array) ( $this->plan->metadata ?? [] );
            $this->plan_type             = (string) ( $meta['plan_type'] ?? ( $this->interval === 'one_time' ? 'one_off' : 'subscription' ) );
            $this->weekly_booking_limit  = isset($meta['weekly_booking_limit']) ? (int) $meta['weekly_booking_limit'] : null;
            $this->total_booking_credits = isset($meta['total_booking_credits']) ? (int) $meta['total_booking_credits'] : null;
            $allowed                     = $meta['allowed_class_type_ids'] ?? [];
            if ( is_string($allowed) ) {
                $decoded = json_decode($allowed, true);
                if ( json_last_error() === JSON_ERROR_NONE && is_array($decoded) ) {
                    $allowed = $decoded;
                }
                else {
                    $allowed = [];
                }
            }
            $this->allowed_class_type_ids = array_map('intval', (array) $allowed);
        }
        else {
            $this->authorize('create', Plan::class);
        }

        $this->loadClassTypes();
    }

    protected function loadClassTypes(): void {
        $this->classTypes = ClassType::query()
                                     ->when(tenant(), fn( $q ) => $q->where('tenant_id', tenant()->id))
                                     ->orderBy('name')
                                     ->get([ 'id', 'name' ])
                                     ->map(function ( ClassType $ct ) {
                                         $name = $ct->getTranslation('name', app()->getLocale()) ?? (string) ( $ct->name['da'] ?? $ct->name['en'] ?? '' );

                                         return [
                                             'id'   => $ct->id,
                                             'name' => (string) $name,
                                         ];
                                     })->all();
    }

    public function save() {
        $this->validate();

        // Build metadata payload (stored locally and on Stripe Product)
        $metadata = [
            'plan_type'              => $this->plan_type,
            'weekly_booking_limit'   => $this->weekly_booking_limit,
            'total_booking_credits'  => $this->total_booking_credits,
            'allowed_class_type_ids' => $this->allowed_class_type_ids,
        ];

        // Convert amount to minor units
        $amountMinor = (int) round(( (float) str_replace(',', '.', $this->amount) ) * 100);
        $currency    = strtoupper($this->currency);

        $interval = $this->plan_type === 'one_off' ? 'one_time' : $this->interval;

        $service = StripePlanService::make();

        if ( $this->plan && $this->plan->exists ) {
            $this->plan = $service->updatePlan($this->plan, $this->name, $amountMinor, $currency, $interval, $metadata);
            session()->flash('status', __('Plan opdateret.'));
        }
        else {
            $this->plan = $service->createPlan($this->name, $amountMinor, $interval, $currency, $metadata);
            session()->flash('status', __('Plan oprettet.'));

            return redirect()->route('plans.edit', $this->plan);
        }
    }

    public function render(): View {
        return view('livewire.admin.plans.form');
    }
}
