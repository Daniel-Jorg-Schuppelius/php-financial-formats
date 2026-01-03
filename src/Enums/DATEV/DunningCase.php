<?php
/*
 * Created on   : Sun Nov 23 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DunningCase.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV;

use InvalidArgumentException;

/**
 * Subject indicator for OPOS-relevant dunning interest and dunning fee records.
 *
 * 31 = Mahnzins
 * 40 = Dunning fee
 */
enum DunningCase: int {
    case INTEREST = 31;  // Mahnzins
    case FEE      = 40;  // Mahngebühr

    public function getLabel(): string {
        return match ($this) {
            self::INTEREST => 'Mahnzins',
            self::FEE      => 'Mahngebühr',
        };
    }

    public static function fromInt(int $value): self {
        return match ($value) {
            31 => self::INTEREST,
            40 => self::FEE,
            default => throw new InvalidArgumentException("Invalid DATEV case type: $value"),
        };
    }
}
