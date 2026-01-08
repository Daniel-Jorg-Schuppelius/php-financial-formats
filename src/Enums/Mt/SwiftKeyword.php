<?php
/*
 * Created on   : Wed Jan 08 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : SwiftKeyword.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\Mt;

/**
 * SWIFT Structured Keywords for MT940/MT9xx :86: field.
 * 
 * These keywords are used in international SWIFT format to structure
 * the purpose/remittance information. Format: /KEYWORD/value/
 * 
 * @see mt940-details.pdf
 */
enum SwiftKeyword: string {
    // =========================================================================
    // Reference Keywords
    // =========================================================================
    /**
     * End-to-End Reference (35x)
     * Unique reference assigned by the initiating party.
     */
    case EREF = 'EREF';

    /**
     * Payment Information ID (35x)
     * Reference assigned by the payment initiator.
     */
    case PREF = 'PREF';

    /**
     * Instruction ID (35x)
     * Unique identification of the instruction.
     */
    case IREF = 'IREF';

    /**
     * Mandate Reference (35x)
     * Unique reference of the mandate for direct debit.
     */
    case MREF = 'MREF';

    /**
     * Creditor Identifier (35x)
     * Unique identification of the creditor.
     */
    case CRED = 'CRED';

    /**
     * Transaction Reference (17x)
     * Bank's reference for the transaction.
     */
    case TR = 'TR';

    // =========================================================================
    // Remittance Information
    // =========================================================================
    /**
     * Remittance Information (140x)
     * Free text description of the payment purpose.
     */
    case REMI = 'REMI';

    // =========================================================================
    // Party Information
    // =========================================================================
    /**
     * Beneficiary Name (70x)
     * Name of the beneficiary. Use with /NAME/ sub-keyword.
     */
    case BENM = 'BENM';

    /**
     * Ordering Party Name (70x)
     * Name of the ordering party. Use with /NAME/ sub-keyword.
     */
    case ORDP = 'ORDP';

    /**
     * Ultimate Debtor Name (70x)
     * Name of the ultimate debtor. Use with /NAME/ sub-keyword.
     */
    case ULTD = 'ULTD';

    /**
     * Ultimate Creditor Name (70x)
     * Name of the ultimate creditor. Use with /NAME/ sub-keyword.
     */
    case ULTC = 'ULTC';

    // =========================================================================
    // Account Information
    // =========================================================================
    /**
     * Beneficiary Account Info (34x)
     * Account information of the beneficiary.
     */
    case INFO = 'INFO';

    /**
     * Beneficiary Bank BIC (35x)
     * BIC of the beneficiary's bank.
     */
    case BBK = 'BBK';

    /**
     * Ordering Bank BIC (35x)
     * BIC of the ordering party's bank.
     */
    case OBK = 'OBK';

    /**
     * Virtual Account (34x)
     * Virtual account number.
     */
    case VACC = 'VACC';

    // =========================================================================
    // Amount Information
    // =========================================================================
    /**
     * Original Currency Amount (3!a15d)
     * Original amount in source currency.
     */
    case OCMT = 'OCMT';

    /**
     * Charges (3!a15d)
     * Transaction charges.
     */
    case CHGS = 'CHGS';

    /**
     * Exchange Rate (12d)
     * Foreign exchange rate applied.
     */
    case EXCH = 'EXCH';

    // =========================================================================
    // Classification
    // =========================================================================
    /**
     * Purpose Code (4a)
     * Standard purpose code (e.g., SALA, INTC).
     * Use with /CD/ sub-keyword.
     */
    case PURP = 'PURP';

    /**
     * Return Reason (4a)
     * Reason code for return/rejection.
     */
    case RTRN = 'RTRN';

    /**
     * Type Code (3!n/10x)
     * Accounting entry type.
     */
    case TYPE = 'TYPE';

    /**
     * Local Code (2a/3n)
     * Local operation code.
     */
    case CODE = 'CODE';

    // =========================================================================
    // Batch Information
    // =========================================================================
    /**
     * Number of Transactions (10!n)
     * Number of transactions in batch.
     */
    case NBTR = 'NBTR';

    /**
     * Urgency/Priority
     * Priority indicator.
     */
    case URGP = 'URGP';

