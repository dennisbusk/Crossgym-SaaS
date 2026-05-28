<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class BaseApiController extends Controller
{
    protected string $model;

    protected string $resource;

    public function index(Request $request)
    {
        $query = $this->model::query();

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->has('search')) {
            $this->applySearch($query, $request->search);
        }

        return $this->resource::collection($query->get());
    }

    protected function applySearch($query, $searchTerm)
    {
        // Default implementation, can be overridden
        if (\Schema::hasColumn((new $this->model)->getTable(), 'name')) {
            $query->where('name', 'like', "%{$searchTerm}%");
        }
    }

    public function store(Request $request)
    {
        $item = $this->model::create($request->all());

        return new $this->resource($item);
    }

    public function show($id)
    {
        $item = $this->model::findOrFail($id);

        return new $this->resource($item);
    }

    public function update(Request $request, $id)
    {
        $item = $this->model::findOrFail($id);
        $item->update($request->all());

        return new $this->resource($item);
    }

    public function destroy($id)
    {
        $item = $this->model::findOrFail($id);
        $item->delete();

        return response()->json(null, 204);
    }
}
