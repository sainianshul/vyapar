<?php

namespace App\Exceptions\CareRequest;

use App\Exceptions\ApiException;

class CareRequestNotFoundException extends ApiException
{
    protected int $defaultStatus = 404;
}
