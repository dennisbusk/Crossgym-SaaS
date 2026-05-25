<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SubscriptionOption;
use App\Http\Resources\V1\SubscriptionOptionResource;

class SubscriptionOptionController extends BaseApiController
{
    protected string $model = SubscriptionOption::class;
    protected string $resource = SubscriptionOptionResource::class;
}
