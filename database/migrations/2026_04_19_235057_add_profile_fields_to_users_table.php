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
            $table->string('medlemsnummer')->nullable()->index();
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->date('birthday')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('sex')->nullable();
            $table->date('joined_at')->nullable();
            $table->date('left_at')->nullable();
            $table->boolean('is_approved_for_closed_classes')->default(false);
            $table->string('image')->nullable();
            $table->string('old_user_id')->nullable()->index(); // Til at hjælpe med import-mapping
            $table->string('old_member_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'medlemsnummer',
                'address',
                'postal_code',
                'city',
                'birthday',
                'phone',
                'mobile',
                'sex',
                'joined_at',
                'left_at',
                'is_approved_for_closed_classes',
                'image',
                'old_user_id',
                'old_member_id',
            ]);
        });
    }
};
