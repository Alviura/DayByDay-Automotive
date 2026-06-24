<?php

namespace App\Enums;

enum SupplierSellAs: string
{
    case Piece = 'piece';
    case Pair = 'pair';
    case Set = 'set';

    public function label(): string
    {
        return match ($this) {
            self::Piece => 'Piece',
            self::Pair => 'Pair',
            self::Set => 'Set',
        };
    }

    public function defaultUnitsPerUnit(): int
    {
        return match ($this) {
            self::Piece => 1,
            self::Pair => 2,
            self::Set => 4,
        };
    }

    public function quantityLabel(): string
    {
        return match ($this) {
            self::Piece => 'pieces',
            self::Pair => 'pairs',
            self::Set => 'sets',
        };
    }

    public function orderUnitLabel(): string
    {
        return match ($this) {
            self::Piece => 'PCS',
            self::Pair => 'Pairs',
            self::Set => 'Sets',
        };
    }
}
