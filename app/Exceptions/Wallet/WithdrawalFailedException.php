<?php

namespace App\Exceptions\Wallet;

use App\Exceptions\ApiException;

class WithdrawalFailedException extends ApiException
{
    protected int $defaultStatus = 502;
}
