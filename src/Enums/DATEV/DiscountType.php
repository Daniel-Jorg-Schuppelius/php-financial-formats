<?php
/*
 * Created on   : Sun Nov 23 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DiscountType.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV;

use InvalidArgumentException;

enum DiscountType: int {
    case GOODS_PURCHASE       = 1; // Einkauf von Waren
    case MATERIALS_PURCHASE   = 2; // Roh-, Hilfs- und Betriebsstoffe

    /**
     * Liefert eine deutschsprachige Beschreibung.
     */
    public function getLabel(): string {
        return match ($this) {
            self::GOODS_PURCHASE     => 'Einkauf von Waren',
            self::MATERIALS_PURCHASE => 'Erwerb von Roh-, Hilfs- und Betriebsstoffen',
        };
    }

    /**
     * Erzeugt ein Enum aus einem numerischen DATEV-Wert.
     */
    public static function fromInt(int $value): self {
        return match ($value) {
            1 => self::GOODS_PURCHASE,
            2 => self::MATERIALS_PURCHASE,
            default => throw new InvalidArgumentException("Ungültiger Skontotyp: $value"),
        };
    }
}
