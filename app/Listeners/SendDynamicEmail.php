<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Events\PaymentFailed;
use App\Events\RetentionTriggered;
use App\Events\SubscriptionCreated;
use App\Events\UserRegistered;
use App\Mail\DynamicMailable;
use App\Models\EmailTemplate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendDynamicEmail implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $templateName = $this->getTemplateNameForEvent($event);
        if (! $templateName) {
            return;
        }

        $user = $event->user;
        $tenantId = $user->tenant_id;

        if (! $tenantId) {
            return;
        }

        $template = EmailTemplate::where('tenant_id', $tenantId)
            ->where('name', $templateName)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return;
        }

        $data = $this->getDataForEvent($event);

        Mail::to($user->email)->send(new DynamicMailable($template, $data));
    }

    protected function getTemplateNameForEvent(object $event): string
    {
        return match (get_class($event)) {
            UserRegistered::class, \Illuminate\Auth\Events\Registered::class => 'welcome_email',
            SubscriptionCreated::class => 'subscription_confirmation',
            PaymentFailed::class => 'payment_failed',
            BookingCreated::class => 'booking_confirmation',
            RetentionTriggered::class => 'retention_email',
            default => '',
        };
    }

    protected function getDataForEvent(object $event): array
    {
        $user = $event->user;
        $data = [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'tenant_name' => $user->tenant?->name ?? 'Vores Gym',
        ];

        if (property_exists($event, 'plan')) {
            $data['plan_name'] = $event->plan?->name ?? 'Abonnement';
        }

        if (property_exists($event, 'gymClass')) {
            $data['class_name'] = $event->gymClass?->classType?->name ?? 'Træning';
            $data['class_date'] = ($event->gymClass?->start_at instanceof \DateTimeInterface)
                ? $event->gymClass->start_at->format('d-m-Y H:i')
                : (string) $event->gymClass?->start_at;
        }

        return $data;
    }
}
