<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Permission;
use App\Http\Resources\V1\PermissionResource;

class PermissionController extends BaseApiController
{
    protected string $model = Permission::class;
    protected string $resource = PermissionResource::class;
}
