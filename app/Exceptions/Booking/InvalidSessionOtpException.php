<?php

namespace App\Exceptions\Booking;

use App\Exceptions\ApiException;

class InvalidSessionOtpException extends ApiException
{
    protected int $defaultStatus = 422;
}
