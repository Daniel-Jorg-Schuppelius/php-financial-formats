<?php
/*
 * Created on   : Fri Dec 26 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : EndType.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV;

use InvalidArgumentException;

/**
 * DATEV End type for recurring bookings (Field 87).
 * Definiert, wie die Wiederholung endet.
 *
 * @see https://developer.datev.de/de/file-format/details/datev-format/format-description/recurring-bookings
 */
enum EndType: int {
    case NO_END     = 1; // Kein Ende (unbegrenzt)
    case BY_COUNT   = 2; // Endet nach Anzahl Wiederholungen
    case BY_DATE    = 3; // Endet an einem bestimmten Datum

    /**
     * German text label for UI/Logging.
     */
    public function getLabel(): string {
        return match ($this) {
            self::NO_END   => 'Kein Ende',
            self::BY_COUNT => 'Nach Anzahl',
            self::BY_DATE  => 'Am Datum',
        };
    }

    /**
     * Detailed description for UI tooltips.
     */
    public function getDescription(): string {
        return match ($this) {
            self::NO_END   => 'Die wiederkehrende Buchung läuft unbegrenzt weiter',
            self::BY_COUNT => 'Die wiederkehrende Buchung endet nach einer bestimmten Anzahl von Wiederholungen',
            self::BY_DATE  => 'Die wiederkehrende Buchung endet an einem bestimmten Datum',
        };
    }

    /**
     * Factory for integer values.
     */
    public static function fromInt(int $value): self {
        return match ($value) {
            1 => self::NO_END,
            2 => self::BY_COUNT,
            3 => self::BY_DATE,
            default => throw new InvalidArgumentException("Ungültiger Endetyp: $value"),
        };
    }

    /**
     * Factory with null return for invalid values.
     */
    public static function tryFromInt(int $value): ?self {
        try {
            return self::fromInt($value);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Factory for string values.
     */
    public static function tryFromString(string $value): ?self {
        $trimmed = trim($value, '" ');
        if ($trimmed === '' || !is_numeric($trimmed)) {
            return null;
        }
        return self::tryFromInt((int) $trimmed);
    }

    /**
     * Checks if unlimited.
     */
    public function isUnlimited(): bool {
        return $this === self::NO_END;
    }

    /**
     * Checks if by count.
     */
    public function isByCount(): bool {
        return $this === self::BY_COUNT;
    }

    /**
     * Checks if by date.
     */
    public function isByDate(): bool {
        return $this === self::BY_DATE;
    }

    /**
     * Checks if an end condition is defined.
     */
    public function hasEndCondition(): bool {
        return $this !== self::NO_END;
    }

    /**
     * Checks if the "Count" field (Field 88) is relevant.
     */
    public function requiresCount(): bool {
        return $this === self::BY_COUNT;
    }

    /**
     * Checks if the "End date" field (Field 89) is relevant.
     */
    public function requiresEndDate(): bool {
        return $this === self::BY_DATE;
    }
}
