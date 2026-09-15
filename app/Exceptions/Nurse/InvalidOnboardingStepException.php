<?php

namespace App\Exceptions\Nurse;

use App\Exceptions\ApiException;

class InvalidOnboardingStepException extends ApiException
{
    protected int $defaultStatus = 422;
}
