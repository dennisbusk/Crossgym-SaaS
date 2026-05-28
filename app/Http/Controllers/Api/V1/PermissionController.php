<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\PermissionResource;
use App\Models\Permission;

class PermissionController extends BaseApiController
{
    protected string $model = Permission::class;

    protected string $resource = PermissionResource::class;
}
