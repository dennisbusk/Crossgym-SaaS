<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\CheckInResource;
use App\Models\CheckIn;
use Illuminate\Http\Request;

class CheckInController extends BaseApiController
{
    protected string $model = CheckIn::class;

    protected string $resource = CheckInResource::class;

    public function index(Request $request)
    {
        $query = CheckIn::query();

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('from_date')) {
            $query->where('checked_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('checked_at', '<=', $request->to_date);
        }

        return CheckInResource::collection($query->get());
    }
}
