<?php

namespace App\Exceptions\Support;

use App\Exceptions\ApiException;

class InvalidCategoryException extends ApiException
{
    protected int $defaultStatus = 422;

    public function __construct(string $message = 'The selected support category is invalid or inactive.')
    {
        parent::__construct($message, $this->defaultStatus);
    }
}
