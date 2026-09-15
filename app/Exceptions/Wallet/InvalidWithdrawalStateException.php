<?php

namespace App\Exceptions\Wallet;

use App\Exceptions\ApiException;

class InvalidWithdrawalStateException extends ApiException
{
    protected int $defaultStatus = 409;
}
