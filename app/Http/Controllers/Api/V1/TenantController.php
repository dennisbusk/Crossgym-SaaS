<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TenantResource;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Tenant::query();

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('domain', 'like', "%{$searchTerm}%");
            });
        }

        $tenants = $query->get();

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

    public function occupancy(Request $request, Tenant $tenant)
    {
        // For simulation, we'll calculate based on recent check-ins within the last 2 hours
        $currentCount = \App\Models\CheckIn::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', now()->subHours(2))
            ->count();

        $capacity = 60; // Default capacity
        $occupancyPercent = min(100, (int) round(($currentCount / $capacity) * 100));

        $status = 'quiet';
        if ($occupancyPercent > 80) {
            $status = 'busy';
        } elseif ($occupancyPercent > 40) {
            $status = 'moderate';
        }

        return response()->json([
            'occupancy_percent' => $occupancyPercent,
            'status' => $status,
            'current_count' => $currentCount,
            'capacity' => $capacity,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'domain' => 'sometimes|string|max:255|unique:tenants,domain,'.$tenant->id,
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
