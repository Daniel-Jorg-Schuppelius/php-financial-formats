<?php
/*
 * Created on   : Sun Dec 16 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DebitorsCreditors.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\DATEV\Documents;

use CommonToolkit\Entities\CSV\ColumnWidthConfig;
use CommonToolkit\Entities\CSV\HeaderLine;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV\Document;
use CommonToolkit\FinancialFormats\Entities\DATEV\MetaHeaderLine;
use CommonToolkit\Enums\Common\CSV\TruncationStrategy;
use CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields\Format\Category;
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\V700\DebitorsCreditorsHeaderField;
use CommonToolkit\FinancialFormats\Enums\DATEV\{
    AddresseeType,
    AddressType,
    CurrencyControl,
    DirectDebitIndicator,
    DunningIndicator,
    InterestCalculationIndicator,
    ItemLock,
    Language,
    OutputTarget,
    PaymentCarrierIndicator,
    StatementIndicator
};
use CommonToolkit\Enums\CountryCode;
use RuntimeException;

/**
 * DATEV-Debitoren/Kreditoren-Dokument.
 * Special document class for Debitors/Creditors format (Category 16).
 * 
 * Die Spaltenbreiten werden automatisch basierend auf den DATEV-Spezifikationen
 * aus DebitorsCreditorsHeaderField::getMaxLength() angewendet.
 */
final class DebitorsCreditors extends Document {
    public function __construct(?MetaHeaderLine $metaHeader, ?HeaderLine $header, array $rows = []) {
        parent::__construct($metaHeader, $header, $rows);
    }

    /**
     * Erstellt eine ColumnWidthConfig basierend auf den DATEV-Spezifikationen.
     * Maximum field lengths are derived from DebitorsCreditorsHeaderField::getMaxLength().
     * 
     * @param TruncationStrategy $strategy Truncation strategy (Default: TRUNCATE for DATEV conformity)
     * @return ColumnWidthConfig
     */
    public static function createDatevColumnWidthConfig(TruncationStrategy $strategy = TruncationStrategy::TRUNCATE): ColumnWidthConfig {
        $config = new ColumnWidthConfig(null, $strategy);

        foreach (DebitorsCreditorsHeaderField::ordered() as $index => $field) {
            $maxLength = $field->getMaxLength();
            if ($maxLength !== null) {
                $config->setColumnWidth($index, $maxLength);
            }
        }

        return $config;
    }

    /**
     * Returns the DATEV category for this document type.
     */
    public function getCategory(): Category {
        return Category::DebitorenKreditoren;
    }

    /**
     * Returns the DATEV format type.
     */
    public function getFormatType(): string {
        return Category::DebitorenKreditoren->nameValue();
    }

    /**
     * Validiert Debitoren/Kreditoren-spezifische Regeln.
     */
    public function validate(): void {
        parent::validate();

        $metaFields = $this->getMetaHeader()?->getFields() ?? [];
        if (count($metaFields) > 2 && (int)$metaFields[2]->getValue() !== 16) {
            throw new RuntimeException('Ungültige Kategorie für Debitoren/Kreditoren-Dokument. Erwartet: 16');
        }
    }


    // ==== DEBITOREN/KREDITOREN-SPEZIFISCHE ENUM GETTER/SETTER ====

    // ---- ADDRESSEE FIELDS ----

    /**
     * Returns the addressee type of a debitor/creditor (field 7).
     * 0 = not specified (Default: company), 1 = natural person, 2 = company
     */
    public function getAddresseeTypeValue(int $rowIndex): ?AddresseeType {
        return parent::getAddresseeType($rowIndex, DebitorsCreditorsHeaderField::Adressattyp->getPosition());
    }

    /**
     * Setzt den Adressatentyp eines Debitors/Kreditors (Feld 7).
     */
    public function setAddresseeTypeValue(int $rowIndex, AddresseeType $addresseeType): void {
        parent::setAddresseeType($rowIndex, DebitorsCreditorsHeaderField::Adressattyp->getPosition(), $addresseeType);
    }

    /**
     * Returns the address type (field 15): STR=street, PF=P.O. box, GK=major customer.
     */
    public function getCorrespondenceAddressType(int $rowIndex): ?AddressType {
        return parent::getAddressType($rowIndex, DebitorsCreditorsHeaderField::Adressart->getPosition());
    }

