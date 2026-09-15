<?php

namespace App\Exceptions\CareRequest;

use App\Exceptions\ApiException;

class DuplicateBookingException extends ApiException
{
    protected int $defaultStatus = 409;
}
