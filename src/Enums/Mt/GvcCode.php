<?php
/*
 * Created on   : Wed Jan 08 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : GvcCode.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\Mt;

use InvalidArgumentException;

/**
 * Geschäftsvorfall-Codes (GVC) for MT940 :86: field.
 * 
 * Based on DFÜ-Abkommen Anlage 3 - Datenformate.
 * These codes identify the type of banking transaction.
 * 
 * @see https://www.ebics.de/de/datenformate
 */
enum GvcCode: string {
    // =========================================================================
    // 001-019: Überweisungen (Transfers)
    // =========================================================================
    case TRANSFER_SEPA = '001';
    case TRANSFER_SEPA_INSTANT = '002';
    case TRANSFER_NON_SEPA = '003';
    case TRANSFER_INTERNAL = '004';
    case DIRECT_DEBIT = '005';
    case DIRECT_DEBIT_RETURN = '006';
    case STANDING_ORDER = '008';

        // =========================================================================
        // 020-039: Überweisungen (legacy)
        // =========================================================================
    case TRANSFER = '020';
    case TRANSFER_DTI = '021';

        // =========================================================================
        // 051-069: Gutschriften (Credits)
        // =========================================================================
    case CREDIT = '051';
    case CREDIT_SEPA = '052';
    case CREDIT_STANDING_ORDER = '053';
    case CREDIT_INTERNAL = '054';
    case CREDIT_INTEREST = '055';
    case CREDIT_FOREIGN = '063';
    case CREDIT_RETURN = '059';

        // =========================================================================
        // 071-089: Lastschriften (Direct Debits)
        // =========================================================================
    case DEBIT = '071';
    case DEBIT_SEPA = '072';
    case DEBIT_SEPA_CORE = '073';
    case DEBIT_SEPA_B2B = '074';
    case DEBIT_INTERNAL = '075';
    case DEBIT_RETURN = '079';

        // =========================================================================
        // 083-089: Schecks (Checks)
        // =========================================================================
    case CHECK_ISSUED = '083';
    case CHECK_CASHED = '084';
    case CHECK_RETURN = '089';

        // =========================================================================
        // 104-109: Kartenzahlungen (Card Payments)
        // =========================================================================
    case CARD_PAYMENT = '104';
    case CARD_PAYMENT_DEBIT = '105';
    case CARD_PAYMENT_CREDIT = '106';
    case ATM_WITHDRAWAL = '107';

        // =========================================================================
        // 152-159: Gebühren (Fees)
        // =========================================================================
    case FEE = '152';
    case FEE_ACCOUNT = '153';
    case FEE_TRANSFER = '154';

        // =========================================================================
        // 160-169: SEPA-Überweisungen
        // =========================================================================
    case SEPA_CREDIT_TRANSFER = '166';
    case SEPA_CREDIT_TRANSFER_INST = '167';

        // =========================================================================
        // 170-179: SEPA-Lastschriften
        // =========================================================================
    case SEPA_DIRECT_DEBIT = '170';
    case SEPA_DIRECT_DEBIT_CORE = '171';
    case SEPA_DIRECT_DEBIT_B2B = '172';

        // =========================================================================
        // 180-189: SEPA-Rückbuchungen
        // =========================================================================
    case SEPA_RETURN = '180';
    case SEPA_REVERSAL = '181';
    case SEPA_REFUND = '182';
    case SEPA_REJECT = '183';

        // =========================================================================
        // 191-199: Zinsen (Interest)
        // =========================================================================
    case INTEREST_CREDIT = '191';
    case INTEREST_DEBIT = '192';

        // =========================================================================
        // 201-219: Internationale Überweisungen
        // =========================================================================
    case INTERNATIONAL_TRANSFER = '201';
    case INTERNATIONAL_CREDIT = '211';

        // =========================================================================
        // 805-809: Daueraufträge
        // =========================================================================
    case STANDING_ORDER_EXECUTION = '805';
    case STANDING_ORDER_CREDIT = '806';

        // =========================================================================
        // 809: Sonstige
        // =========================================================================
    case MISCELLANEOUS = '809';

