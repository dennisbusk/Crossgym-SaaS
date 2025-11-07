<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscription_options', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['transaction_fee', 'member_fee']);
            $table->decimal('value', 8, 3);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Seed defaults
        DB::table('subscription_options')->insert([
            [
                'name' => '.5% of each transaction',
                'type' => 'transaction_fee',
                'value' => 0.5,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '2kr per active member',
                'type' => 'member_fee',
                'value' => 2.000,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_options');
    }
};
