<?php

namespace App\Exceptions\Nurse;

use App\Exceptions\ApiException;

class NurseNotApprovedException extends ApiException
{
    protected int $defaultStatus = 403;

    public function __construct()
    {
        parent::__construct('Your profile is not approved. You cannot perform this action.', 403);
    }
}
