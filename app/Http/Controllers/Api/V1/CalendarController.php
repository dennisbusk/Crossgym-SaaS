<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Calendar;
use App\Http\Resources\V1\CalendarResource;

class CalendarController extends BaseApiController
{
    protected string $model = Calendar::class;
    protected string $resource = CalendarResource::class;
}
