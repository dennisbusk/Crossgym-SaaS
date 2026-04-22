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
            if (! Schema::hasColumn('tenants', 'stripe_public_key')) {
                $table->string('stripe_public_key')->nullable()->after('domain');
            }
            if (! Schema::hasColumn('tenants', 'stripe_secret_key')) {
                $table->string('stripe_secret_key')->nullable()->after('stripe_public_key');
            }
            if (! Schema::hasColumn('tenants', 'stripe_webhook_secret')) {
                $table->string('stripe_webhook_secret')->nullable()->after('stripe_secret_key');
            }
            if (! Schema::hasColumn('tenants', 'stripe_connect_account_id')) {
                $table->string('stripe_connect_account_id')->nullable()->after('stripe_webhook_secret')->index();
            }
            if (! Schema::hasColumn('tenants', 'stripe_connect_refresh_token')) {
                $table->string('stripe_connect_refresh_token')->nullable()->after('stripe_connect_account_id');
            }
            if (! Schema::hasColumn('tenants', 'stripe_connect_access_token')) {
                $table->string('stripe_connect_access_token')->nullable()->after('stripe_connect_refresh_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_public_key',
                'stripe_secret_key',
                'stripe_webhook_secret',
                'stripe_connect_account_id',
                'stripe_connect_refresh_token',
                'stripe_connect_access_token',
            ]);
        });
    }
};
