<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\ProcessedStripeEventResource;
use App\Models\ProcessedStripeEvent;

class ProcessedStripeEventController extends BaseApiController
{
    protected string $model = ProcessedStripeEvent::class;

    protected string $resource = ProcessedStripeEventResource::class;
}
