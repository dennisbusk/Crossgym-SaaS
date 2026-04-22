<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('subscriptions', 'plan_type')) {
                $table->string('plan_type')->nullable()->after('cancel_at_period_end');
            }
            if (! Schema::hasColumn('subscriptions', 'credits_remaining')) {
                $table->integer('credits_remaining')->default(0)->after('plan_type');
            }
            if (! Schema::hasColumn('subscriptions', 'last_credit_reset_at')) {
                $table->timestamp('last_credit_reset_at')->nullable()->after('credits_remaining');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable()->after('remember_token')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'last_credit_reset_at')) {
                $table->dropColumn('last_credit_reset_at');
            }
            if (Schema::hasColumn('subscriptions', 'credits_remaining')) {
                $table->dropColumn('credits_remaining');
            }
            if (Schema::hasColumn('subscriptions', 'plan_type')) {
                $table->dropColumn('plan_type');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'stripe_customer_id')) {
                $table->dropColumn('stripe_customer_id');
            }
        });
    }
};
