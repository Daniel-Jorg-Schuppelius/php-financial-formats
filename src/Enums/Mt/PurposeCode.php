<?php
/*
 * Created on   : Wed Jan 08 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PurposeCode.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\Mt;

/**
 * Purpose Codes for payment transactions (ISO 20022 ExternalPurpose1Code).
 * 
 * Used in SEPA/SWIFT transactions to identify the purpose of payment.
 * Format: 4 alphabetic characters (4!a).
 * 
 * @see ISO 20022 External Code Sets
 */
enum PurposeCode: string {
    // =========================================================================
    // Salary & Pension (SALA, PENS, BONU)
    // =========================================================================
    /**
     * Salary Payment
     */
    case SALA = 'SALA';

    /**
     * Pension Payment
     */
    case PENS = 'PENS';

    /**
     * Bonus Payment
     */
    case BONU = 'BONU';

    /**
     * Commission
     */
    case COMM = 'COMM';

    /**
     * Dividend Payment
     */
    case DIVD = 'DIVD';

    // =========================================================================
    // Trade & Commerce (GDDS, SCVE, SUPP)
    // =========================================================================
    /**
     * Purchase/Sale of Goods
     */
    case GDDS = 'GDDS';

    /**
     * Purchase/Sale of Services
     */
    case SCVE = 'SCVE';

    /**
     * Supplier Payment
     */
    case SUPP = 'SUPP';

    /**
     * Trade Services
     */
    case TRAD = 'TRAD';

    // =========================================================================
    // Tax & Government (TAXS, VATX, GOVT)
    // =========================================================================
    /**
     * Tax Payment
     */
    case TAXS = 'TAXS';

    /**
     * VAT Payment
     */
    case VATX = 'VATX';

    /**
     * Government Payment
     */
    case GOVT = 'GOVT';

    /**
     * Social Security
     */
    case SSBE = 'SSBE';

    // =========================================================================
    // Financial Services (INTC, INSU, LOAN)
    // =========================================================================
    /**
     * Intra-Company Payment
     */
    case INTC = 'INTC';

    /**
     * Insurance Premium
     */
    case INSU = 'INSU';

    /**
     * Loan Payment
     */
    case LOAN = 'LOAN';

    /**
     * Interest Payment
     */
    case INTE = 'INTE';

    /**
     * Investment
     */
    case INVS = 'INVS';

    // =========================================================================
    // Utilities & Rent (ELEC, GASB, RENT)
    // =========================================================================
    /**
     * Electricity Bill
     */
    case ELEC = 'ELEC';

    /**
     * Gas Bill
     */
    case GASB = 'GASB';

    /**
     * Water Bill
     */
    case WTER = 'WTER';

    /**
     * Rent Payment
     */
    case RENT = 'RENT';

    /**
     * Telecommunications
     */
    case PHON = 'PHON';

    // =========================================================================
    // Other Common Codes
    // =========================================================================
    /**
     * Charity Payment
     */
    case CHAR = 'CHAR';

    /**
     * Study Costs
     */
    case STDY = 'STDY';

    /**
     * Medical Services
     */
    case MDCS = 'MDCS';

    /**
     * Travel
     */
    case TRVL = 'TRVL';

    /**
     * Other
     */
    case OTHR = 'OTHR';

    /**
     * Returns the description.
     */
    public function description(): string {
        return match ($this) {
            self::SALA => 'Salary Payment',
            self::PENS => 'Pension Payment',
            self::BONU => 'Bonus Payment',
            self::COMM => 'Commission',
            self::DIVD => 'Dividend Payment',
            self::GDDS => 'Purchase/Sale of Goods',
            self::SCVE => 'Purchase/Sale of Services',
            self::SUPP => 'Supplier Payment',
            self::TRAD => 'Trade Services',
            self::TAXS => 'Tax Payment',
            self::VATX => 'VAT Payment',
            self::GOVT => 'Government Payment',
            self::SSBE => 'Social Security',
            self::INTC => 'Intra-Company Payment',
            self::INSU => 'Insurance Premium',
            self::LOAN => 'Loan Payment',
            self::INTE => 'Interest Payment',
            self::INVS => 'Investment',
            self::ELEC => 'Electricity Bill',
            self::GASB => 'Gas Bill',
            self::WTER => 'Water Bill',
            self::RENT => 'Rent Payment',
            self::PHON => 'Telecommunications',
            self::CHAR => 'Charity Payment',
            self::STDY => 'Study Costs',
            self::MDCS => 'Medical Services',
            self::TRVL => 'Travel',
            self::OTHR => 'Other',
        };
    }

    /**
     * Returns the German description.
     */
    public function descriptionDe(): string {
        return match ($this) {
            self::SALA => 'Gehaltszahlung',
            self::PENS => 'Rentenzahlung',
            self::BONU => 'Bonuszahlung',
            self::COMM => 'Provision',
            self::DIVD => 'Dividendenzahlung',
            self::GDDS => 'Warenhandel',
            self::SCVE => 'Dienstleistungen',
            self::SUPP => 'Lieferantenzahlung',
            self::TRAD => 'Handelsdienstleistungen',
            self::TAXS => 'Steuerzahlung',
            self::VATX => 'Umsatzsteuer',
            self::GOVT => 'Behördenzahlung',
            self::SSBE => 'Sozialversicherung',
            self::INTC => 'Konzerninterner Transfer',
            self::INSU => 'Versicherungsprämie',
            self::LOAN => 'Kredittilgung',
            self::INTE => 'Zinszahlung',
            self::INVS => 'Investition',
            self::ELEC => 'Stromrechnung',
            self::GASB => 'Gasrechnung',
            self::WTER => 'Wasserrechnung',
            self::RENT => 'Mietzahlung',
            self::PHON => 'Telekommunikation',
            self::CHAR => 'Spende',
            self::STDY => 'Studienkosten',
            self::MDCS => 'Medizinische Leistungen',
            self::TRVL => 'Reise',
            self::OTHR => 'Sonstige',
        };
    }

    /**
     * Returns the category of this purpose code.
     */
    public function category(): string {
        return match ($this) {
            self::SALA, self::PENS, self::BONU, self::COMM, self::DIVD => 'salary',
            self::GDDS, self::SCVE, self::SUPP, self::TRAD             => 'trade',
            self::TAXS, self::VATX, self::GOVT, self::SSBE             => 'government',
            self::INTC, self::INSU, self::LOAN, self::INTE, self::INVS => 'financial',
            self::ELEC, self::GASB, self::WTER, self::RENT, self::PHON => 'utilities',
            default => 'other',
        };
    }

    /**
     * Creates from a string.
     */
    public static function tryFromString(string $code): ?self {
        return self::tryFrom(strtoupper(trim($code)));
    }
}
