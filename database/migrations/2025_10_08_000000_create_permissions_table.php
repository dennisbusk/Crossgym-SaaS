<?php

declare( strict_types=1 );

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('permissions', function ( Blueprint $table ) {
            $table->id();
            $table->string('model');
            $table->string('ability');
            $table->unique([ 'model', 'ability' ]);
            $table->timestamps();
        });
        Schema::create('roles', function ( Blueprint $table ) {
            $table->dropColumn('permissions');
        });
    }

    public function down(): void {
        Schema::dropIfExists('permissions');
        Schema::create('roles', function ( Blueprint $table ) {
            $table->json('permissions')->nullable();
        });
    }
};
