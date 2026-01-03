<?php
/*
 * Created on   : Fri Dec 26 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : ReceiptFieldHandling.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV;

use InvalidArgumentException;

/**
 * DATEV Receipt field 1 handling for recurring bookings (Field 1 "B1").
 * Steuert, wie die Rechnungsnummer bei der Verarbeitung behandelt wird.
 *
 * @see https://developer.datev.de/de/file-format/details/datev-format/format-description/recurring-bookings
 */
enum ReceiptFieldHandling: int {
    case FIXED_36       = 1; // Rechnungsnummer max. 36-stellig, unverändert
    case EXTENDED_34    = 2; // Rechnungsnummer max. 34-stellig, +2 Stellen ergänzt
    case AUTO_INCREMENT = 3; // Keine Erfassung, automatische Hochzählung

    /**
     * German text label for UI/Logging.
     */
    public function getLabel(): string {
        return match ($this) {
            self::FIXED_36       => 'Unverändert (max. 36-stellig)',
            self::EXTENDED_34    => 'Ergänzung +2 Stellen (max. 34-stellig)',
            self::AUTO_INCREMENT => 'Automatische Hochzählung',
        };
    }

    /**
     * Returns the maximum length of the invoice number.
     */
    public function getMaxLength(): ?int {
        return match ($this) {
            self::FIXED_36       => 36,
            self::EXTENDED_34    => 34,
            self::AUTO_INCREMENT => null, // Keine manuelle Eingabe
        };
    }

    /**
     * Factory for integer values.
     */
    public static function fromInt(int $value): self {
        return match ($value) {
            1 => self::FIXED_36,
            2 => self::EXTENDED_34,
            3 => self::AUTO_INCREMENT,
            default => throw new InvalidArgumentException("Ungültige Belegfeld-Behandlung: $value"),
        };
    }

    /**
     * Factory for string values with null return for invalid values.
     */
    public static function tryFromString(string $value): ?self {
        $trimmed = trim($value, '" ');
        if ($trimmed === '') {
            return null;
        }

        try {
            return self::fromInt((int) $trimmed);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Checks if the invoice number is automatically incremented.
     */
    public function isAutoIncrement(): bool {
        return $this === self::AUTO_INCREMENT;
    }

    /**
     * Checks if the invoice number is entered manually.
     */
    public function isManual(): bool {
        return $this !== self::AUTO_INCREMENT;
    }
}
