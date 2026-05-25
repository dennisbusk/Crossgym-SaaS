<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\UserDashboardWidget;
use App\Http\Resources\V1\UserDashboardWidgetResource;

class UserDashboardWidgetController extends BaseApiController
{
    protected string $model = UserDashboardWidget::class;
    protected string $resource = UserDashboardWidgetResource::class;
}
