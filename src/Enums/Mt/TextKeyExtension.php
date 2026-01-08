<?php
/*
 * Created on   : Wed Jan 08 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : TextKeyExtension.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\Mt;

/**
 * Text Key Extension (?34 field) for MT940 :86: field.
 * 
 * 3-digit extension code for the transaction type.
 * Used in DATEV format to provide additional classification.
 */
enum TextKeyExtension: string {
    // =========================================================================
    // Standard Extensions (900-999)
    // =========================================================================
    case STANDARD = '000';

    /**
     * SEPA Credit Transfer
     */
    case SEPA_CREDIT = '997';

    /**
     * SEPA Direct Debit Core
     */
    case SEPA_CORE = '998';

    /**
     * SEPA Direct Debit B2B
     */
    case SEPA_B2B = '999';

    // =========================================================================
    // Specific Extensions
    // =========================================================================
    /**
     * Salary Payment
     */
    case SALARY = '001';

    /**
     * Standing Order
     */
    case STANDING_ORDER = '002';

    /**
     * Return/Rejection
     */
    case RETURN = '003';

    /**
     * Interest
     */
    case INTEREST = '004';

    /**
     * Fee
     */
    case FEE = '005';

    /**
     * Returns the description.
     */
    public function description(): string {
        return match ($this) {
            self::STANDARD       => 'Standard',
            self::SEPA_CREDIT    => 'SEPA Credit Transfer',
            self::SEPA_CORE      => 'SEPA Direct Debit (Core)',
            self::SEPA_B2B       => 'SEPA Direct Debit (B2B)',
            self::SALARY         => 'Salary Payment',
            self::STANDING_ORDER => 'Standing Order',
            self::RETURN         => 'Return/Rejection',
            self::INTEREST       => 'Interest',
            self::FEE            => 'Fee',
        };
    }

    /**
     * Returns the German description.
     */
    public function descriptionDe(): string {
        return match ($this) {
            self::STANDARD       => 'Standard',
            self::SEPA_CREDIT    => 'SEPA-Überweisung',
            self::SEPA_CORE      => 'SEPA-Basislastschrift',
            self::SEPA_B2B       => 'SEPA-Firmenlastschrift',
            self::SALARY         => 'Gehaltszahlung',
            self::STANDING_ORDER => 'Dauerauftrag',
            self::RETURN         => 'Rückbuchung/Ablehnung',
            self::INTEREST       => 'Zinsen',
            self::FEE            => 'Gebühr',
        };
    }

    /**
     * Checks if this is a SEPA-related extension.
     */
    public function isSepa(): bool {
        return match ($this) {
            self::SEPA_CREDIT, self::SEPA_CORE, self::SEPA_B2B => true,
            default => false,
        };
    }

    /**
     * Creates from a string, returns STANDARD if not recognized.
     */
    public static function fromString(string $code): self {
        $code = str_pad(trim($code), 3, '0', STR_PAD_LEFT);
        return self::tryFrom($code) ?? self::STANDARD;
    }

    /**
     * Tries to create from a string, returns null if not recognized.
     */
    public static function tryFromString(string $code): ?self {
        $code = str_pad(trim($code), 3, '0', STR_PAD_LEFT);
        return self::tryFrom($code);
    }
}
