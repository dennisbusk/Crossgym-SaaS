<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SuperAdmin;
use App\Http\Resources\V1\SuperAdminResource;

class SuperAdminController extends BaseApiController
{
    protected string $model = SuperAdmin::class;
    protected string $resource = SuperAdminResource::class;
}
