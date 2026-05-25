<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SystemSetting;
use App\Http\Resources\V1\SystemSettingResource;

class SystemSettingController extends BaseApiController
{
    protected string $model = SystemSetting::class;
    protected string $resource = SystemSettingResource::class;
}
