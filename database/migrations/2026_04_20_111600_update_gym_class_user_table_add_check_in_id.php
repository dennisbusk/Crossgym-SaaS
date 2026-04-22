<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gym_class_user', function (Blueprint $table) {
            $table->foreignId('check_in_id')->nullable()->after('user_id')->constrained('check_ins')->nullOnDelete();
            if (Schema::hasColumn('gym_class_user', 'checked_in_at')) {
                // Drop index first to avoid SQLite errors
                $table->dropIndex('gym_class_user_checked_in_at_index');
                $table->dropColumn('checked_in_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('gym_class_user', function (Blueprint $table) {
            $table->dropForeign(['check_in_id']);
            $table->dropColumn('check_in_id');
            $table->timestamp('checked_in_at')->nullable()->after('user_id');
        });
    }
};
