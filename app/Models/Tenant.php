<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\TenantObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[ObservedBy([TenantObserver::class])]
class Tenant extends Model
{
    use HasFactory;
    use HasTranslations;

    public $translatable = ['terms'];

    protected $fillable
        = [
            'name',
            'domain',
            'stripe_public_key',
            'stripe_secret_key',
            'stripe_webhook_secret',
            'stripe_connect_account_id',
            'stripe_connect_refresh_token',
            'stripe_connect_access_token',
            'stripe_connect_email',
            'stripe_connect_onboarded',
            'stripe_connect_charges_enabled',
            'stripe_connect_payouts_enabled',
            'subscription_option_id',
            'onboarded_at',
            'ai_coach_stripe_subscription_id',
            'ai_coach_stripe_price_id',
            'ai_coach_enabled_at',
            'app_name',
            'icon_path',
            'theme_color',
            'background_color',
            'latitude',
            'longitude',
            'checkin_radius',
            'terms',
            'allow_member_billing_management',
        ];

    protected $casts = [
        'onboarded_at' => 'datetime',
        'ai_coach_enabled_at' => 'datetime',
    ];

    public function hasAICoach(): bool
    {
        return $this->ai_coach_enabled_at !== null
            && $this->ai_coach_stripe_subscription_id !== null;
    }

    /**
     * Get the users for the tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * The selected subscription option.
     */
    public function subscriptionOption(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SubscriptionOption::class);
    }

    public function hasSubscription(): bool
    {
        return ! is_null($this->subscription_option_id);
    }

    public function emailTemplates(): HasMany
    {
        return $this->hasMany(EmailTemplate::class);
    }

    public function aiCoachSettings(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(AICoachSettings::class);
    }
}
