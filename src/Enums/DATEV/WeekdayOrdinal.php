<?php
/*
 * Created on   : Fri Dec 26 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : WeekdayOrdinal.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV;

use CommonToolkit\Enums\Weekday;
use InvalidArgumentException;

/**
 * DATEV Weekday ordinal for recurring bookings (Field 86).
 * Definiert, ob z.B. der 1., 2., 3., 4. oder letzte Montag im Monat gemeint ist.
 *
 * @see https://developer.datev.de/de/file-format/details/datev-format/format-description/recurring-bookings
 */
enum WeekdayOrdinal: int {
    case FIRST  = 1;
    case SECOND = 2;
    case THIRD  = 3;
    case FOURTH = 4;
    case LAST   = 5;

    /**
     * German text label for UI/Logging.
     */
    public function getLabel(): string {
        return match ($this) {
            self::FIRST  => 'Erster',
            self::SECOND => 'Zweiter',
            self::THIRD  => 'Dritter',
            self::FOURTH => 'Vierter',
            self::LAST   => 'Letzter',
        };
    }

    /**
     * Text form in lowercase (for sentence construction).
     */
    public function getLowercaseLabel(): string {
        return strtolower($this->getLabel());
    }

    /**
     * Factory for integer values.
     */
    public static function fromInt(int $value): self {
        return match ($value) {
            1 => self::FIRST,
            2 => self::SECOND,
            3 => self::THIRD,
            4 => self::FOURTH,
            5 => self::LAST,
            default => throw new InvalidArgumentException("Ungültige Ordnungszahl Wochentag: $value"),
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
     * Checks if this is the last day.
     */
    public function isLast(): bool {
        return $this === self::LAST;
    }

    /**
     * Formatiert Ordnungszahl mit Wochentag als lesbaren Text.
     * z.B. "Erster Montag", "Letzter Freitag"
     */
    public function formatWithWeekday(Weekday $weekday): string {
        return $this->getLabel() . ' ' . $weekday->getName('de');
    }
}
