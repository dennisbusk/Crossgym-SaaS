<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gym_class_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['gym_class_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_class_user');
    }
};
