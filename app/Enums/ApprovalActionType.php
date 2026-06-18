<?php

namespace App\Enums;

enum ApprovalActionType: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Returned = 'returned';
    case Commented = 'commented';

    public function label(): string
    {
        return match ($this) {
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Returned => 'Returned for Revision',
            self::Commented => 'Comment',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Approved => 'fa-circle-check',
            self::Rejected => 'fa-circle-xmark',
            self::Returned => 'fa-rotate-left',
            self::Commented => 'fa-comment',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Approved => 'green',
            self::Rejected => 'red',
            self::Returned => 'amber',
            self::Commented => 'gray',
        };
    }
}
