<?php

declare(strict_types=1);

namespace App\Enums;

enum InventoryMovementType: string
{
    case SALE = 'sale';
    case PURCHASE = 'purchase';
    case ADJUSTMENT = 'adjustment';
    case RETURN = 'return';
    case DAMAGE = 'damage';

    public function label(): string
    {
        return match ($this) {
            self::SALE => 'Sale',
            self::PURCHASE => 'Purchase',
            self::ADJUSTMENT => 'Adjustment',
            self::RETURN => 'Return',
            self::DAMAGE => 'Damage',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
