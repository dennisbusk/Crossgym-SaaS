<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->cascadeOnDelete()->after('id');
            // Name the index explicitly so we can safely drop it later
            $table->unique(['tenant_id', 'slug'], 'roles_tenant_id_slug_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        if (Schema::hasColumn('roles', 'tenant_id')) {
            // 1) Drop FK constraint first (it uses an index on tenant_id which can be the left-most of the unique index)
            try {
                Schema::table('roles', function (Blueprint $table) {
                    // Try the conventional name first
                    $table->dropForeign('roles_tenant_id_foreign');
                });
            } catch (\Throwable $e) {
                try {
                    Schema::table('roles', function (Blueprint $table) {
                        // Fallback: let Laravel infer by column
                        $table->dropForeign(['tenant_id']);
                    });
                } catch (\Throwable $e2) {
                    // Ignore if it does not exist
                }
            }

            // 2) Drop the unique index if it exists
            $indexExists = ! empty(DB::select("SHOW INDEX FROM `roles` WHERE Key_name = 'roles_tenant_id_slug_unique'"));
            if ($indexExists) {
                Schema::table('roles', function (Blueprint $table) {
                    $table->dropUnique('roles_tenant_id_slug_unique');
                });
            }

            // 3) Finally drop the column (and any remaining index on it)
            if (Schema::hasColumn('roles', 'tenant_id')) {
                Schema::table('roles', function (Blueprint $table) {
                    $table->dropColumn('tenant_id');
                });
            }
        }
    }
};
