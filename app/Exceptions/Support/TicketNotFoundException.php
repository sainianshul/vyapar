<?php

namespace App\Exceptions\Support;

use App\Exceptions\ApiException;

class TicketNotFoundException extends ApiException
{
    protected int $defaultStatus = 404;

    public function __construct(string $message = 'Support ticket not found.')
    {
        parent::__construct($message, $this->defaultStatus);
    }
}
