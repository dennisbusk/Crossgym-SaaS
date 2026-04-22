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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('app_name')->nullable();
            $table->string('icon_path')->nullable();
            $table->string('theme_color')->nullable()->default('#000000');
            $table->string('background_color')->nullable()->default('#ffffff');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['app_name', 'icon_path', 'theme_color', 'background_color']);
        });
    }
};
