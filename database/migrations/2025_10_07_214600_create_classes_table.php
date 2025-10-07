<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->json('name');
            $table->json('description');
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_type_id')->constrained('class_types')->cascadeOnDelete();
            $table->unsignedInteger('max_participants');
            $table->dateTime('class_start');
            $table->dateTime('class_end');
            $table->string('recurring_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
