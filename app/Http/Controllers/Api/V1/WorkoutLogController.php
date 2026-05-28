<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\WorkoutLogResource;
use App\Models\WorkoutLog;
use Illuminate\Http\Request;

class WorkoutLogController extends BaseApiController
{
    protected string $model = WorkoutLog::class;

    protected string $resource = WorkoutLogResource::class;

    public function index(Request $request)
    {
        $query = WorkoutLog::query();

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('exercise_id')) {
            $query->where('exercise_id', $request->exercise_id);
        }

        if ($request->has('from_date')) {
            $query->where('date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('date', '<=', $request->to_date);
        }

        return WorkoutLogResource::collection($query->get());
    }
}
