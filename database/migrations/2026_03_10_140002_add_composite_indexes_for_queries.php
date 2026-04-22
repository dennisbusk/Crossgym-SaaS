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
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'stripe_price_id']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->index(['tenant_id', 'class_start']);
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'user_id']);
            $table->dropIndex(['tenant_id', 'stripe_price_id']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'class_start']);
        });
    }
};
