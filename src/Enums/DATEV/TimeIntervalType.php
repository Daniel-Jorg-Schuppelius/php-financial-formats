<?php
/*
 * Created on   : Fri Dec 26 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : TimeIntervalType.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV;

use InvalidArgumentException;

/**
 * DATEV Time interval type for recurring bookings (Field 81).
 * Defines whether booking is daily or monthly.
 *
 * @see https://developer.datev.de/de/file-format/details/datev-format/format-description/recurring-bookings
 */
enum TimeIntervalType: string {
    case DAILY   = 'TAG';
    case MONTHLY = 'MON';

    /**
     * German text label for UI/Logging.
     */
    public function getLabel(): string {
        return match ($this) {
            self::DAILY   => 'Täglich',
            self::MONTHLY => 'Monatlich',
        };
    }

    /**
     * Factory for string values (case-insensitive).
     */
    public static function fromString(string $value): self {
        $normalized = strtoupper(trim($value, '" '));
        return match ($normalized) {
            'TAG'   => self::DAILY,
            'MON'   => self::MONTHLY,
            default => throw new InvalidArgumentException("Ungültige Zeitintervallart: $value"),
        };
    }

    /**
     * Factory with null return for invalid values.
     */
    public static function tryFromString(string $value): ?self {
        try {
            return self::fromString($value);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Checks if daily.
     */
    public function isDaily(): bool {
        return $this === self::DAILY;
    }

    /**
     * Checks if monthly.
     */
    public function isMonthly(): bool {
        return $this === self::MONTHLY;
    }

    /**
     * Returns the CSV value (enclosed in quotes).
     */
    public function toCsvValue(): string {
        return '"' . $this->value . '"';
    }
}