    /**
     * Returns the German description of the GVC code.
     */
    public function description(): string {
        return match ($this) {
            self::TRANSFER_SEPA             => 'SEPA-Überweisung',
            self::TRANSFER_SEPA_INSTANT     => 'SEPA-Echtzeitüberweisung',
            self::TRANSFER_NON_SEPA         => 'Überweisung (nicht SEPA)',
            self::TRANSFER_INTERNAL         => 'Interne Umbuchung',
            self::DIRECT_DEBIT              => 'Lastschrift',
            self::DIRECT_DEBIT_RETURN       => 'Lastschriftrückgabe',
            self::STANDING_ORDER            => 'Dauerauftrag',
            self::TRANSFER                  => 'Überweisung',
            self::TRANSFER_DTI              => 'Überweisung DTI',
            self::CREDIT                    => 'Gutschrift',
            self::CREDIT_SEPA               => 'SEPA-Gutschrift',
            self::CREDIT_STANDING_ORDER     => 'Dauerauftrag-Gutschrift',
            self::CREDIT_INTERNAL           => 'Interne Gutschrift',
            self::CREDIT_INTEREST           => 'Zinsgutschrift',
            self::CREDIT_FOREIGN            => 'Auslandsgutschrift',
            self::CREDIT_RETURN             => 'Rückgutschrift',
            self::DEBIT                     => 'Lastschrift',
            self::DEBIT_SEPA                => 'SEPA-Lastschrift',
            self::DEBIT_SEPA_CORE           => 'SEPA-Basislastschrift',
            self::DEBIT_SEPA_B2B            => 'SEPA-Firmenlastschrift',
            self::DEBIT_INTERNAL            => 'Interne Belastung',
            self::DEBIT_RETURN              => 'Lastschriftrückgabe',
            self::CHECK_ISSUED              => 'Scheckausstellung',
            self::CHECK_CASHED              => 'Scheckeinreichung',
            self::CHECK_RETURN              => 'Scheckrückgabe',
            self::CARD_PAYMENT              => 'Kartenzahlung',
            self::CARD_PAYMENT_DEBIT        => 'Kartenzahlung (Debit)',
            self::CARD_PAYMENT_CREDIT       => 'Kartenzahlung (Credit)',
            self::ATM_WITHDRAWAL            => 'Geldautomat-Abhebung',
            self::FEE                       => 'Gebühr',
            self::FEE_ACCOUNT               => 'Kontoführungsgebühr',
            self::FEE_TRANSFER              => 'Überweisungsgebühr',
            self::SEPA_CREDIT_TRANSFER      => 'SEPA-Überweisung',
            self::SEPA_CREDIT_TRANSFER_INST => 'SEPA-Echtzeitüberweisung',
            self::SEPA_DIRECT_DEBIT         => 'SEPA-Lastschrift',
            self::SEPA_DIRECT_DEBIT_CORE    => 'SEPA-Basislastschrift',
            self::SEPA_DIRECT_DEBIT_B2B     => 'SEPA-Firmenlastschrift',
            self::SEPA_RETURN               => 'SEPA-Rücklastschrift',
            self::SEPA_REVERSAL             => 'SEPA-Storno',
            self::SEPA_REFUND               => 'SEPA-Erstattung',
            self::SEPA_REJECT               => 'SEPA-Ablehnung',
            self::INTEREST_CREDIT           => 'Habenzinsen',
            self::INTEREST_DEBIT            => 'Sollzinsen',
            self::INTERNATIONAL_TRANSFER    => 'Auslandsüberweisung',
            self::INTERNATIONAL_CREDIT      => 'Auslandsgutschrift',
            self::STANDING_ORDER_EXECUTION  => 'Dauerauftrag-Ausführung',
            self::STANDING_ORDER_CREDIT     => 'Dauerauftrag-Gutschrift',
            self::MISCELLANEOUS             => 'Sonstige',
        };
    }

    /**
     * Returns the English description of the GVC code.
     */
    public function descriptionEn(): string {
        return match ($this) {
            self::TRANSFER_SEPA             => 'SEPA Credit Transfer',
            self::TRANSFER_SEPA_INSTANT     => 'SEPA Instant Credit Transfer',
            self::TRANSFER_NON_SEPA         => 'Non-SEPA Transfer',
            self::TRANSFER_INTERNAL         => 'Internal Transfer',
            self::DIRECT_DEBIT              => 'Direct Debit',
            self::DIRECT_DEBIT_RETURN       => 'Direct Debit Return',
            self::STANDING_ORDER            => 'Standing Order',
            self::TRANSFER                  => 'Transfer',
            self::TRANSFER_DTI              => 'DTI Transfer',
            self::CREDIT                    => 'Credit',
            self::CREDIT_SEPA               => 'SEPA Credit',
            self::CREDIT_STANDING_ORDER     => 'Standing Order Credit',
            self::CREDIT_INTERNAL           => 'Internal Credit',
            self::CREDIT_INTEREST           => 'Interest Credit',
            self::CREDIT_FOREIGN            => 'Foreign Credit',
            self::CREDIT_RETURN             => 'Return Credit',
            self::DEBIT                     => 'Direct Debit',
            self::DEBIT_SEPA                => 'SEPA Direct Debit',
            self::DEBIT_SEPA_CORE           => 'SEPA Core Direct Debit',
            self::DEBIT_SEPA_B2B            => 'SEPA B2B Direct Debit',
            self::DEBIT_INTERNAL            => 'Internal Debit',
            self::DEBIT_RETURN              => 'Direct Debit Return',
            self::CHECK_ISSUED              => 'Check Issued',
            self::CHECK_CASHED              => 'Check Cashed',
            self::CHECK_RETURN              => 'Check Return',
            self::CARD_PAYMENT              => 'Card Payment',
            self::CARD_PAYMENT_DEBIT        => 'Debit Card Payment',
            self::CARD_PAYMENT_CREDIT       => 'Credit Card Payment',
            self::ATM_WITHDRAWAL            => 'ATM Withdrawal',
            self::FEE                       => 'Fee',
            self::FEE_ACCOUNT               => 'Account Fee',
            self::FEE_TRANSFER              => 'Transfer Fee',
            self::SEPA_CREDIT_TRANSFER      => 'SEPA Credit Transfer',
            self::SEPA_CREDIT_TRANSFER_INST => 'SEPA Instant Credit Transfer',
            self::SEPA_DIRECT_DEBIT         => 'SEPA Direct Debit',
            self::SEPA_DIRECT_DEBIT_CORE    => 'SEPA Core Direct Debit',
            self::SEPA_DIRECT_DEBIT_B2B     => 'SEPA B2B Direct Debit',
            self::SEPA_RETURN               => 'SEPA Return',
            self::SEPA_REVERSAL             => 'SEPA Reversal',
            self::SEPA_REFUND               => 'SEPA Refund',
            self::SEPA_REJECT               => 'SEPA Reject',
            self::INTEREST_CREDIT           => 'Credit Interest',
            self::INTEREST_DEBIT            => 'Debit Interest',
            self::INTERNATIONAL_TRANSFER    => 'International Transfer',
            self::INTERNATIONAL_CREDIT      => 'International Credit',
            self::STANDING_ORDER_EXECUTION  => 'Standing Order Execution',
            self::STANDING_ORDER_CREDIT     => 'Standing Order Credit',
            self::MISCELLANEOUS             => 'Miscellaneous',
        };
    }

