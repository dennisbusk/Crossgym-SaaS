<?php

namespace App\Services;

use App\Models\Template;
use App\Models\Tenant;
use Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class TenantResolver
{
    public static function resolve($domain): void
    {
        if (Schema::hasTable('tenants')) {
            if (! is_string($domain) && get_class($domain) == Tenant::class) {
                $tenant = $domain;
            } else {
                $tenant = Tenant::where('domain', $domain)->first();
            }
            if ($tenant) {
                //                if (Auth::check() && ! Auth::user()->superAdmin && Auth::user()->tenant_id != $tenant->id) {
                //                    Auth::user()->update(['tenant_id' => $tenant->id]);
                //                }
                Session::put([
                    'tenant' => $tenant,
                    'tenant_id' => $tenant->id,
                    //                    'payment_method' => $tenant->paymentMethod,
                    //                    'template' => Template::find($tenant->template_id),
                ]);
                Config::set([
                    'app.name' => $tenant->name ?? '',
                    //                    'app.payment_enabled' => $tenant->payment_enabled ? true : false,
                    //                    'app.payment_gateway' => $tenant->payment_gateway ?? 'stripe',
                    //                    'app.payment_testmode' => $tenant->payment_testmode ?? false,
                    //                    'app.easyaccess.enabled' => $tenant->easyaccess_enabled ?? false,
                    //                    'app.easyaccess.username' => $tenant->easyaccess_username ?? null,
                    //                    'app.easyaccess.password' => $tenant->easyaccess_password ?? null,
                    //                    'app.debug' => $tenant->debug ? true : false,
                    //                    'app.admin_mail' => $tenant->admin ?? '',
                    //                    'app.env' => $tenant->environment ?? 'production',
                ]);
                Config::set('session.domain', $tenant->domain);
                //            Config::set('filesystems.disks.public', [
                //                'driver' => 'local',
                //                'root' => storage_path('app/public/tenants/'.$tenant->id),
                //                'url' => 'https://'.$tenant->domain.'/storage/tenants/'.$tenant->id,
                //                'visibility' => 'public',
                //            ]);
                //            Config::set('filesystems.disks.local.root',storage_path('app/tenants/'.$tenant->id));

            }
        }
    }
}
