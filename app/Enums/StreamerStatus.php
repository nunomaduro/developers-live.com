<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StreamerStatus: string implements HasColor, HasLabel
{
    case PendingApproval = 'pending_approval';
    case Rejected = 'rejected';
    case Approved = 'approved';

    /**
     * Get the label for the enum value.
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::PendingApproval => 'Pending Approval',
            self::Rejected => 'Rejected',
            self::Approved => 'Approved',
        };
    }

    /**
     * Get the color for the enum value.
     */
    public function getColor(): string
    {
        return match ($this) {
            self::PendingApproval => 'warning',
            self::Rejected => 'gray',
            self::Approved => 'success',
        };
    }
}
