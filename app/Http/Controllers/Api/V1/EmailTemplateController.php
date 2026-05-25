<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\EmailTemplate;
use App\Http\Resources\V1\EmailTemplateResource;

class EmailTemplateController extends BaseApiController
{
    protected string $model = EmailTemplate::class;
    protected string $resource = EmailTemplateResource::class;
}
