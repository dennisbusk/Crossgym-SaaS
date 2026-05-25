<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Retention;
use App\Http\Resources\V1\RetentionResource;

class RetentionController extends BaseApiController
{
    protected string $model = Retention::class;
    protected string $resource = RetentionResource::class;
}
