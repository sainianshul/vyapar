<?php

namespace App\Exceptions\Support;

use App\Exceptions\ApiException;

class UnauthorizedTicketAccessException extends ApiException
{
    protected int $defaultStatus = 403;

    public function __construct(string $message = 'You are not authorized to access this support ticket.')
    {
        parent::__construct($message, $this->defaultStatus);
    }
}
