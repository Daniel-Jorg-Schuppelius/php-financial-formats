<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : OrganisationIdentification.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 * 
 * Auto-generated from XSD: ISO_ExternalOrganisationIdentification1Code
 * Do not edit manually - regenerate with: php tools/generate-camt-enums.php
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\Camt;

/**
 * OrganisationIdentification - ISO 20022 External Code List
 * 
 * Generiert aus: ISO_ExternalOrganisationIdentification1Code
 * @see https://www.iso20022.org/external_code_list.page
 */
enum OrganisationIdentification: string {
    /**
     * BANK - BankPartyIdentification
     * Unique and unambiguous assignment made by a specific bank or similar financial institution to ide...
     */
    case BANK = 'BANK';

    /**
     * CBID - Central Bank Identification Number
     * A unique identification number assigned by a central bank to identify an organisation.
     */
    case CBID = 'CBID';

    /**
     * CHID - Clearing Identification Number
     * A unique identification number assigned by a clearing house to identify an organisation
     */
    case CHID = 'CHID';

    /**
     * COID - CountryIdentificationCode
     * Country authority given organisation identification (e.g., corporate registration number)
     */
    case COID = 'COID';

    /**
     * CUST - CustomerNumber
     * Number assigned by an issuer to identify a customer. Number assigned by a party to identify a cre...
     */
    case CUST = 'CUST';

    /**
     * DUNS - Data Universal Numbering System
     * A unique identification number provided by Dun & Bradstreet to identify an organisation.
     */
    case DUNS = 'DUNS';

    /**
     * EMPL - EmployerIdentificationNumber
     * Number assigned by a registration authority to an employer.
     */
    case EMPL = 'EMPL';

    /**
     * GS1G - GS1GLNIdentifier
     * Global Location Number. A non-significant reference number used to identify legal entities, funct...
     */
    case GS1G = 'GS1G';

    /**
     * SREN - SIREN
     * The SIREN number is a 9 digit code assigned by INSEE, the French National Institute for Statistic...
     */
    case SREN = 'SREN';

    /**
     * SRET - SIRET
     * The SIRET number is a 14 digit code assigned by INSEE, the French National Institute for Statisti...
     */
    case SRET = 'SRET';

    /**
     * TXID - TaxIdentificationNumber
     * Number assigned by a tax authority to identify an organisation.
     */
    case TXID = 'TXID';

    /**
     * Returns the name/title of the code.
     */
    public function name(): string {
        return match ($this) {
            self::BANK => 'BankPartyIdentification',
            self::CBID => 'Central Bank Identification Number',
            self::CHID => 'Clearing Identification Number',
            self::COID => 'CountryIdentificationCode',
            self::CUST => 'CustomerNumber',
            self::DUNS => 'Data Universal Numbering System',
            self::EMPL => 'EmployerIdentificationNumber',
            self::GS1G => 'GS1GLNIdentifier',
            self::SREN => 'SIREN',
            self::SRET => 'SIRET',
            self::TXID => 'TaxIdentificationNumber',
        };
    }

    /**
     * Returns the definition/description of the code.
     */
    public function definition(): string {
        return match ($this) {
            self::BANK => 'Unique and unambiguous assignment made by a specific bank or similar financial institution to identify a relationship as defined between the bank and its client.',
            self::CBID => 'A unique identification number assigned by a central bank to identify an organisation.',
            self::CHID => 'A unique identification number assigned by a clearing house to identify an organisation',
            self::COID => 'Country authority given organisation identification (e.g., corporate registration number)',
            self::CUST => 'Number assigned by an issuer to identify a customer. Number assigned by a party to identify a creditor or debtor relationship.',
            self::DUNS => 'A unique identification number provided by Dun & Bradstreet to identify an organisation.',
            self::EMPL => 'Number assigned by a registration authority to an employer.',
            self::GS1G => 'Global Location Number. A non-significant reference number used to identify legal entities, functional entities, or physical entities according to GS1 numbering scheme rules.The number is used to r...',
            self::SREN => 'The SIREN number is a 9 digit code assigned by INSEE, the French National Institute for Statistics and Economic Studies, to identify an organisation in France.',
            self::SRET => 'The SIRET number is a 14 digit code assigned by INSEE, the French National Institute for Statistics and Economic Studies, to identify an organisation unit in France. It consists of the SIREN number...',
            self::TXID => 'Number assigned by a tax authority to identify an organisation.',
        };
    }

    /**
     * Factory-Methode aus String.
     */
    public static function fromString(string $value): ?self {
        return self::tryFrom(strtoupper(trim($value)));
    }

    /**
     * Checks if the value is a valid code.
     */
    public static function isValid(string $value): bool {
        return self::tryFrom(strtoupper(trim($value))) !== null;
    }
}
