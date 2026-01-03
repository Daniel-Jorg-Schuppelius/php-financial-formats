<?php
/*
 * Created on   : Fri Dec 26 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : StatementIndicator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV;

use InvalidArgumentException;

/**
 * DATEV Statement indicator for debitors/creditors (Field 122).
 *
 * 1 = Statement for all items
 * 2 = Statement only if an item is dunnable
 * 3 = Statement for all items due for dunning
 * 9 = kein Kontoauszug
 *
 * @see https://developer.datev.de/de/file-format/details/datev-format/format-description/debitorskreditors
 */
enum StatementIndicator: int {
    case ALL_ITEMS        = 1; // Kontoauszug für alle Posten
    case DUNNABLE_ONLY    = 2; // Auszug nur dann, wenn ein Posten mahnfähig ist
    case ALL_DUNNABLE     = 3; // Auszug für alle mahnfälligen Posten
    case DISABLED         = 9; // kein Kontoauszug

    /**
     * German text label for UI/Logging.
     */
    public function getLabel(): string {
        return match ($this) {
            self::ALL_ITEMS     => 'Kontoauszug für alle Posten',
            self::DUNNABLE_ONLY => 'Auszug nur bei mahnfähigem Posten',
            self::ALL_DUNNABLE  => 'Auszug für alle mahnfälligen Posten',
            self::DISABLED      => 'kein Kontoauszug',
        };
    }

    /**
     * Factory for CSV/DATEV import.
     */
    public static function fromInt(int $value): self {
        return match ($value) {
            1 => self::ALL_ITEMS,
            2 => self::DUNNABLE_ONLY,
            3 => self::ALL_DUNNABLE,
            9 => self::DISABLED,
            default => throw new InvalidArgumentException("Ungültiges Kontoauszugskennzeichen: $value"),
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
     * Checks if statement is enabled.
     */
    public function isEnabled(): bool {
        return $this !== self::DISABLED;
    }

    /**
     * Checks if this is a dunning-related statement.
     */
    public function isDunningRelated(): bool {
        return $this === self::DUNNABLE_ONLY || $this === self::ALL_DUNNABLE;
    }
}
