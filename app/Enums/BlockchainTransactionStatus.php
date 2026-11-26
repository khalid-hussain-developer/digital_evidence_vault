<?php

namespace App\Enums;

enum BlockchainTransactionStatus: string
{
    case Submitted = 'submitted';
    case Confirmed = 'confirmed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted — Awaiting Confirmation',
            self::Confirmed => 'Confirmed',
            self::Failed => 'Failed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Submitted => 'text-bg-warning',
            self::Confirmed => 'text-bg-success',
            self::Failed => 'text-bg-danger',
        };
    }
}
