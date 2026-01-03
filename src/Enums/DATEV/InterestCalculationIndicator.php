<?php
/*
 * Created on   : Fri Dec 26 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : InterestCalculationIndicator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV;

use InvalidArgumentException;

/**
 * DATEV Interest calculation indicator for debitors/creditors (Field 129).
 *
 * 0 = MPD keying applies
 * 1 = Fester Zinssatz
 * 2 = Interest rate via scale
 * 9 = No calculation for this debitor
 *
 * @see https://developer.datev.de/de/file-format/details/datev-format/format-description/debitorskreditors
 */
enum InterestCalculationIndicator: int {
    case MPD_DEFAULT      = 0; // MPD-Schlüsselung gilt
    case FIXED_RATE       = 1; // Fester Zinssatz
    case GRADUATED_RATE   = 2; // Zinssatz über Staffel
    case DISABLED         = 9; // Keine Berechnung für diesen Debitor

    /**
     * German text label for UI/Logging.
     */
    public function getLabel(): string {
        return match ($this) {
            self::MPD_DEFAULT    => 'MPD-Schlüsselung gilt',
            self::FIXED_RATE     => 'Fester Zinssatz',
            self::GRADUATED_RATE => 'Zinssatz über Staffel',
            self::DISABLED       => 'Keine Berechnung',
        };
    }

    /**
     * Factory for CSV/DATEV import.
     */
    public static function fromInt(int $value): self {
        return match ($value) {
            0 => self::MPD_DEFAULT,
            1 => self::FIXED_RATE,
            2 => self::GRADUATED_RATE,
            9 => self::DISABLED,
            default => throw new InvalidArgumentException("Ungültiges Zinsberechnungskennzeichen: $value"),
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
     * Checks if interest calculation is active.
     */
    public function isEnabled(): bool {
        return $this !== self::DISABLED;
    }
}
