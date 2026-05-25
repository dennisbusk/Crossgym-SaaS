<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GymClass;
use App\Http\Resources\V1\GymClassResource;
use Illuminate\Http\Request;

class GymClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classes = GymClass::paginate();
        return GymClassResource::collection($classes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|array',
            'description' => 'nullable|array',
            'trainer_id' => 'nullable|exists:users,id',
            'class_type_id' => 'required|exists:class_types,id',
            'max_participants' => 'required|integer|min:1',
            'class_start' => 'required|date',
            'class_end' => 'required|date|after:class_start',
            'all_day_event' => 'boolean',
            'featured' => 'boolean',
        ]);

        $class = GymClass::create($validated);

        return new GymClassResource($class);
    }

    /**
     * Display the specified resource.
     */
    public function show(GymClass $gymClass)
    {
        return new GymClassResource($gymClass);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GymClass $gymClass)
    {
        $validated = $request->validate([
            'name' => 'sometimes|array',
            'description' => 'sometimes|array',
            'trainer_id' => 'sometimes|nullable|exists:users,id',
            'class_type_id' => 'sometimes|exists:class_types,id',
            'max_participants' => 'sometimes|integer|min:1',
            'class_start' => 'sometimes|date',
            'class_end' => 'sometimes|date|after:class_start',
            'all_day_event' => 'sometimes|boolean',
            'featured' => 'sometimes|boolean',
        ]);

        $gymClass->update($validated);

        return new GymClassResource($gymClass);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GymClass $gymClass)
    {
        $gymClass->delete();

        return response()->json(null, 204);
    }
}
