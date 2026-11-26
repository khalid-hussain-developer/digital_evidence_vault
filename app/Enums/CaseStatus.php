<?php

namespace App\Enums;

enum CaseStatus: string
{
    case Open = 'open';
    case UnderInvestigation = 'under_investigation';
    case Closed = 'closed';

    /**
     * Human-readable name shown in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::UnderInvestigation => 'Under Investigation',
            self::Closed => 'Closed',
        };
    }

    /**
     * Bootstrap badge color class for this status, used wherever it is displayed.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'text-bg-info',
            self::UnderInvestigation => 'text-bg-warning',
            self::Closed => 'text-bg-secondary',
        };
    }
}
