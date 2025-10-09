<?php

declare( strict_types=1 );

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('plans', function ( Blueprint $table ) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('stripe_price_id')->unique();
            $table->string('name');
            $table->unsignedBigInteger('amount');
            $table->string('currency', 10)->default('usd');
            $table->string('interval');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('plans');
    }
};
