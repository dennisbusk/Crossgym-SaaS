<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('payments');
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_payment_intent_id')->index();
            $table->string('stripe_session_id')->nullable()->index();
            $table->integer('amount');
            $table->string('currency', 10)->default('DKK');
            $table->string('status')->nullable();
            $table->enum('type', ['payment','refund','partial_refund'])->default('payment');
            $table->timestamps();
            $table->unique(['tenant_id','stripe_payment_intent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
