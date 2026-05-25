<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\GymClassTrial;
use App\Http\Resources\V1\GymClassTrialResource;

class GymClassTrialController extends BaseApiController
{
    protected string $model = GymClassTrial::class;
    protected string $resource = GymClassTrialResource::class;
}
