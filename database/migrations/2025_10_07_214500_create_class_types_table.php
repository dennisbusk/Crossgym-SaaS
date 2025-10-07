<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('class_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('color');
            $table->string('image')->nullable();
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_types');
    }
};
