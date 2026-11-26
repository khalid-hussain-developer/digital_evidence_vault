<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Investigator = 'investigator';
    case ForensicOfficer = 'forensic_officer';
    case Reviewer = 'reviewer';

    /**
     * Human-readable name shown in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Investigator => 'Investigator',
            self::ForensicOfficer => 'Forensic Officer',
            self::Reviewer => 'Reviewer',
        };
    }

    /**
     * Bootstrap badge classes for this role, used wherever a role is displayed.
     *
     * Deliberately an outline style (border + colored text, no fill) rather
     * than the solid "text-bg-*" style used for statuses (case status,
     * evidence status, transaction status) — the two often appear side by
     * side (e.g. a user's role next to a case's status), and without a
     * visual distinction a reader can't tell "this is a role" from "this
     * is a status" at a glance even though they share a color.
     */
    public function badgeClass(): string
    {
        return 'border '.match ($this) {
            self::Admin => 'border-primary text-primary',
            self::Investigator => 'border-info text-info',
            self::ForensicOfficer => 'border-success text-success',
            self::Reviewer => 'border-secondary text-secondary',
        };
    }
}
