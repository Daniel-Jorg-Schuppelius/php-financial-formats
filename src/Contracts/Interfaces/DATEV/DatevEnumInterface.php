<?php
/*
 * Created on   : Fri Jan 03 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DatevEnumInterface.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Interfaces\DATEV;

/**
 * Interface for DATEV enum types that can be converted to/from CSV values.
 * 
 * Enums implementing this interface provide standardized conversion methods
 * for reading from and writing to DATEV CSV fields.
 */
interface DatevEnumInterface {
    /**
     * Converts the enum value to a DATEV CSV string representation.
     * 
     * @return string The CSV-compatible string value (may include quotes if needed)
     */
    public function toCsvValue(): string;

    /**
     * Creates an enum instance from a CSV string value.
     * 
     * @param string|null $value The raw CSV value (may be quoted)
     * @return static|null The enum instance or null if value is empty/invalid
     */
    public static function fromCsvValue(?string $value): ?static;
}
