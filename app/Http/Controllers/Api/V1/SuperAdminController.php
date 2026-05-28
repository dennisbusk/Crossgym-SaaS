<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\SuperAdminResource;
use App\Models\SuperAdmin;

class SuperAdminController extends BaseApiController
{
    protected string $model = SuperAdmin::class;

    protected string $resource = SuperAdminResource::class;
}
