<?php

namespace App\Enums;

enum BlockchainTransactionType: string
{
    case Registration = 'registration';
    case TransferInitiation = 'transfer_initiation';
    case TransferAcceptance = 'transfer_acceptance';

    public function label(): string
    {
        return match ($this) {
            self::Registration => 'Evidence Registration',
            self::TransferInitiation => 'Custody Transfer Initiated',
            self::TransferAcceptance => 'Custody Transfer Accepted',
        };
    }
}
