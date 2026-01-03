<?php
/*
 * Created on   : Fri Dec 26 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DunningSubject.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV;

use InvalidArgumentException;

/**
 * DATEV Subject for OPOS (Field 24).
 * Identifies bookings/items as dunning interest or dunning fee.
 *
 * @see https://developer.datev.de/de/file-format/details/datev-format/format-description/recurring-bookings
 */
enum DunningSubject: int {
    case NONE           = 0;  // Keine Angabe
    case DUNNING_INTEREST = 31; // Mahnzins
    case DUNNING_FEE    = 40; // Mahngebühr

    /**
     * German text label for UI/Logging.
     */
    public function getLabel(): string {
        return match ($this) {
            self::NONE           => 'Keine Angabe',
            self::DUNNING_INTEREST => 'Mahnzins',
            self::DUNNING_FEE    => 'Mahngebühr',
        };
    }

    /**
     * Factory for integer values.
     */
    public static function fromInt(int $value): self {
        return match ($value) {
            0  => self::NONE,
            31 => self::DUNNING_INTEREST,
            40 => self::DUNNING_FEE,
            default => throw new InvalidArgumentException("Ungültiger Sachverhalt: $value"),
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
     * Checks if this is dunning interest.
     */
    public function isDunningInterest(): bool {
        return $this === self::DUNNING_INTEREST;
    }

    /**
     * Checks if this is a dunning fee.
     */
    public function isDunningFee(): bool {
        return $this === self::DUNNING_FEE;
    }

    /**
     * Checks if a dunning subject exists.
     */
    public function isDunningRelated(): bool {
        return $this === self::DUNNING_INTEREST || $this === self::DUNNING_FEE;
    }
}
