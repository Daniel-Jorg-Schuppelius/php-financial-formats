<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : MandateStatus.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\Pain;

/**
 * Mandate status for SEPA mandates.
 * 
 * @package CommonToolkit\Enums\Common\Banking
 */
enum MandateStatus: string {
    case ACCEPTED = 'ACCP';      // Akzeptiert
    case PENDING = 'PDNG';       // Ausstehend
    case REJECTED = 'RJCT';      // Abgelehnt
    case CANCELLED = 'CANC';     // Storniert
    case SUSPENDED = 'SUSP';     // Ausgesetzt
    case EXPIRED = 'EXPD';       // Abgelaufen
    case AMENDED = 'AMND';       // Geändert

    public function isActive(): bool {
        return match ($this) {
            self::ACCEPTED,
            self::AMENDED => true,
            default => false,
        };
    }

    public function isTerminal(): bool {
        return match ($this) {
            self::REJECTED,
            self::CANCELLED,
            self::EXPIRED => true,
            default => false,
        };
    }

    public function description(): string {
        return match ($this) {
            self::ACCEPTED => 'Akzeptiert',
            self::PENDING => 'Ausstehend',
            self::REJECTED => 'Abgelehnt',
            self::CANCELLED => 'Storniert',
            self::SUSPENDED => 'Ausgesetzt',
            self::EXPIRED => 'Abgelaufen',
            self::AMENDED => 'Geändert',
        };
    }
}
