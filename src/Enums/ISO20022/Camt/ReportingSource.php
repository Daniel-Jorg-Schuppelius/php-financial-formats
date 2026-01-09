<?php
/*
 * Created on   : Fri Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : ReportingSource.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 * 
 * Auto-generated from XSD: ISO_ExternalReportingSource1Code
 * Do not edit manually - regenerate with: php tools/generate-camt-enums.php
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\ISO20022\Camt;

/**
 * ReportingSource - ISO 20022 External Code List
 * 
 * Generiert aus: ISO_ExternalReportingSource1Code
 * @see https://www.iso20022.org/external_code_list.page
 */
enum ReportingSource: string {
    /**
     * ACCT - Accounting
     * Statement or Report is based on accounting data.
     */
    case ACCT = 'ACCT';

    /**
     * ARPF - Account Reconciliation System - Full
     * An account reconciliation system that provides full reconciliation that usually addresses checks
     */
    case ARPF = 'ARPF';

    /**
     * ARPP - Account Reconciliation System - Partial
     * An account reconciliation system that provides partial reconciliation that usually addresses checks
     */
    case ARPP = 'ARPP';

    /**
     * CTDB - Controlled Disbursement System
     * A sub-application that reports presentment totals
     */
    case CTDB = 'CTDB';

    /**
     * CUST - Custody
     * Statement or Report is based on custody data.
     */
    case CUST = 'CUST';

    /**
     * DEPT - Deposit System
     * Cash or deposit accounting system
     */
    case DEPT = 'DEPT';

    /**
     * DPCS - Deposit Concentration System
     * Deposit system that reports what has been collected from various financial institutions
     */
    case DPCS = 'DPCS';

    /**
     * LKBX - Lockbox
     * Processing system that captures and reports check data in a lockbox environment.
     */
    case LKBX = 'LKBX';

    /**
     * RCPT - Receipts
     * A system that reports consolidated remittance information obtained from various , i.e., ACH, wire...
     */
    case RCPT = 'RCPT';

    /**
     * Gibt den Namen/Titel des Codes zurück.
     */
    public function name(): string {
        return match ($this) {
            self::ACCT => 'Accounting',
            self::ARPF => 'Account Reconciliation System - Full',
            self::ARPP => 'Account Reconciliation System - Partial',
            self::CTDB => 'Controlled Disbursement System',
            self::CUST => 'Custody',
            self::DEPT => 'Deposit System',
            self::DPCS => 'Deposit Concentration System',
            self::LKBX => 'Lockbox',
            self::RCPT => 'Receipts',
        };
    }

    /**
     * Gibt die Definition/Beschreibung des Codes zurück.
     */
    public function definition(): string {
        return match ($this) {
            self::ACCT => 'Statement or Report is based on accounting data.',
            self::ARPF => 'An account reconciliation system that provides full reconciliation that usually addresses checks',
            self::ARPP => 'An account reconciliation system that provides partial reconciliation that usually addresses checks',
            self::CTDB => 'A sub-application that reports presentment totals',
            self::CUST => 'Statement or Report is based on custody data.',
            self::DEPT => 'Cash or deposit accounting system',
            self::DPCS => 'Deposit system that reports what has been collected from various financial institutions',
            self::LKBX => 'Processing system that captures and reports check data in a lockbox environment.',
            self::RCPT => 'A system that reports consolidated remittance information obtained from various , i.e., ACH, wires, lockbox, etc.',
        };
    }

    /**
     * Factory-Methode aus String.
     */
    public static function fromString(string $value): ?self {
        return self::tryFrom(strtoupper(trim($value)));
    }

    /**
     * Prüft ob der Wert ein gültiger Code ist.
     */
    public static function isValid(string $value): bool {
        return self::tryFrom(strtoupper(trim($value))) !== null;
    }
}