    /**
     * Checks if this is a credit (incoming) transaction.
     */
    public function isCredit(): bool {
        return match ($this) {
            self::CREDIT,
            self::CREDIT_SEPA,
            self::CREDIT_STANDING_ORDER,
            self::CREDIT_INTERNAL,
            self::CREDIT_INTEREST,
            self::CREDIT_FOREIGN,
            self::CREDIT_RETURN,
            self::INTERNATIONAL_CREDIT,
            self::STANDING_ORDER_CREDIT,
            self::SEPA_CREDIT_TRANSFER,
            self::SEPA_CREDIT_TRANSFER_INST,
            self::INTEREST_CREDIT,
            self::SEPA_REFUND => true,
            default => false,
        };
    }

    /**
     * Checks if this is a debit (outgoing) transaction.
     */
    public function isDebit(): bool {
        return match ($this) {
            self::TRANSFER,
            self::TRANSFER_SEPA,
            self::TRANSFER_SEPA_INSTANT,
            self::TRANSFER_NON_SEPA,
            self::TRANSFER_DTI,
            self::DEBIT,
            self::DEBIT_SEPA,
            self::DEBIT_SEPA_CORE,
            self::DEBIT_SEPA_B2B,
            self::DEBIT_INTERNAL,
            self::DEBIT_RETURN,
            self::INTERNATIONAL_TRANSFER,
            self::STANDING_ORDER_EXECUTION,
            self::INTEREST_DEBIT,
            self::ATM_WITHDRAWAL,
            self::FEE,
            self::FEE_ACCOUNT,
            self::FEE_TRANSFER => true,
            default => false,
        };
    }

    /**
     * Checks if this is a SEPA transaction.
     */
    public function isSepa(): bool {
        return match ($this) {
            self::TRANSFER_SEPA,
            self::TRANSFER_SEPA_INSTANT,
            self::CREDIT_SEPA,
            self::DEBIT_SEPA,
            self::DEBIT_SEPA_CORE,
            self::DEBIT_SEPA_B2B,
            self::SEPA_CREDIT_TRANSFER,
            self::SEPA_CREDIT_TRANSFER_INST,
            self::SEPA_DIRECT_DEBIT,
            self::SEPA_DIRECT_DEBIT_CORE,
            self::SEPA_DIRECT_DEBIT_B2B,
            self::SEPA_RETURN,
            self::SEPA_REVERSAL,
            self::SEPA_REFUND,
            self::SEPA_REJECT => true,
            default => false,
        };
    }

    /**
     * Checks if this is a return/reversal transaction.
     */
    public function isReturn(): bool {
        return match ($this) {
            self::DIRECT_DEBIT_RETURN,
            self::CREDIT_RETURN,
            self::DEBIT_RETURN,
            self::CHECK_RETURN,
            self::SEPA_RETURN,
            self::SEPA_REVERSAL,
            self::SEPA_REFUND,
            self::SEPA_REJECT => true,
            default => false,
        };
    }

    /**
     * Creates a GvcCode from a string.
     * Returns null if the code is not recognized.
     */
    public static function tryFromString(string $code): ?self {
        $code = str_pad(trim($code), 3, '0', STR_PAD_LEFT);
        return self::tryFrom($code);
    }

    /**
     * Creates a GvcCode from a string.
     * Throws an exception if the code is not recognized.
     */
    public static function fromString(string $code): self {
        $result = self::tryFromString($code);
        if ($result === null) {
            throw new InvalidArgumentException("Unknown GVC code: $code");
        }
        return $result;
    }
}
