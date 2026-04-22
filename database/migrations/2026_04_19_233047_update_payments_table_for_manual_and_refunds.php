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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('manual_payment_by')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('refunded_amount')->default(0);
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['manual_payment_by']);
            $table->dropColumn(['manual_payment_by', 'refunded_amount', 'notes']);
        });
    }
};
