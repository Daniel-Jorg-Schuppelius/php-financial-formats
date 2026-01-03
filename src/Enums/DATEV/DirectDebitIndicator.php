<?php
/*
 * Created on   : Fri Dec 26 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DirectDebitIndicator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV;

use InvalidArgumentException;

/**
 * DATEV Direct debit indicator for debitors/creditors (Field 133).
 *
 * Empty or 0 = no specification, master data keying applies
 * 7 = SEPA-Lastschrift mit einer Rechnung
 * 8 = SEPA-Lastschrift mit mehreren Rechnungen
 * 9 = kein Lastschriftverfahren bei diesem Debitor
 *
 * @see https://developer.datev.de/de/file-format/details/datev-format/format-description/debitorskreditors
 */
enum DirectDebitIndicator: int {
    case NONE                = 0; // keine Angaben, es gilt die Stammdaten-Schlüsselung
    case SEPA_SINGLE_INVOICE = 7; // SEPA-Lastschrift mit einer Rechnung
    case SEPA_MULTI_INVOICE  = 8; // SEPA-Lastschrift mit mehreren Rechnungen
    case DISABLED            = 9; // kein Lastschriftverfahren bei diesem Debitor

    /**
     * German text label for UI/Logging.
     */
    public function getLabel(): string {
        return match ($this) {
            self::NONE                => 'keine Angabe (Stammdaten-Schlüsselung)',
            self::SEPA_SINGLE_INVOICE => 'SEPA-Lastschrift mit einer Rechnung',
            self::SEPA_MULTI_INVOICE  => 'SEPA-Lastschrift mit mehreren Rechnungen',
            self::DISABLED            => 'kein Lastschriftverfahren',
        };
    }

    /**
     * Factory for CSV/DATEV import.
     */
    public static function fromInt(int $value): self {
        return match ($value) {
            0 => self::NONE,
            7 => self::SEPA_SINGLE_INVOICE,
            8 => self::SEPA_MULTI_INVOICE,
            9 => self::DISABLED,
            default => throw new InvalidArgumentException("Ungültiges Lastschriftkennzeichen: $value"),
        };
    }

    /**
     * Factory for string values (quoted in DATEV-Format).
     */
    public static function tryFromString(string $value): ?self {
        $trimmed = trim($value, '" ');
        if ($trimmed === '') {
            return null;
        }
        return self::fromInt((int) $trimmed);
    }

    /**
     * Checks if SEPA direct debit is active.
     */
    public function isSepaDirectDebit(): bool {
        return $this === self::SEPA_SINGLE_INVOICE || $this === self::SEPA_MULTI_INVOICE;
    }

    /**
     * Checks if direct debit is explicitly disabled.
     */
    public function isDisabled(): bool {
        return $this === self::DISABLED;
    }
}
