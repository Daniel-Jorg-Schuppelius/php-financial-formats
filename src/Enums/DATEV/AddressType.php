<?php
/*
 * Created on   : Fri Dec 26 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : AddressType.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV;

use InvalidArgumentException;

/**
 * DATEV Address type for debitors/creditors (Field 15, 153).
 *
 * @see https://developer.datev.de/de/file-format/details/datev-format/format-description/debitorskreditors
 */
enum AddressType: string {
    case STREET       = 'STR'; // Straße
    case PO_BOX       = 'PF';  // Postfach
    case KEY_ACCOUNT  = 'GK';  // Großkunde

    /**
     * German text label for UI/Logging.
     */
    public function getLabel(): string {
        return match ($this) {
            self::STREET      => 'Straße',
            self::PO_BOX      => 'Postfach',
            self::KEY_ACCOUNT => 'Großkunde',
        };
    }

    /**
     * Factory for CSV/DATEV import (with quotes).
     */
    public static function tryFromString(string $value): ?self {
        $trimmed = trim($value, '" ');
        if ($trimmed === '') {
            return null;
        }
        return match (strtoupper($trimmed)) {
            'STR' => self::STREET,
            'PF'  => self::PO_BOX,
            'GK'  => self::KEY_ACCOUNT,
            default => throw new InvalidArgumentException("Ungültige Adressart: $value"),
        };
    }
}
