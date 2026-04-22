<?php

declare(strict_types=1);

namespace App\Livewire\Tenant;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Onboarding extends Component
{
    public int $step = 1;

    public string $app_name = '';

    public string $theme_color = '#000000';

    public bool $allow_member_billing_management = true;

    public function mount()
    {
        $tenant = tenant();
        if ($tenant) {
            $this->app_name = $tenant->app_name ?? '';
            $this->theme_color = $tenant->theme_color ?? '#000000';
            $this->allow_member_billing_management = (bool) ($tenant->allow_member_billing_management ?? true);
        }
    }

    #[Layout('components.layouts.app')]
    public function render(): View
    {
        return view('livewire.tenant.onboarding');
    }

    public function next(): void
    {
        $this->step = min(4, $this->step + 1);
    }

    public function prev(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function complete(): \Symfony\Component\HttpFoundation\Response
    {
        $tenant = tenant();

        if ($tenant) {
            $tenant->update([
                'app_name' => $this->app_name,
                'theme_color' => $this->theme_color,
                'allow_member_billing_management' => $this->allow_member_billing_management,
                'onboarded_at' => now(),
            ]);

            $this->seedDefaultTemplates($tenant);
        }

        session()->flash('success', __('You\'re all set!'));

        return redirect()->route('dashboard');
    }

    protected function seedDefaultTemplates($tenant): void
    {
        $templates = [
            [
                'name' => 'welcome_email',
                'subject' => __('Welcome to {{ tenant_name }}'),
                'content' => __("Hi {{ user_name }},\n\nWelcome to {{ tenant_name }}! We are happy to have you with us.\n\nBest regards,\n{{ tenant_name }}"),
            ],
            [
                'name' => 'subscription_confirmation',
                'subject' => __('Subscription confirmation: {{ plan_name }}'),
                'content' => __("Hi {{ user_name }},\n\nThank you for your purchase of {{ plan_name }}. Your subscription is now active.\n\nBest regards,\n{{ tenant_name }}"),
            ],
            [
                'name' => 'payment_failed',
                'subject' => __('Payment failed'),
                'content' => __("Hi {{ user_name }},\n\nWe were unfortunately unable to complete the payment for your subscription at {{ tenant_name }}. Please check your payment information.\n\nBest regards,\n{{ tenant_name }}"),
            ],
            [
                'name' => 'booking_confirmation',
                'subject' => __('Booking confirmed: {{ class_name }}'),
                'content' => __("Hi {{ user_name }},\n\nYour booking for {{ class_name }} on {{ class_date }} is confirmed.\n\nWe look forward to seeing you!\n\nBest regards,\n{{ tenant_name }}"),
            ],
            [
                'name' => 'retention_email',
                'subject' => __('We miss you at {{ tenant_name }}'),
                'content' => __("Hi {{ user_name }},\n\nIt's been a while since we've seen you for training. We hope everything is well and look forward to seeing you again soon!\n\nBest regards,\n{{ tenant_name }}"),
            ],
        ];

        foreach ($templates as $data) {
            \App\Models\EmailTemplate::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => $data['name'],
                ],
                [
                    'subject' => $data['subject'],
                    'content' => $data['content'],
                    'is_active' => true,
                ]
            );
        }
    }
}
