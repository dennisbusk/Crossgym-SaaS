<?php

namespace App\Http\Controllers;

use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class SuperadminController extends Controller
{
    public function switchTenant(Request $request)
    {
        if (! $request->crypt_key) {
            abort(403, 'Invalid or expired token.');
        }
        $key = Crypt::decrypt($request->crypt_key);
        if (Carbon::parse($key)->isPast()) {
            abort(403, 'Invalid or expired token.');
        }

        // Find superadmin by email (or central auth mapping)
        $superadmin = \App\Models\User::withoutGlobalScopes()->where('email', $request->email)->whereHas('role', function ($query) {
            $query->where('slug', 'superadmin');
        })->first();
        if (! $superadmin) {
            abort(403, 'Superadmin not found.');
        }
        if ($superadmin->tenant_id != $request->tenant_id) {
            $superadmin->update(['tenant_id' => $request->tenant_id]);
        }
        // Log in as superadmin on this tenant
        Auth::login($superadmin);

        return redirect('/dashboard');
    }
}
