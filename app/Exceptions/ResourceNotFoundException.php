<?php

namespace App\Exceptions;

class ResourceNotFoundException extends ApiException
{
    public function __construct(string $resource = 'Resource', array $data = [])
    {
        parent::__construct("{$resource} not found", 404, $data);
    }
}
