<?php

declare(strict_types=1);

namespace App\Enums;

enum CartStatus: string
{
    case ACTIVE = 'active';
    case CHECKED_OUT = 'checked_out';
    case ABANDONED = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::CHECKED_OUT => 'Checked Out',
            self::ABANDONED => 'Abandoned',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
