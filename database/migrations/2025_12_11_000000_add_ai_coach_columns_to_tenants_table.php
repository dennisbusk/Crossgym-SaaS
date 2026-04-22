<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('ai_coach_stripe_subscription_id')->nullable()->after('onboarded_at');
            $table->string('ai_coach_stripe_price_id')->nullable()->after('ai_coach_stripe_subscription_id');
            $table->timestamp('ai_coach_enabled_at')->nullable()->after('ai_coach_stripe_price_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'ai_coach_stripe_subscription_id',
                'ai_coach_stripe_price_id',
                'ai_coach_enabled_at',
            ]);
        });
    }
};
