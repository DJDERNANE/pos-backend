<?php

namespace App\Enums;

enum UserRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case CASHIER = 'cashier';
    case INVENTORY_MANAGER = 'inventory_manager';

    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Organization Owner',
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Store Manager',
            self::CASHIER => 'Cashier',
            self::INVENTORY_MANAGER => 'Inventory Manager',
        };
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
