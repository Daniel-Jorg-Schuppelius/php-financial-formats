<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : ChargesCode.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\Mt;

/**
 * Charges code for MT10x transfers (Field :71A:).
 * 
 * Defines who bears the transfer charges.
 */
enum ChargesCode: string {
    /**
     * BEN - Beneficiary (beneficiary bears all charges)
     */
    case BEN = 'BEN';

    /**
     * OUR - Ordering Customer (sender bears all charges)
     */
    case OUR = 'OUR';

    /**
     * SHA - Shared (charges are shared)
     */
    case SHA = 'SHA';

    /**
     * SLEV - Service Level (according to SEPA agreement)
     */
    case SLEV = 'SLEV';

    /**
     * Returns the German description.
     */
    public function description(): string {
        return match ($this) {
            self::BEN  => 'Begünstigter trägt alle Gebühren',
            self::OUR  => 'Auftraggeber trägt alle Gebühren',
            self::SHA  => 'Gebühren werden geteilt',
            self::SLEV => 'Gemäß Service-Level-Vereinbarung (SEPA)',
        };
    }

    /**
     * Returns the English description.
     */
    public function descriptionEn(): string {
        return match ($this) {
            self::BEN => 'All charges borne by beneficiary',
            self::OUR => 'All charges borne by ordering customer',
            self::SHA => 'Charges shared',
            self::SLEV => 'Following service level agreement (SEPA)',
        };
    }

    /**
     * Checks if the code is SEPA-compliant.
     */
    public function isSepaCompliant(): bool {
        return in_array($this, [self::SHA, self::SLEV]);
    }

    /**
     * Returns the default SEPA code.
     */
    public static function defaultSepa(): self {
        return self::SLEV;
    }

    /**
     * Factory-Methode aus String.
     */
    public static function fromString(string $value): ?self {
        return self::tryFrom(strtoupper(trim($value)));
    }
}
