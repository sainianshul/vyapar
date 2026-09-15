<?php

namespace App\Exceptions\CareRequest;

use App\Exceptions\ApiException;

class InvalidCareRequestStateException extends ApiException
{
    protected int $defaultStatus = 409;
}
