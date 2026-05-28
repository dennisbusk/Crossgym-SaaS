<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ClassTypeResource;
use App\Models\ClassType;
use Illuminate\Http\Request;

class ClassTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ClassType::query();

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('slug', 'like', "%{$searchTerm}%");
            });
        }

        $types = $query->get();

        return ClassTypeResource::collection($types);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|array',
            'slug' => 'required|string|unique:class_types',
            'price' => 'nullable|integer',
            'description' => 'nullable|array',
        ]);

        $type = ClassType::create($validated);

        return new ClassTypeResource($type);
    }

    /**
     * Display the specified resource.
     */
    public function show(ClassType $classType)
    {
        return new ClassTypeResource($classType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClassType $classType)
    {
        $validated = $request->validate([
            'name' => 'sometimes|array',
            'slug' => 'sometimes|string|unique:class_types,slug,'.$classType->id,
            'price' => 'sometimes|nullable|integer',
            'description' => 'sometimes|nullable|array',
        ]);

        $classType->update($validated);

        return new ClassTypeResource($classType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassType $classType)
    {
        $classType->delete();

        return response()->json(null, 204);
    }
}
