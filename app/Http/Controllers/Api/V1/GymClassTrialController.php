<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\GymClassTrialResource;
use App\Models\GymClassTrial;

class GymClassTrialController extends BaseApiController
{
    protected string $model = GymClassTrial::class;

    protected string $resource = GymClassTrialResource::class;
}
