<?php

namespace App\Exceptions\Payment;

use App\Exceptions\ApiException;

class InvalidPaymentAmountException extends ApiException
{
    protected int $defaultStatus = 422;
}
