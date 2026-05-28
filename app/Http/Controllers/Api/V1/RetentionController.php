<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\RetentionResource;
use App\Models\Retention;

class RetentionController extends BaseApiController
{
    protected string $model = Retention::class;

    protected string $resource = RetentionResource::class;
}
