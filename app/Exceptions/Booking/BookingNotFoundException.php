<?php

namespace App\Exceptions\Booking;

use App\Exceptions\ApiException;

class BookingNotFoundException extends ApiException
{
    protected int $defaultStatus = 404;
}
