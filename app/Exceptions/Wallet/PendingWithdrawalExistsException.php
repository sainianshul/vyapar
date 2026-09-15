<?php

namespace App\Exceptions\Wallet;

use App\Exceptions\ApiException;

class PendingWithdrawalExistsException extends ApiException
{
    protected int $defaultStatus = 409;
}
