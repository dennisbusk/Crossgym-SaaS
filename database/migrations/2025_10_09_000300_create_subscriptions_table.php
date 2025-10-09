<?php

declare( strict_types=1 );

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('subscriptions', function ( Blueprint $table ) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('stripe_subscription_id')->index();
            $table->string('stripe_price_id')->index();
            $table->string('status')->default('active');
            $table->timestamp('current_period_end')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('subscriptions');
    }
};
