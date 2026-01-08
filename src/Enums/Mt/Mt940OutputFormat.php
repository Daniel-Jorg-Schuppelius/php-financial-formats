<?php
/*
 * Created on   : Wed Jan 08 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt940OutputFormat.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\Mt;

/**
 * Enum for MT940 output format variants.
 *
 * SWIFT: Standard SWIFT MT940 format with simple :86: lines (65 chars max per line)
 * DATEV: German DATEV/DFÜ format with structured ?xx subfields
 */
enum Mt940OutputFormat: string {
    /**
     * Standard SWIFT MT940 format.
     * :86: field contains plain text split into 65-character lines (max 6 lines).
     */
    case SWIFT = 'swift';

    /**
     * German DATEV/DFÜ format.
     * :86: field uses structured subfields:
     * - ?00: GVC code + booking text
     * - ?10: Primanoten-Nr.
     * - ?20-?29: Purpose lines
     * - ?30: Payer BLZ/BIC
     * - ?31: Payer account/IBAN
     * - ?32-?33: Payer name
     * - ?34: Text key extension
     * - ?60-?63: Additional purpose lines
     */
    case DATEV = 'datev';

    /**
     * Returns whether this format uses structured subfields.
     */
    public function isStructured(): bool {
        return $this === self::DATEV;
    }

    /**
     * Returns the maximum line length for the :86: field.
     */
    public function maxLineLength(): int {
        return match ($this) {
            self::SWIFT => 65,
            self::DATEV => 27,
        };
    }

    /**
     * Returns the maximum number of lines for the :86: field.
     */
    public function maxLines(): int {
        return match ($this) {
            self::SWIFT => 6,
            self::DATEV => 20, // ?20-?29 + ?60-?63 = 14, plus header lines
        };
    }

    /**
     * Returns a human-readable description.
     */
    public function description(): string {
        return match ($this) {
            self::SWIFT => 'Standard SWIFT MT940 format',
            self::DATEV => 'German DATEV/DFÜ format with structured subfields',
        };
    }
}
