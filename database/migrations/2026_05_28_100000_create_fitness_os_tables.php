<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Health Metrics (Wearable data)
        Schema::create('health_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // steps, calories, sleep_hours, sleep_quality, hrv, rhr, etc.
            $table->decimal('value', 12, 2);
            $table->date('date');
            $table->string('source')->nullable(); // apple_health, google_fit, garmin, polar
            $table->timestamps();

            $table->unique(['user_id', 'type', 'date']);
        });

        // 2. Challenges
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->json('name');
            $table->json('description');
            $table->string('type'); // individual, gym_wide, seasonal
            $table->string('goal_type'); // workouts_count, volume_kg, points, check_ins
            $table->decimal('goal_value', 12, 2);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('challenge_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('current_value', 12, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['challenge_id', 'user_id']);
        });

        // 3. Fist Bumps (Reactions)
        Schema::create('fist_bumps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('bumpable'); // WorkoutLog, Achievement, etc.
            $table->timestamps();

            $table->unique(['user_id', 'bumpable_type', 'bumpable_id'], 'fist_bumps_unique');
        });

        // 4. Update Users with Recovery data
        Schema::table('users', function (Blueprint $table) {
            $table->integer('recovery_score')->nullable();
            $table->integer('last_hrv')->nullable();
            $table->integer('last_rhr')->nullable();
            $table->integer('last_sleep_score')->nullable();
            $table->timestamp('recovery_updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['recovery_score', 'last_hrv', 'last_rhr', 'last_sleep_score', 'recovery_updated_at']);
        });
        Schema::dropIfExists('fist_bumps');
        Schema::dropIfExists('challenge_user');
        Schema::dropIfExists('challenges');
        Schema::dropIfExists('health_metrics');
    }
};
