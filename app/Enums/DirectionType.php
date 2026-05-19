<?php

declare(strict_types=1);

namespace App\Enums;

enum DirectionType: string
{
    case IN = 'in';
    case OUT = 'out';

    public function label(): string
    {
        return match ($this) {
            self::IN => 'In',
            self::OUT => 'Out',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
