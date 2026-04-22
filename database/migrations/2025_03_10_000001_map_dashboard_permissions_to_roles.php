<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This migration may run before the permission tables on a fresh install.
        // In that case there is nothing to map yet.
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('permission_role')) {
            return;
        }

        $viewTenantStats = DB::table('permissions')
            ->where('model', 'User')
            ->where('ability', 'view_tenant_stats')
            ->value('id');

        $viewStripeStatus = DB::table('permissions')
            ->where('model', 'User')
            ->where('ability', 'view_stripe_status')
            ->value('id');

        $dashboardPerms = DB::table('permissions')
            ->where('model', 'Dashboard')
            ->whereIn('ability', [
                'view_revenue',
                'view_bookings',
                'view_subscribers',
                'view_stripe_status',
                'view_export',
            ])
            ->pluck('id', 'ability')
            ->all();

        if (empty($dashboardPerms)) {
            return;
        }

        $tenantStatsRoles = $viewTenantStats
            ? DB::table('permission_role')->where('permission_id', $viewTenantStats)->pluck('role_id')->unique()
            : collect();

        $stripeStatusRoles = $viewStripeStatus
            ? DB::table('permission_role')->where('permission_id', $viewStripeStatus)->pluck('role_id')->unique()
            : collect();

        $toAttach = [];

        foreach ($tenantStatsRoles as $roleId) {
            foreach (['view_revenue', 'view_bookings', 'view_subscribers'] as $ability) {
                $permId = $dashboardPerms[$ability] ?? null;
                if ($permId) {
                    $toAttach[] = ['role_id' => $roleId, 'permission_id' => $permId];
                }
            }
        }

        foreach ($stripeStatusRoles as $roleId) {
            foreach (['view_stripe_status', 'view_export'] as $ability) {
                $permId = $dashboardPerms[$ability] ?? null;
                if ($permId) {
                    $toAttach[] = ['role_id' => $roleId, 'permission_id' => $permId];
                }
            }
        }

        if (empty($toAttach)) {
            return;
        }

        $existing = DB::table('permission_role')
            ->whereIn('role_id', collect($toAttach)->pluck('role_id')->unique()->all())
            ->whereIn('permission_id', collect($toAttach)->pluck('permission_id')->unique()->all())
            ->get()
            ->mapWithKeys(fn ($r) => ["{$r->role_id}-{$r->permission_id}" => true]);

        foreach ($toAttach as $row) {
            $key = "{$row['role_id']}-{$row['permission_id']}";
            if (! isset($existing[$key])) {
                DB::table('permission_role')->insertOrIgnore([
                    'role_id' => $row['role_id'],
                    'permission_id' => $row['permission_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // No-op: we don't remove Dashboard permissions on rollback
        // to avoid breaking roles that were manually configured.
    }
};
