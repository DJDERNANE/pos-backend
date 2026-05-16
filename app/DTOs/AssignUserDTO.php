<?php

namespace App\DTOs;

readonly class AssignUserDTO
{
    public function __construct(
        public string $user_id,
        public string $role,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            user_id: $data['user_id'],
            role: $data['role'],
        );
    }
}
