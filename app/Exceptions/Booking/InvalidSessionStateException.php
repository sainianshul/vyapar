<?php

namespace App\Exceptions\Booking;

use App\Exceptions\ApiException;

class InvalidSessionStateException extends ApiException
{
    protected int $defaultStatus = 409;
}
