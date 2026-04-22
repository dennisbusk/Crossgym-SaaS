<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processed_stripe_events', function (Blueprint $table) {
            $table->string('event_id', 255)->primary();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_stripe_events');
    }
};
