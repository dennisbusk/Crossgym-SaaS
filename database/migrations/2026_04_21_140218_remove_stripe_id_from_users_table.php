<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Move data from stripe_id to stripe_customer_id if stripe_customer_id is empty
        DB::table('users')
            ->whereNotNull('stripe_id')
            ->where(function ($query) {
                $query->whereNull('stripe_customer_id')
                    ->orWhere('stripe_customer_id', '');
            })
            ->update(['stripe_customer_id' => DB::raw('stripe_id')]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('stripe_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_id')->nullable()->after('password');
        });

        DB::table('users')
            ->whereNotNull('stripe_customer_id')
            ->update(['stripe_id' => DB::raw('stripe_customer_id')]);
    }
};
