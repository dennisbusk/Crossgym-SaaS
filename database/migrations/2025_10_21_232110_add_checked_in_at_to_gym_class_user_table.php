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
        Schema::table('gym_class_user', function (Blueprint $table) {
            if(!Schema::hasColumn('gym_class_user', 'checked_in_at'))
            $table->timestamp('checked_in_at')->nullable()->after('created_at');
            $table->index(
                ['gym_class_id', 'user_id', 'checked_in_at'],
                'gym_class_user_checked_in_at_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gym_class_user', function (Blueprint $table) {
            $table->dropColumn('checked_in_at');
        });
    }
};
