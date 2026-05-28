<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\EmailLogResource;
use App\Models\EmailLog;

class EmailLogController extends BaseApiController
{
    protected string $model = EmailLog::class;

    protected string $resource = EmailLogResource::class;
}