    /**
     * Setzt die Adressart (Feld 15).
     */
    public function setCorrespondenceAddressType(int $rowIndex, AddressType $addressType): void {
        parent::setAddressType($rowIndex, DebitorsCreditorsHeaderField::Adressart->getPosition(), $addressType);
    }

    /**
     * Returns the address type of the invoice address (field 153).
     */
    public function getInvoiceAddressType(int $rowIndex): ?AddressType {
        return parent::getAddressType($rowIndex, DebitorsCreditorsHeaderField::AdressartRechnungsadresse->getPosition());
    }

    /**
     * Setzt die Adressart der Rechnungsadresse (Feld 153).
     */
    public function setInvoiceAddressType(int $rowIndex, AddressType $addressType): void {
        parent::setAddressType($rowIndex, DebitorsCreditorsHeaderField::AdressartRechnungsadresse->getPosition(), $addressType);
    }

    // ---- COUNTRY FIELDS ----

    /**
     * Returns the EU country of a debitor/creditor (field 9).
     */
    public function getEUCountry(int $rowIndex): ?CountryCode {
        return $this->getCountryCode($rowIndex, DebitorsCreditorsHeaderField::EULand->getPosition());
    }

    /**
     * Setzt das EU-Land eines Debitors/Kreditors (Feld 9).
     */
    public function setEUCountry(int $rowIndex, CountryCode $countryCode): void {
        $this->setCountryCode($rowIndex, DebitorsCreditorsHeaderField::EULand->getPosition(), $countryCode);
    }

    /**
     * Returns the country of a debitor/creditor (field 20).
     */
    public function getCountry(int $rowIndex): ?CountryCode {
        return $this->getCountryCode($rowIndex, DebitorsCreditorsHeaderField::Land->getPosition());
    }

    /**
     * Setzt das Land eines Debitors/Kreditors (Feld 20).
     */
    public function setCountry(int $rowIndex, CountryCode $countryCode): void {
        $this->setCountryCode($rowIndex, DebitorsCreditorsHeaderField::Land->getPosition(), $countryCode);
    }

    /**
     * Returns the country of the invoice address (field 158).
     */
    public function getInvoiceCountry(int $rowIndex): ?CountryCode {
        return $this->getCountryCode($rowIndex, DebitorsCreditorsHeaderField::LandRechnungsadresse->getPosition());
    }

    /**
     * Setzt das Land der Rechnungsadresse (Feld 158).
     */
    public function setInvoiceCountry(int $rowIndex, CountryCode $countryCode): void {
        $this->setCountryCode($rowIndex, DebitorsCreditorsHeaderField::LandRechnungsadresse->getPosition(), $countryCode);
    }

    // ---- COMMUNICATION AND OUTPUT FIELDS ----

    /**
     * Returns the language (field 101).
     * 1=German, 4=French, 5=English, 10=Spanish, 19=Italian
     */
    public function getLanguageValue(int $rowIndex): ?Language {
        return parent::getLanguage($rowIndex, DebitorsCreditorsHeaderField::Sprache->getPosition());
    }

    /**
     * Setzt die Sprache (Feld 101).
     */
    public function setLanguageValue(int $rowIndex, Language $language): void {
        parent::setLanguage($rowIndex, DebitorsCreditorsHeaderField::Sprache->getPosition(), $language);
    }

    /**
     * Returns the output target (field 106).
     * 1=Druck, 2=Telefax, 3=E-Mail
     */
    public function getOutputTargetValue(int $rowIndex): ?OutputTarget {
        return parent::getOutputTarget($rowIndex, DebitorsCreditorsHeaderField::Ausgabeziel->getPosition());
    }

    /**
     * Setzt das Ausgabeziel (Feld 106).
     */
    public function setOutputTargetValue(int $rowIndex, OutputTarget $outputTarget): void {
        parent::setOutputTarget($rowIndex, DebitorsCreditorsHeaderField::Ausgabeziel->getPosition(), $outputTarget);
    }

    // ---- CURRENCY AND MISCELLANEOUS ACCOUNT ----

