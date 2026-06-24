<?php

namespace App\Enums;

enum JournalSource: string
{
    case Manual = 'manual';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::System => 'System',
        };
    }
}
