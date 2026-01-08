<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : BankOperationCode.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\Mt;

/**
 * Bank Operation Code for MT10x transfers (Field :23B:).
 * 
 * Definiert die Art der Transaktion.
 */
enum BankOperationCode: string {
    /**
     * CRED - Credit Transfer
     * Normal credit/transfer
     */
    case CRED = 'CRED';

    /**
     * CRTS - Credit Transfer for Test
     * Test transfer
     */
    case CRTS = 'CRTS';

    /**
     * SPAY - Special Payment
     * Sonderzahlung
     */
    case SPAY = 'SPAY';

    /**
     * SPRI - Priority Payment
     * Priority payment
     */
    case SPRI = 'SPRI';

    /**
     * SSTD - Standard Settlement
     * Standard-Abwicklung
     */
    case SSTD = 'SSTD';

    /**
     * Returns the German description.
     */
    public function description(): string {
        return match ($this) {
            self::CRED => 'Überweisung',
            self::CRTS => 'Test transfer',
            self::SPAY => 'Sonderzahlung',
            self::SPRI => 'Priority payment',
            self::SSTD => 'Standard-Abwicklung',
        };
    }

    /**
     * Returns the English description.
     */
    public function descriptionEn(): string {
        return match ($this) {
            self::CRED => 'Credit Transfer',
            self::CRTS => 'Credit Transfer for Test',
            self::SPAY => 'Special Payment',
            self::SPRI => 'Priority Payment',
            self::SSTD => 'Standard Settlement',
        };
    }

    /**
     * Checks if this is a test transfer.
     */
    public function isTest(): bool {
        return $this === self::CRTS;
    }

    /**
     * Factory-Methode aus String.
     */
    public static function fromString(string $value): ?self {
        return self::tryFrom(strtoupper(trim($value)));
    }
}
