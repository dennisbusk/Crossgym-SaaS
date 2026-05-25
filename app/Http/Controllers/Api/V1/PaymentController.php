<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Payment;
use App\Http\Resources\V1\PaymentResource;

class PaymentController extends BaseApiController
{
    protected string $model = Payment::class;
    protected string $resource = PaymentResource::class;
}
