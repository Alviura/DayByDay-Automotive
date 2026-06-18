<?php

namespace App\Enums;

enum ApprovalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Returned => 'Returned for Revision',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'mi-status-pending',
            self::Approved => 'mi-status-active',
            self::Rejected => 'mi-status-rejected',
            self::Returned => 'mi-status-inactive',
        };
    }
}
