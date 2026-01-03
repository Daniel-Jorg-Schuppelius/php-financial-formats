<?php
/*
 * Created on   : Sat Dec 14 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : FieldHeaderInterface.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Interfaces\DATEV;

use CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields\Format\Category;

/**
 * Interface for DATEV field header definitions.
 * Defines column descriptions for various DATEV formats.
 */
interface FieldHeaderInterface {
    /**
     * Returns all fields in the correct order.
     * 
     * @return static[]
     */
    public static function ordered(): array;

    /**
     * Returns the required fields.
     * 
     * @return static[]
     */
    public static function required(): array;

    /**
     * Checks if the field is required.
     */
    public function isRequired(): bool;

    /**
     * Returns the DATEV category for this header format.
     */
    public static function getCategory(): Category;

    /**
     * Returns the DATEV version for this header format.
     */
    public static function getVersion(): int;

    /**
     * Returns the number of defined fields.
     */
    public static function getFieldCount(): int;

    /**
     * Checks if a field value is valid (contained in enum).
     */
    public static function isValidFieldValue(string $value): bool;

    /**
     * Indicates whether the field should be quoted in the FieldHeader.
     * Default false - DATEV FieldHeaders are not quoted.
     */
    public function isQuotedHeader(): bool;

    /**
     * Indicates whether data values for this field should be quoted.
     * Based on the data type: text fields = quoted, numeric fields = not quoted.
     */
    public function isQuotedValue(): bool;

    /**
     * Returns the actual header name for CSV output.
     * Kann vom Enum-Wert abweichen, wenn die DATEV-Sample-Dateien andere Namen verwenden.
     * By default, the enum value is returned.
     */
    public function headerName(): string;

    /**
     * Returns the 0-based field position/index.
     */
    public function getPosition(): int;
}
