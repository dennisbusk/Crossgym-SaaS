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
        Schema::table('gym_class_trials', function (Blueprint $table) {
            $table->foreignId('check_in_id')->nullable()->after('name')->constrained('check_ins')->nullOnDelete();
            if (Schema::hasColumn('gym_class_trials', 'checked_in_at')) {
                $table->dropColumn('checked_in_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gym_class_trials', function (Blueprint $table) {
            $table->dropForeign(['check_in_id']);
            $table->dropColumn('check_in_id');
            $table->timestamp('checked_in_at')->nullable()->after('name');
        });
    }
};
