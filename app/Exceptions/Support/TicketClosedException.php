<?php

namespace App\Exceptions\Support;

use App\Exceptions\ApiException;

class TicketClosedException extends ApiException
{
    protected int $defaultStatus = 409;

    public function __construct(string $message = 'This support ticket is closed and cannot be modified.')
    {
        parent::__construct($message, $this->defaultStatus);
    }
}
