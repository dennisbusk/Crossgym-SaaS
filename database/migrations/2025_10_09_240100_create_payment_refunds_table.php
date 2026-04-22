<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('payment_intent_id');
            $table->unsignedBigInteger('amount');
            $table->string('currency', 8)->default('DKK');
            $table->string('reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'payment_intent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_refunds');
    }
};
