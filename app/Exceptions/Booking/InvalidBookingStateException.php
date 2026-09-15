<?php

namespace App\Exceptions\Booking;

use App\Exceptions\ApiException;

class InvalidBookingStateException extends ApiException
{
    protected int $defaultStatus = 409;
}
