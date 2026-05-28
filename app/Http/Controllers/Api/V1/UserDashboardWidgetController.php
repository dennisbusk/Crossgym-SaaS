<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\UserDashboardWidgetResource;
use App\Models\UserDashboardWidget;

class UserDashboardWidgetController extends BaseApiController
{
    protected string $model = UserDashboardWidget::class;

    protected string $resource = UserDashboardWidgetResource::class;
}
