<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Color;
use App\Http\Resources\V1\ColorResource;

class ColorController extends BaseApiController
{
    protected string $model = Color::class;
    protected string $resource = ColorResource::class;
}
