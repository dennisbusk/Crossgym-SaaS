<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AICoachSettings;
use App\Http\Resources\V1\AICoachSettingsResource;

class AICoachSettingsController extends BaseApiController
{
    protected string $model = AICoachSettings::class;
    protected string $resource = AICoachSettingsResource::class;
}
