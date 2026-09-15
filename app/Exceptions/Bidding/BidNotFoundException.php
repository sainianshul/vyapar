<?php

namespace App\Exceptions\Bidding;

use App\Exceptions\ApiException;

class BidNotFoundException extends ApiException
{
    protected int $defaultStatus = 404;
}
