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
        Schema::table('check_ins', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->boolean('is_paid')->default(false)->after('user_id');
            $table->string('charge_id')->nullable()->after('is_paid');
            $table->timestamp('checked_at')->nullable()->after('charge_id');
            $table->foreignId('gym_class_id')->nullable()->after('checked_at')->constrained('classes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropForeign(['gym_class_id']);
            $table->dropColumn(['is_paid', 'charge_id', 'checked_at', 'gym_class_id']);
        });
    }
};
