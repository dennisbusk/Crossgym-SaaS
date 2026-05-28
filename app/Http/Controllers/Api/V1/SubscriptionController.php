<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\SubscriptionResource;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends BaseApiController
{
    protected string $model = Subscription::class;

    protected string $resource = SubscriptionResource::class;

    public function index(Request $request)
    {
        $query = Subscription::query();

        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        return SubscriptionResource::collection($query->get());
    }
}
