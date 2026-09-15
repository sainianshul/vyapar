<?php

namespace App\Exceptions\Nurse;

use App\Exceptions\ApiException;

class NurseProfileException extends ApiException
{
    protected int $defaultStatus = 400;

    public function __construct(string $message = 'Failed to update profile.', int $code = 400)
    {
        parent::__construct($message, $code);
    }
}
