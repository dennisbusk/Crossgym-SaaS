<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\EmailLog;
use App\Http\Resources\V1\EmailLogResource;

class EmailLogController extends BaseApiController
{
    protected string $model = EmailLog::class;
    protected string $resource = EmailLogResource::class;
}
