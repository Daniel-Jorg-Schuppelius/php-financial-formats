<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : LocalInstrument.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\ISO20022\Pain;

/**
 * Local Instrument for SEPA payments (LclInstrm).
 * 
 * Definiert den SEPA-Zahlungstyp (Core, B2B, etc.).
 * 
 * @package CommonToolkit\Enums\Common\Banking
 */
enum LocalInstrument: string {
    case SEPA_CORE = 'CORE';          // SEPA Core
    case SEPA_B2B  = 'B2B';           // SEPA B2B
    case SEPA_COR1 = 'COR1';          // SEPA Core (1 Tag)
    case SEPA_INST = 'INST';          // SEPA Instant

    /**
     * Returns the description.
     */
    public function description(): string {
        return match ($this) {
            self::SEPA_CORE => 'SEPA Core Lastschrift',
            self::SEPA_B2B  => 'SEPA B2B Lastschrift (Firmenlastschrift)',
            self::SEPA_COR1 => 'SEPA Core Lastschrift (verkürzte Frist)',
            self::SEPA_INST => 'SEPA Instant',
        };
    }

    /**
     * Default for consumer direct debits.
     */
    public static function defaultConsumer(): self {
        return self::SEPA_CORE;
    }

    /**
     * Default for business direct debits.
     */
    public static function defaultBusiness(): self {
        return self::SEPA_B2B;
    }

    /**
     * Checks if B2B direct debit.
     */
    public function isBusiness(): bool {
        return $this === self::SEPA_B2B;
    }

    /**
     * Checks if Core direct debit.
     */
    public function isCore(): bool {
        return match ($this) {
            self::SEPA_CORE,
            self::SEPA_COR1 => true,
            default => false,
        };
    }
}