    /**
     * Returns the currency control (field 107).
     * 0=payments in input currency, 2=output in EUR
     */
    public function getCurrencyControlValue(int $rowIndex): ?CurrencyControl {
        return parent::getCurrencyControl($rowIndex, DebitorsCreditorsHeaderField::Waehrungssteuerung->getPosition());
    }

    /**
     * Sets the currency control (field 107).
     */
    public function setCurrencyControlValue(int $rowIndex, CurrencyControl $currencyControl): void {
        parent::setCurrencyControl($rowIndex, DebitorsCreditorsHeaderField::Waehrungssteuerung->getPosition(), $currencyControl);
    }

    /**
     * Returns the sundry account indicator (field 105).
     * 0=Nein, 1=Ja
     */
    public function getMiscellaneousAccount(int $rowIndex): ?ItemLock {
        return parent::getItemLock($rowIndex, DebitorsCreditorsHeaderField::DiverseKonto->getPosition());
    }

    /**
     * Setzt das Diverse-Konto-Kennzeichen (Feld 105).
     */
    public function setMiscellaneousAccount(int $rowIndex, ItemLock $itemLock): void {
        parent::setItemLock($rowIndex, DebitorsCreditorsHeaderField::DiverseKonto->getPosition(), $itemLock);
    }

    // ---- DUNNING AND STATEMENT ----

    /**
     * Returns the dunning indicator (field 121).
     * 0=Keine Angaben, 1=1. Mahnung, 2=2. Mahnung, 3=1.+2. Mahnung,
     * 4=3. Mahnung, 6=2.+3. Mahnung, 7=1.,2.+3. Mahnung, 9=keine Mahnung
     */
    public function getDunningIndicatorValue(int $rowIndex): ?DunningIndicator {
        return parent::getDunningIndicator($rowIndex, DebitorsCreditorsHeaderField::Mahnung->getPosition());
    }

    /**
     * Setzt das Mahnungs-Kennzeichen (Feld 121).
     */
    public function setDunningIndicatorValue(int $rowIndex, DunningIndicator $indicator): void {
        parent::setDunningIndicator($rowIndex, DebitorsCreditorsHeaderField::Mahnung->getPosition(), $indicator);
    }

    /**
     * Returns the statement indicator (field 122).
     * 1=all items, 2=only dunnable, 3=all dunnable due, 9=no statement
     */
    public function getStatementIndicatorValue(int $rowIndex): ?StatementIndicator {
        return parent::getStatementIndicator($rowIndex, DebitorsCreditorsHeaderField::Kontoauszug->getPosition());
    }

    /**
     * Setzt das Kontoauszugs-Kennzeichen (Feld 122).
     */
    public function setStatementIndicatorValue(int $rowIndex, StatementIndicator $indicator): void {
        parent::setStatementIndicator($rowIndex, DebitorsCreditorsHeaderField::Kontoauszug->getPosition(), $indicator);
    }

    // ---- INTEREST CALCULATION ----

    /**
     * Returns the interest calculation indicator (field 129).
     * 0=MPD keying, 1=fixed rate, 2=rate via scale, 9=no calculation
     */
    public function getInterestCalculationIndicatorValue(int $rowIndex): ?InterestCalculationIndicator {
        return parent::getInterestCalculationIndicator($rowIndex, DebitorsCreditorsHeaderField::Zinsberechnung->getPosition());
    }

    /**
     * Setzt das Zinsberechnungs-Kennzeichen (Feld 129).
     */
    public function setInterestCalculationIndicatorValue(int $rowIndex, InterestCalculationIndicator $indicator): void {
        parent::setInterestCalculationIndicator($rowIndex, DebitorsCreditorsHeaderField::Zinsberechnung->getPosition(), $indicator);
    }

    // ---- DIRECT DEBIT AND PAYMENT CARRIER ----

    /**
     * Returns the direct debit indicator (field 133).
     * 0=keine Angabe, 7=SEPA-Einzelrechnung, 8=SEPA-Sammelrechnung, 9=kein Lastschriftverfahren
     */
    public function getDirectDebitIndicatorValue(int $rowIndex): ?DirectDebitIndicator {
        return parent::getDirectDebitIndicator($rowIndex, DebitorsCreditorsHeaderField::Lastschrift->getPosition());
    }

