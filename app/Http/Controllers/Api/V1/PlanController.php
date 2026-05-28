<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\PlanResource;
use App\Models\Plan;

class PlanController extends BaseApiController
{
    protected string $model = Plan::class;

    protected string $resource = PlanResource::class;
}
