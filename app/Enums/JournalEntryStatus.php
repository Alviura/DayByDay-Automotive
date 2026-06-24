<?php

namespace App\Enums;

enum JournalEntryStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Posted = 'posted';
    case Voided = 'voided';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingApproval => 'Pending Approval',
            self::Posted => 'Posted',
            self::Voided => 'Voided',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'fin-badge fin-badge-slate',
            self::PendingApproval => 'fin-badge fin-badge-amber',
            self::Posted => 'fin-badge fin-badge-green',
            self::Voided => 'fin-badge fin-badge-rose',
        };
    }
}
