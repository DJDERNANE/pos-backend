<?php


namespace App\Enums;

enum UnitType: string
{
    case PIECE = 'piece';
    case PACK  = 'pack';
    case BOX   = 'box';

    /**
     * Human-readable label for display in UI / reports.
     */
    public function label(): string
    {
        return match ($this) {
            self::PIECE => 'Piece',
            self::PACK  => 'Pack',
            self::BOX   => 'Box',
        };
    }

    /**
     * Default quantity_per_unit for each unit type.
     * Callers may override this; it is only a sensible fallback.
     */
    public function defaultQuantity(): int
    {
        return match ($this) {
            self::PIECE => 1,
            self::PACK  => 6,
            self::BOX   => 12,
        };
    }

    /**
     * Whether this unit type can be split into base units at the POS.
     */
    public function isSplittable(): bool
    {
        return $this !== self::PIECE;
    }

    /**
     * Return all string values — useful for validation rules.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}