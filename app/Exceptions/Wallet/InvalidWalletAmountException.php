<?php

namespace App\Exceptions\Wallet;

use App\Exceptions\ApiException;

class InvalidWalletAmountException extends ApiException
{
    protected int $defaultStatus = 422;
}