    /**
     * Setzt das Lastschrift-Kennzeichen (Feld 133).
     */
    public function setDirectDebitIndicatorValue(int $rowIndex, DirectDebitIndicator $indicator): void {
        parent::setDirectDebitIndicator($rowIndex, DebitorsCreditorsHeaderField::Lastschrift->getPosition(), $indicator);
    }

    /**
     * Returns the payment carrier indicator (field 136).
     * 0=not specified, 5=single cheque, 6=collective cheque, 7=SEPA transfer single,
     * 8=SEPA transfer collective, 9=no transfers/cheques
     */
    public function getPaymentCarrierIndicatorValue(int $rowIndex): ?PaymentCarrierIndicator {
        return parent::getPaymentCarrierIndicator($rowIndex, DebitorsCreditorsHeaderField::Zahlungstraeger->getPosition());
    }

    /**
     * Sets the payment carrier indicator (field 136).
     */
    public function setPaymentCarrierIndicatorValue(int $rowIndex, PaymentCarrierIndicator $indicator): void {
        parent::setPaymentCarrierIndicator($rowIndex, DebitorsCreditorsHeaderField::Zahlungstraeger->getPosition(), $indicator);
    }


    // ==== CONVENIENCE METHODS ====

    /**
     * Checks if a debitor/creditor is located in an EU country.
     */
    public function isEUResident(int $rowIndex): bool {
        $euCountry = $this->getEUCountry($rowIndex);
        $country = $this->getCountry($rowIndex);

        return ($euCountry?->isEU() ?? false) || ($country?->isEU() ?? false);
    }

    /**
     * Checks if SEPA direct debit is enabled for this debitor/creditor.
     */
    public function isSepaDirectDebitEnabled(int $rowIndex): bool {
        return $this->getDirectDebitIndicatorValue($rowIndex)?->isSepaDirectDebit() ?? false;
    }

    /**
     * Checks if dunning is enabled for this debitor/creditor.
     */
    public function isDunningEnabled(int $rowIndex): bool {
        return $this->getDunningIndicatorValue($rowIndex)?->hasDunning() ?? false;
    }

    /**
     * Checks if this is a natural person.
     */
    public function isNaturalPerson(int $rowIndex): bool {
        return $this->getAddresseeTypeValue($rowIndex)?->isNaturalPerson() ?? false;
    }

    /**
     * Checks if this is a company.
     */
    public function isCompany(int $rowIndex): bool {
        return $this->getAddresseeTypeValue($rowIndex)?->isCompany() ?? true; // Default ist Unternehmen
    }

    /**
     * Checks if statement is enabled.
     */
    public function isStatementEnabled(int $rowIndex): bool {
        return $this->getStatementIndicatorValue($rowIndex)?->isEnabled() ?? false;
    }

    /**
     * Checks if interest calculation is enabled.
     */
    public function isInterestCalculationEnabled(int $rowIndex): bool {
        return $this->getInterestCalculationIndicatorValue($rowIndex)?->isEnabled() ?? false;
    }

    /**
     * Checks if this is a sundry account.
     */
    public function isMiscellaneousAccount(int $rowIndex): bool {
        return $this->getMiscellaneousAccount($rowIndex)?->isLocked() ?? false;
    }

    /**
     * Returns all debitors/creditors of a specific country.
     */
    public function getRowsByCountry(CountryCode $country): array {
        $result = [];

        foreach ($this->rows as $index => $row) {
            if ($this->getCountry($index) === $country || $this->getEUCountry($index) === $country) {
                $result[] = $index;
            }
        }

        return $result;
    }

    /**
     * Returns all natural persons.
     */
    public function getNaturalPersons(): array {
        $result = [];

        foreach ($this->rows as $index => $row) {
            if ($this->isNaturalPerson($index)) {
                $result[] = $index;
            }
        }

        return $result;
    }

    /**
     * Returns all companies.
     */
    public function getCompanies(): array {
        $result = [];

        foreach ($this->rows as $index => $row) {
            if ($this->isCompany($index)) {
                $result[] = $index;
            }
        }

        return $result;
    }
}
