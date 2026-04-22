<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workout_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('exercise_id')->nullable()->constrained()->onDelete('set null');
            $table->date('date');
            $table->decimal('weight', 8, 2)->nullable();
            $table->integer('reps')->nullable();
            $table->integer('sets')->nullable();
            $table->decimal('distance', 8, 2)->nullable();
            $table->integer('duration')->nullable(); // in seconds
            $table->integer('intensity')->nullable(); // 1-10
            $table->string('mood')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_logs');
    }
};
