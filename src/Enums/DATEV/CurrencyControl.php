<?php
/*
 * Created on   : Fri Dec 26 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CurrencyControl.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV;

use InvalidArgumentException;

/**
 * DATEV Currency control for debitors/creditors (Field 107).
 *
 * 0 = Payments in input currency
 * 2 = Ausgabe in EUR
 *
 * @see https://developer.datev.de/de/file-format/details/datev-format/format-description/debitorskreditors
 */
enum CurrencyControl: int {
    case INPUT_CURRENCY = 0; // Zahlungen in Eingabewährung
    case OUTPUT_EUR     = 2; // Ausgabe in EUR

    /**
     * German text label for UI/Logging.
     */
    public function getLabel(): string {
        return match ($this) {
            self::INPUT_CURRENCY => 'Zahlungen in Eingabewährung',
            self::OUTPUT_EUR     => 'Ausgabe in EUR',
        };
    }

    /**
     * Factory for CSV/DATEV import.
     */
    public static function fromInt(int $value): self {
        return match ($value) {
            0 => self::INPUT_CURRENCY,
            2 => self::OUTPUT_EUR,
            default => throw new InvalidArgumentException("Ungültige Währungssteuerung: $value"),
        };
    }

    /**
     * Factory for string values.
     */
    public static function tryFromString(string $value): ?self {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }
        return self::fromInt((int) $trimmed);
    }

    /**
     * Checks if EUR is used as output currency.
     */
    public function isEuroOutput(): bool {
        return $this === self::OUTPUT_EUR;
    }
}
