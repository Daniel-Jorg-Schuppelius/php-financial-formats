<?php
/*
 * Created on   : Wed Jan 08 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : TransactionTypeIndicator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\Mt;

/**
 * Transaction Type Indicator for MT940 :61: field.
 * 
 * According to SWIFT MT940 specification, the transaction type indicator
 * is a single character that classifies the transaction.
 * 
 * Format in :61:: [TransactionTypeIndicator][3!c] (e.g., "N051", "S020", "FCHK")
 */
enum TransactionTypeIndicator: string {
    /**
     * N - SWIFT Transfer (Book Transfer)
     * Standard SWIFT transaction type.
     */
    case SWIFT = 'N';

    /**
     * S - First Advice (SWIFT Message)
     * Used when this is the first advice of the transaction.
     */
    case FIRST_ADVICE = 'S';

    /**
     * F - Other Transaction (Non-SWIFT)
     * Used for non-SWIFT transactions or special types.
     */
    case OTHER = 'F';

    /**
     * Returns the description.
     */
    public function description(): string {
        return match ($this) {
            self::SWIFT        => 'SWIFT Transfer',
            self::FIRST_ADVICE => 'First Advice',
            self::OTHER        => 'Non-SWIFT Transaction',
        };
    }

    /**
     * Returns the German description.
     */
    public function descriptionDe(): string {
        return match ($this) {
            self::SWIFT        => 'SWIFT-Übertragung',
            self::FIRST_ADVICE => 'Erstanzeige',
            self::OTHER        => 'Sonstige Transaktion',
        };
    }

    /**
     * Creates from the first character of a booking key.
     */
    public static function fromBookingKey(string $bookingKey): ?self {
        if (strlen($bookingKey) < 1) {
            return null;
        }
        return self::tryFrom(strtoupper($bookingKey[0]));
    }
}
