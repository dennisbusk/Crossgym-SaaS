<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_coach_settings', function (Blueprint $table) {
            $table->string('ai_provider')->nullable()->after('duration_max');
            $table->text('ai_api_key')->nullable()->after('ai_provider');
        });
    }

    public function down(): void
    {
        Schema::table('ai_coach_settings', function (Blueprint $table) {
            $table->dropColumn(['ai_provider', 'ai_api_key']);
        });
    }
};
