<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\CalendarResource;
use App\Models\Calendar;

class CalendarController extends BaseApiController
{
    protected string $model = Calendar::class;

    protected string $resource = CalendarResource::class;
}
