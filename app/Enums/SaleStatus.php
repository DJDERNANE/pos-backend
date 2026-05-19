<?php

declare(strict_types=1);

namespace App\Enums;

enum SaleStatus: string
{
    case COMPLETED = 'completed';
    case REFUNDED = 'refunded';
    case VOID = 'void';

    public function label(): string
    {
        return match ($this) {
            self::COMPLETED => 'Completed',
            self::REFUNDED => 'Refunded',
            self::VOID => 'Void',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
