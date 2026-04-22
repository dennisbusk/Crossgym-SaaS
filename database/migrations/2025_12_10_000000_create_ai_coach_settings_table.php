<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_coach_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->json('equipment');
            $table->json('intensity');
            $table->json('focus_area');
            $table->json('difficulty');
            $table->unsignedSmallInteger('duration_min');
            $table->unsignedSmallInteger('duration_max');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_coach_settings');
    }
};
