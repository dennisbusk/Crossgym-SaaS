<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Dashboard;
use App\Http\Resources\V1\DashboardResource;

class DashboardController extends BaseApiController
{
    protected string $model = Dashboard::class;
    protected string $resource = DashboardResource::class;
}
