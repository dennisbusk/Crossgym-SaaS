<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\ColorResource;
use App\Models\Color;

class ColorController extends BaseApiController
{
    protected string $model = Color::class;

    protected string $resource = ColorResource::class;
}
