<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('plans');
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            // Allow null for global plans (shared across tenants), as relied upon by tests
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('stripe_price_id')->unique();
            $table->string('name');
            $table->integer('amount');
            $table->string('currency', 10)->default('DKK');
            $table->string('interval', 20)->default('month');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
