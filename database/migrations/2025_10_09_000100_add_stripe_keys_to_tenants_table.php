<?php

declare( strict_types=1 );

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::table('tenants', function ( Blueprint $table ) {
            $table->string('stripe_public_key')->nullable()->after('domain');
            $table->string('stripe_secret_key')->nullable()->after('stripe_public_key');
            $table->string('stripe_webhook_secret')->nullable()->after('stripe_secret_key');
        });
    }

    public function down(): void {
        Schema::table('tenants', function ( Blueprint $table ) {
            $table->dropColumn([ 'stripe_public_key', 'stripe_secret_key', 'stripe_webhook_secret' ]);
        });
    }
};
