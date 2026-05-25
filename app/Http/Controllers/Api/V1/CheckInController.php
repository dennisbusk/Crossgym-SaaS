<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CheckIn;
use App\Http\Resources\V1\CheckInResource;

class CheckInController extends BaseApiController
{
    protected string $model = CheckIn::class;
    protected string $resource = CheckInResource::class;
}
