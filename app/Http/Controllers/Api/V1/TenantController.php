<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Http\Resources\V1\TenantResource;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenants = Tenant::paginate();
        return TenantResource::collection($tenants);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants',
            'app_name' => 'nullable|string|max:255',
        ]);

        $tenant = Tenant::create($validated);

        return new TenantResource($tenant);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant)
    {
        return new TenantResource($tenant);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'domain' => 'sometimes|string|max:255|unique:tenants,domain,' . $tenant->id,
            'app_name' => 'sometimes|nullable|string|max:255',
        ]);

        $tenant->update($validated);

        return new TenantResource($tenant);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        $tenant->delete();

        return response()->json(null, 204);
    }
}
