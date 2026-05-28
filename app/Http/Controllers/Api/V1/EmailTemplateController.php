<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\EmailTemplateResource;
use App\Models\EmailTemplate;

class EmailTemplateController extends BaseApiController
{
    protected string $model = EmailTemplate::class;

    protected string $resource = EmailTemplateResource::class;
}
