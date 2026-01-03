<?php
/*
 * Created on   : Fri Dec 26 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : AddresseeType.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV;

use InvalidArgumentException;

/**
 * DATEV Addressee type for debitors/creditors (Field 7).
 *
 * @see https://developer.datev.de/de/file-format/details/datev-format/format-description/debitorskreditors
 */
enum AddresseeType: int {
    case NONE           = 0; // keine Angabe (Default = Unternehmen)
    case NATURAL_PERSON = 1; // natürliche Person
    case COMPANY        = 2; // Unternehmen

    /**
     * German text label for UI/Logging.
     */
    public function getLabel(): string {
        return match ($this) {
            self::NONE           => 'keine Angabe',
            self::NATURAL_PERSON => 'natürliche Person',
            self::COMPANY        => 'Unternehmen',
        };
    }

    /**
     * Factory for CSV/DATEV import.
     */
    public static function fromInt(int $value): self {
        return match ($value) {
            0 => self::NONE,
            1 => self::NATURAL_PERSON,
            2 => self::COMPANY,
            default => throw new InvalidArgumentException("Ungültiger Adressatentyp: $value"),
        };
    }

    /**
     * Factory for string values (quoted in DATEV-Format).
     */
    public static function tryFromString(string $value): ?self {
        $trimmed = trim($value, '"');
        if ($trimmed === '') {
            return null;
        }
        return self::fromInt((int) $trimmed);
    }

    /**
     * Checks if this is a natural person.
     */
    public function isNaturalPerson(): bool {
        return $this === self::NATURAL_PERSON;
    }

    /**
     * Checks if this is a company.
     */
    public function isCompany(): bool {
        return $this === self::COMPANY || $this === self::NONE;
    }
}
