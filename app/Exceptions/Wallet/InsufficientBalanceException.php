<?php

namespace App\Exceptions\Wallet;

use App\Exceptions\ApiException;

class InsufficientBalanceException extends ApiException
{
    protected int $defaultStatus = 422;
}
