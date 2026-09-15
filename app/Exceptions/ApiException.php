<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Exception;

class ApiException extends Exception
{
    protected int $defaultStatus = 400;

    public function __construct(string $message = '', int $code = 0)
    {
        parent::__construct($message, $code ?: $this->defaultStatus);
    }

    public function render()
    {
        return ApiResponse::error(
            $this->getMessage(),
            $this->getCode() ?: $this->defaultStatus
        );
    }
}
