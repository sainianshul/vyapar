<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Exception;

/**
 * Base exception for all API exceptions.
 * Every child exception automatically renders a consistent JSON error response.
 *
 * Usage:
 *   throw new ApiException('Something went wrong.', 400);
 *
 * Child classes can override $defaultStatus to set a fixed HTTP status code.
 */
class ApiException extends Exception
{
    protected int $defaultStatus = 400;

    public function __construct(string $message = '', int $code = 0)
    {
        parent::__construct($message, $code ?: $this->defaultStatus);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render()
    {
        return ApiResponse::error(
            $this->getMessage(),
            $this->getCode() ?: $this->defaultStatus
        );
    }
}
