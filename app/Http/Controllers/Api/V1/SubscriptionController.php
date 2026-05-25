<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Subscription;
use App\Http\Resources\V1\SubscriptionResource;

class SubscriptionController extends BaseApiController
{
    protected string $model = Subscription::class;
    protected string $resource = SubscriptionResource::class;
}
