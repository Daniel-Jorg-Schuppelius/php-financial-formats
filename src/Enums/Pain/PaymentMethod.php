<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PaymentMethod.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\Pain;

/**
 * Payment method for pain messages (PmtMtd).
 * 
 * @package CommonToolkit\Enums\Common\Banking
 */
enum PaymentMethod: string {
    /** Überweisung (Transfer) - Standard für SEPA und Auslandsüberweisungen */
    case TRANSFER = 'TRF';

    /** Scheck (Cheque) */
    case CHEQUE = 'CHK';

    /** Direktlastschrift (Direct Debit) - für pain.008 */
    case DIRECT_DEBIT = 'DD';

    /**
     * Returns the German description.
     */
    public function description(): string {
        return match ($this) {
            self::TRANSFER => 'Überweisung',
            self::CHEQUE => 'Scheck',
            self::DIRECT_DEBIT => 'Lastschrift',
        };
    }

    /**
     * Erstellt PaymentMethod aus String.
     */
    public static function fromString(string $code): self {
        return match (strtoupper(trim($code))) {
            'TRF', 'TRANSFER' => self::TRANSFER,
            'CHK', 'CHEQUE' => self::CHEQUE,
            'DD', 'DIRECT_DEBIT', 'DIRECTDEBIT' => self::DIRECT_DEBIT,
            default => self::TRANSFER,
        };
    }

    /**
     * Checks if the method is suitable for pain.001.
     */
    public function isPain001(): bool {
        return in_array($this, [self::TRANSFER, self::CHEQUE]);
    }

    /**
     * Checks if the method is suitable for pain.008.
     */
    public function isPain008(): bool {
        return $this === self::DIRECT_DEBIT;
    }

    /**
     * Returns the default for SEPA transfers.
     */
    public static function defaultSepa(): self {
        return self::TRANSFER;
    }
}
