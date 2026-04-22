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
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_id')->nullable()->after('password');
            $table->string('card_brand')->nullable()->after('stripe_id');
            $table->string('card_last_four')->nullable()->after('card_brand');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->boolean('all_day_event')->default(false)->after('class_end');
            $table->boolean('featured')->default(false)->after('all_day_event');
        });

        Schema::table('class_types', function (Blueprint $table) {
            $table->integer('price')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['stripe_id', 'card_brand', 'card_last_four']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn(['all_day_event', 'featured']);
        });

        Schema::table('class_types', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
