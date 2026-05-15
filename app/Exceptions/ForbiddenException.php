<?php

namespace App\Exceptions;

class ForbiddenException extends ApiException
{
    public function __construct(string $message = 'Forbidden', array $data = [])
    {
        parent::__construct($message, 403, $data);
    }
}
