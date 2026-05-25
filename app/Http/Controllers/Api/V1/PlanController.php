<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Plan;
use App\Http\Resources\V1\PlanResource;

class PlanController extends BaseApiController
{
    protected string $model = Plan::class;
    protected string $resource = PlanResource::class;
}
