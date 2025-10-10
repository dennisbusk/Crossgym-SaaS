<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'stripe_connect_email')) {
                $table->string('stripe_connect_email')->nullable()->after('stripe_connect_access_token');
            }
            if (!Schema::hasColumn('tenants', 'stripe_connect_onboarded')) {
                $table->boolean('stripe_connect_onboarded')->default(false)->after('stripe_connect_email');
            }
            if (!Schema::hasColumn('tenants', 'stripe_connect_charges_enabled')) {
                $table->boolean('stripe_connect_charges_enabled')->default(false)->after('stripe_connect_onboarded');
            }
            if (!Schema::hasColumn('tenants', 'stripe_connect_payouts_enabled')) {
                $table->boolean('stripe_connect_payouts_enabled')->default(false)->after('stripe_connect_charges_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_connect_email',
                'stripe_connect_onboarded',
                'stripe_connect_charges_enabled',
                'stripe_connect_payouts_enabled',
            ]);
        });
    }
};
