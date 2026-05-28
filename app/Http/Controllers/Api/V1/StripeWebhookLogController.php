<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\StripeWebhookLogResource;
use App\Models\StripeWebhookLog;

class StripeWebhookLogController extends BaseApiController
{
    protected string $model = StripeWebhookLog::class;

    protected string $resource = StripeWebhookLogResource::class;
}
