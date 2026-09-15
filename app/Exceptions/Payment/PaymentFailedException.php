<?php

namespace App\Exceptions\Payment;

use App\Exceptions\ApiException;

class PaymentFailedException extends ApiException
{
    protected int $defaultStatus = 502;
}
