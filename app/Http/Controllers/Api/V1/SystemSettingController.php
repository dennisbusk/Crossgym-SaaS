<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\SystemSettingResource;
use App\Models\SystemSetting;

class SystemSettingController extends BaseApiController
{
    protected string $model = SystemSetting::class;

    protected string $resource = SystemSettingResource::class;
}
