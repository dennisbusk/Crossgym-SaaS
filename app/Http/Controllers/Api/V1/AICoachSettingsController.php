<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\AICoachSettingsResource;
use App\Models\AICoachSettings;

class AICoachSettingsController extends BaseApiController
{
    protected string $model = AICoachSettings::class;

    protected string $resource = AICoachSettingsResource::class;
}
