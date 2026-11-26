<?php

namespace App\Enums;

enum EvidenceStatus: string
{
    case Pending = 'pending';
    case Registered = 'registered';
    case Failed = 'failed';

    /**
     * Human-readable name shown in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Registration',
            self::Registered => 'Registered on Blockchain',
            self::Failed => 'Registration Failed',
        };
    }

    /**
     * Bootstrap badge color class for this status, used wherever it is displayed.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'text-bg-warning',
            self::Registered => 'text-bg-success',
            self::Failed => 'text-bg-danger',
        };
    }
}
