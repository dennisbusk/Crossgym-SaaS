<?php

declare(strict_types=1);

namespace App\Livewire\Admin\EmailTemplates;

use App\Models\EmailTemplate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class TemplateIndex extends Component
{
    use WithPagination;

    public $search = '';

    protected $standardTemplates = [
        'welcome_email' => [
            'subject' => 'Welcome to {{ tenant_name }}',
            'content' => 'Hi {{ user_name }}, welcome to {{ tenant_name }}!',
        ],
        'subscription_confirmation' => [
            'subject' => 'Subscription confirmed',
            'content' => 'Hi {{ user_name }}, your subscription to {{ plan_name }} is now active.',
        ],
        'payment_failed' => [
            'subject' => 'Payment failed',
            'content' => 'Hi {{ user_name }}, your payment for {{ plan_name }} failed. Please update your payment information.',
        ],
        'booking_confirmation' => [
            'subject' => 'Booking confirmed',
            'content' => 'Hi {{ user_name }}, your booking for {{ class_name }} on {{ class_date }} is confirmed.',
        ],
        'retention_email' => [
            'subject' => 'We miss you!',
            'content' => 'Hi {{ user_name }}, we haven\'t seen you in a while. Come down and train with us!',
        ],
    ];

    public function mount()
    {
        $this->ensureStandardTemplatesExist();
    }

    protected function ensureStandardTemplatesExist()
    {
        $tenantId = auth()->user()->tenant_id;

        foreach ($this->standardTemplates as $name => $data) {
            EmailTemplate::firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $name],
                [
                    'subject' => __($data['subject']),
                    'content' => __($data['content']),
                    'is_active' => true,
                ]
            );
        }
    }

    public function toggleActive($templateId)
    {
        $template = EmailTemplate::findOrFail($templateId);
        $template->is_active = ! $template->is_active;
        $template->save();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $templates = EmailTemplate::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where(function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('subject', 'like', '%'.$this->search.'%');
            })
            ->paginate(10);

        return view('livewire.admin.email-templates.index', [
            'templates' => $templates,
        ]);
    }
}
