<?php

namespace App\Exceptions;

class UnauthorizedException extends ApiException
{
    public function __construct(string $message = 'Unauthorized', array $data = [])
    {
        parent::__construct($message, 401, $data);
    }
}
