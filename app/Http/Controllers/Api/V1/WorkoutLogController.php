<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\WorkoutLog;
use App\Http\Resources\V1\WorkoutLogResource;

class WorkoutLogController extends BaseApiController
{
    protected string $model = WorkoutLog::class;
    protected string $resource = WorkoutLogResource::class;
}
