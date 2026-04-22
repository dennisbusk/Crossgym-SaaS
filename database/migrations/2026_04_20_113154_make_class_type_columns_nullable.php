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
        Schema::table('class_types', function (Blueprint $table) {
            $table->string('color')->nullable()->change();
            $table->json('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_types', function (Blueprint $table) {
            $table->string('color')->nullable(false)->change();
            $table->json('description')->nullable(false)->change();
        });
    }
};