    /**
     * Returns the description.
     */
    public function description(): string {
        return match ($this) {
            self::EREF => 'End-to-End Reference',
            self::PREF => 'Payment Information ID',
            self::IREF => 'Instruction ID',
            self::MREF => 'Mandate Reference',
            self::CRED => 'Creditor Identifier',
            self::TR   => 'Transaction Reference',
            self::REMI => 'Remittance Information',
            self::BENM => 'Beneficiary Name',
            self::ORDP => 'Ordering Party Name',
            self::ULTD => 'Ultimate Debtor Name',
            self::ULTC => 'Ultimate Creditor Name',
            self::INFO => 'Beneficiary Account Info',
            self::BBK  => 'Beneficiary Bank BIC',
            self::OBK  => 'Ordering Bank BIC',
            self::VACC => 'Virtual Account',
            self::OCMT => 'Original Currency Amount',
            self::CHGS => 'Charges',
            self::EXCH => 'Exchange Rate',
            self::PURP => 'Purpose Code',
            self::RTRN => 'Return Reason',
            self::TYPE => 'Type Code',
            self::CODE => 'Local Code',
            self::NBTR => 'Number of Transactions',
            self::URGP => 'Urgency/Priority',
        };
    }

    /**
     * Returns the German description.
     */
    public function descriptionDe(): string {
        return match ($this) {
            self::EREF => 'End-to-End-Referenz',
            self::PREF => 'Zahlungsinformations-ID',
            self::IREF => 'Instruktions-ID',
            self::MREF => 'Mandatsreferenz',
            self::CRED => 'Gläubiger-Identifikation',
            self::TR   => 'Transaktionsreferenz',
            self::REMI => 'Verwendungszweck',
            self::BENM => 'Begünstigter Name',
            self::ORDP => 'Auftraggeber Name',
            self::ULTD => 'Ursprünglicher Schuldner',
            self::ULTC => 'Ursprünglicher Gläubiger',
            self::INFO => 'Begünstigten-Konto',
            self::BBK  => 'Begünstigten-Bank BIC',
            self::OBK  => 'Auftraggeber-Bank BIC',
            self::VACC => 'Virtuelles Konto',
            self::OCMT => 'Ursprungsbetrag',
            self::CHGS => 'Gebühren',
            self::EXCH => 'Wechselkurs',
            self::PURP => 'Verwendungszweck-Code',
            self::RTRN => 'Rückgabegrund',
            self::TYPE => 'Buchungsart',
            self::CODE => 'Lokaler Code',
            self::NBTR => 'Anzahl Transaktionen',
            self::URGP => 'Priorität',
        };
    }

    /**
     * Returns the maximum length for this keyword's value.
     */
    public function maxLength(): int {
        return match ($this) {
            self::EREF, self::PREF, self::IREF, self::MREF, self::CRED, self::BBK, self::OBK => 35,
            self::TR                                                                         => 17,
            self::REMI                                                                       => 140,
            self::BENM, self::ORDP, self::ULTD, self::ULTC                                   => 70,
            self::INFO, self::VACC                                                           => 34,
            self::OCMT, self::CHGS                                                           => 18, // 3!a15d
            self::EXCH                                                                       => 12,
            self::PURP, self::RTRN                                                           => 4,
            self::TYPE                                                                       => 13,
            self::CODE                                                                       => 5,
            self::NBTR                                                                       => 10,
            self::URGP                                                                       => 1,
        };
    }

    /**
     * Returns whether this keyword has sub-keywords.
     */
    public function hasSubKeywords(): bool {
        return match ($this) {
            self::BENM, self::ORDP, self::ULTD, self::ULTC, self::PURP => true,
            default => false,
        };
    }

    /**
     * Returns the available sub-keywords.
     */
    public function subKeywords(): array {
        return match ($this) {
            self::BENM, self::ORDP, self::ULTD, self::ULTC => ['NAME', 'ADDR', 'CITY', 'CTRY'],
            self::PURP => ['CD', 'PRTRY'],
            default => [],
        };
    }

    /**
     * Formats a value with this keyword.
     */
    public function format(string $value, ?string $subKeyword = null): string {
        $value = substr($value, 0, $this->maxLength());
        if ($subKeyword !== null && $this->hasSubKeywords()) {
            return '/' . $this->value . '/' . $subKeyword . '/' . $value . '/';
        }
        return '/' . $this->value . '/' . $value . '/';
    }
}