<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ProcessedStripeEvent;
use App\Http\Resources\V1\ProcessedStripeEventResource;

class ProcessedStripeEventController extends BaseApiController
{
    protected string $model = ProcessedStripeEvent::class;
    protected string $resource = ProcessedStripeEventResource::class;
}
