<?php
/*
 * Created on   : Fri Dec 26 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PaymentCarrierIndicator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV;

use InvalidArgumentException;

/**
 * DATEV Payment carrier indicator for debitors/creditors (Field 136).
 *
 * Empty or 0 = no specification, master data keying applies
 * 5 = Einzelscheck
 * 6 = Sammelscheck
 * 7 = SEPA/foreign transfer with one invoice
 * 8 = SEPA/foreign transfer with multiple invoices
 * 9 = no transfers, cheques
 *
 * @see https://developer.datev.de/de/file-format/details/datev-format/format-description/debitorskreditors
 */
enum PaymentCarrierIndicator: int {
    case NONE                  = 0; // keine Angaben, es gilt die Stammdaten-Schlüsselung
    case SINGLE_CHECK          = 5; // Einzelscheck
    case COLLECTIVE_CHECK      = 6; // Sammelscheck
    case SEPA_TRANSFER_SINGLE  = 7; // SEPA-/Auslandsüberweisung mit einer Rechnung
    case SEPA_TRANSFER_MULTI   = 8; // SEPA-/Auslandsüberweisung mit mehreren Rechnungen
    case DISABLED              = 9; // keine Überweisungen, Schecks

    /**
     * German text label for UI/Logging.
     */
    public function getLabel(): string {
        return match ($this) {
            self::NONE                 => 'keine Angabe (Stammdaten-Schlüsselung)',
            self::SINGLE_CHECK         => 'Einzelscheck',
            self::COLLECTIVE_CHECK     => 'Sammelscheck',
            self::SEPA_TRANSFER_SINGLE => 'SEPA-/Auslandsüberweisung (einzeln)',
            self::SEPA_TRANSFER_MULTI  => 'SEPA-/Auslandsüberweisung (Sammel)',
            self::DISABLED             => 'keine Überweisungen/Schecks',
        };
    }

    /**
     * Factory for CSV/DATEV import.
     */
    public static function fromInt(int $value): self {
        return match ($value) {
            0 => self::NONE,
            5 => self::SINGLE_CHECK,
            6 => self::COLLECTIVE_CHECK,
            7 => self::SEPA_TRANSFER_SINGLE,
            8 => self::SEPA_TRANSFER_MULTI,
            9 => self::DISABLED,
            default => throw new InvalidArgumentException("Ungültiges Zahlungsträgerkennzeichen: $value"),
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
     * Checks if this is a SEPA transfer.
     */
    public function isSepaTransfer(): bool {
        return $this === self::SEPA_TRANSFER_SINGLE || $this === self::SEPA_TRANSFER_MULTI;
    }

    /**
     * Checks if this is a cheque.
     */
    public function isCheck(): bool {
        return $this === self::SINGLE_CHECK || $this === self::COLLECTIVE_CHECK;
    }

    /**
     * Checks if payment carrier is explicitly disabled.
     */
    public function isDisabled(): bool {
        return $this === self::DISABLED;
    }
}
