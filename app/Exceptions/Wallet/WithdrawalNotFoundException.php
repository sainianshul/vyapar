<?php

namespace App\Exceptions\Wallet;

use App\Exceptions\ApiException;

class WithdrawalNotFoundException extends ApiException
{
    protected int $defaultStatus = 404;
}
