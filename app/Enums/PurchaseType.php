<?php

namespace App\Enums;

enum PurchaseType: string
{
    case Local = 'local';
    case Import = 'import';

    public function label(): string
    {
        return match ($this) {
            self::Local => 'Local',
            self::Import => 'Import',
        };
    }

    public function isLocal(): bool
    {
        return $this === self::Local;
    }

    public function isImport(): bool
    {
        return $this === self::Import;
    }
}
