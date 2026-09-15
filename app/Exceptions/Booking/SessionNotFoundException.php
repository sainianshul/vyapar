<?php

namespace App\Exceptions\Booking;

use App\Exceptions\ApiException;

class SessionNotFoundException extends ApiException
{
    protected int $defaultStatus = 404;
}
