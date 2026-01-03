<?php
/*
 * Created on   : Sun Dec 16 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : VariousAddresses.php
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
use CommonToolkit\Enums\CountryCode;
use CommonToolkit\FinancialFormats\Enums\DATEV\AddresseeType;
use CommonToolkit\FinancialFormats\Enums\DATEV\AddressType;
use CommonToolkit\FinancialFormats\Enums\DATEV\ItemLock;
use CommonToolkit\FinancialFormats\Enums\DATEV\Language;
use CommonToolkit\FinancialFormats\Enums\DATEV\OutputTarget;
use CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields\Format\Category;
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\V700\VariousAddressesHeaderField;

/**
 * DATEV-Diverse Adressen-Dokument.
 * Special document class for Various Addresses format (Category 48).
 * 
 * Die Spaltenbreiten werden automatisch basierend auf den DATEV-Spezifikationen
 * aus VariousAddressesHeaderField::getMaxLength() angewendet.
 */
final class VariousAddresses extends Document {
    public function __construct(
        ?MetaHeaderLine $metaHeader,
        ?HeaderLine $header,
        array $rows = []
    ) {
        parent::__construct($metaHeader, $header, $rows);
    }

    /**
     * Erstellt eine ColumnWidthConfig basierend auf den DATEV-Spezifikationen.
     * Maximum field lengths are derived from VariousAddressesHeaderField::getMaxLength().
     * 
     * @param TruncationStrategy $strategy Truncation strategy (Default: TRUNCATE for DATEV conformity)
     * @return ColumnWidthConfig
     */
    public static function createDatevColumnWidthConfig(TruncationStrategy $strategy = TruncationStrategy::TRUNCATE): ColumnWidthConfig {
        $config = new ColumnWidthConfig(null, $strategy);

        foreach (VariousAddressesHeaderField::ordered() as $index => $field) {
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
        return Category::DiverseAdressen;
    }

    /**
     * Returns the DATEV format type.
     */
    public function getFormatType(): string {
        return Category::DiverseAdressen->nameValue();
    }

    /**
     * Validiert Diverse Adressen-spezifische Regeln.
     */
    public function validate(): void {
        parent::validate();

        $metaFields = $this->getMetaHeader()?->getFields() ?? [];
        if (count($metaFields) > 2 && (int)$metaFields[2]->getValue() !== 48) {
            throw new \RuntimeException('Ungültige Kategorie für Diverse Adressen-Dokument. Erwartet: 48');
        }
    }

    // ==== ADDRESSEE FIELDS ====

    /**
     * Returns the addressee type (field 10).
     * 0 = not specified (Default: company), 1 = natural person, 2 = company
     */
    public function getAddresseeTypeValue(int $rowIndex): ?AddresseeType {
        return parent::getAddresseeType($rowIndex, VariousAddressesHeaderField::Adressattyp->getPosition());
    }

    /**
     * Setzt den Adressatentyp (Feld 10).
     */
    public function setAddresseeTypeValue(int $rowIndex, AddresseeType $addresseeType): void {
        parent::setAddresseeType($rowIndex, VariousAddressesHeaderField::Adressattyp->getPosition(), $addresseeType);
    }

    /**
     * Returns the address type (field 15): STR=street, PF=P.O. box, GK=major customer.
     */
    public function getCorrespondenceAddressType(int $rowIndex): ?AddressType {
        return parent::getAddressType($rowIndex, VariousAddressesHeaderField::Adressart->getPosition());
    }

    /**
     * Setzt die Adressart (Feld 15).
     */
    public function setCorrespondenceAddressType(int $rowIndex, AddressType $addressType): void {
        parent::setAddressType($rowIndex, VariousAddressesHeaderField::Adressart->getPosition(), $addressType);
    }

    /**
     * Returns the address type of the invoice address (field 29).
     */
    public function getInvoiceAddressType(int $rowIndex): ?AddressType {
        return parent::getAddressType($rowIndex, VariousAddressesHeaderField::AdressartRechnungsadresse->getPosition());
    }

    /**
     * Setzt die Adressart der Rechnungsadresse (Feld 29).
     */
    public function setInvoiceAddressType(int $rowIndex, AddressType $addressType): void {
        parent::setAddressType($rowIndex, VariousAddressesHeaderField::AdressartRechnungsadresse->getPosition(), $addressType);
    }

    // ==== COUNTRY FIELDS ====

    /**
     * Returns the country (field 20).
     */
    public function getCountry(int $rowIndex): ?CountryCode {
        return parent::getCountryCode($rowIndex, VariousAddressesHeaderField::Land->getPosition());
    }

    /**
     * Setzt das Land (Feld 20).
     */
    public function setCountry(int $rowIndex, CountryCode $countryCode): void {
        parent::setCountryCode($rowIndex, VariousAddressesHeaderField::Land->getPosition(), $countryCode);
    }

    /**
     * Returns the country of the invoice address (field 34).
     */
    public function getInvoiceCountry(int $rowIndex): ?CountryCode {
        return parent::getCountryCode($rowIndex, VariousAddressesHeaderField::LandRechnungsadresse->getPosition());
    }

    /**
     * Setzt das Land der Rechnungsadresse (Feld 34).
     */
    public function setInvoiceCountry(int $rowIndex, CountryCode $countryCode): void {
        parent::setCountryCode($rowIndex, VariousAddressesHeaderField::LandRechnungsadresse->getPosition(), $countryCode);
    }

    // ==== CORRESPONDENCE ADDRESS FLAG ====

    /**
     * Returns the correspondence address indicator (field 25).
     * 1 = Kennzeichnung Korrespondenzadresse
     */
    public function getCorrespondenceAddressFlag(int $rowIndex): ?ItemLock {
        return parent::getItemLock($rowIndex, VariousAddressesHeaderField::KennzKorrespondenzadresse->getPosition());
    }

    /**
     * Setzt das Korrespondenzadresse-Kennzeichen (Feld 25).
     */
    public function setCorrespondenceAddressFlag(int $rowIndex, ItemLock $flag): void {
        parent::setItemLock($rowIndex, VariousAddressesHeaderField::KennzKorrespondenzadresse->getPosition(), $flag);
    }

    /**
     * Checks if the address is marked as correspondence address.
     */
    public function isCorrespondenceAddress(int $rowIndex): bool {
        $flag = $this->getCorrespondenceAddressFlag($rowIndex);
        return $flag !== null && $flag->isLocked();
    }

    // ==== COMMUNICATION AND OUTPUT FIELDS ====

    /**
     * Returns the language (field 169).
     * 1=German, 4=French, 5=English, 10=Spanish, 19=Italian
     */
    public function getLanguageValue(int $rowIndex): ?Language {
        return parent::getLanguage($rowIndex, VariousAddressesHeaderField::Sprache->getPosition());
    }

    /**
     * Setzt die Sprache (Feld 169).
     */
    public function setLanguageValue(int $rowIndex, Language $language): void {
        parent::setLanguage($rowIndex, VariousAddressesHeaderField::Sprache->getPosition(), $language);
    }

    /**
     * Returns the output target (field 170).
     * Bei Diverse Adressen: 1=Druck, 3=E-Mail (kein Fax!)
     */
    public function getOutputTargetValue(int $rowIndex): ?OutputTarget {
        return parent::getOutputTarget($rowIndex, VariousAddressesHeaderField::Ausgabeziel->getPosition());
    }

    /**
     * Setzt das Ausgabeziel (Feld 170).
     * Bei Diverse Adressen nur 1=Druck oder 3=E-Mail erlaubt (kein Fax!).
     *
     * @throws \InvalidArgumentException if fax is selected as output target
     */
    public function setOutputTargetValue(int $rowIndex, OutputTarget $outputTarget): void {
        if ($outputTarget === OutputTarget::FAX) {
            throw new \InvalidArgumentException('Fax ist für Diverse Adressen nicht als Ausgabeziel verfügbar.');
        }
        parent::setOutputTarget($rowIndex, VariousAddressesHeaderField::Ausgabeziel->getPosition(), $outputTarget);
    }

    // ==== MAIN BANK CONNECTION FLAGS (10 banks) ====

    /**
     * Returns the main bank account indicator for bank 1 (field 61).
     */
    public function getMainBankConnectionFlag1(int $rowIndex): ?ItemLock {
        return parent::getItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb1->getPosition());
    }

    /**
     * Sets the main bank account indicator for bank 1 (field 61).
     */
    public function setMainBankConnectionFlag1(int $rowIndex, ItemLock $flag): void {
        parent::setItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb1->getPosition(), $flag);
    }

    /**
     * Checks if bank 1 is the main bank account.
     */
    public function isMainBankConnection1(int $rowIndex): bool {
        $flag = $this->getMainBankConnectionFlag1($rowIndex);
        return $flag !== null && $flag->isLocked();
    }

    /**
     * Returns the main bank account indicator for bank 2 (field 72).
     */
    public function getMainBankConnectionFlag2(int $rowIndex): ?ItemLock {
        return parent::getItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb2->getPosition());
    }

    /**
     * Sets the main bank account indicator for bank 2 (field 72).
     */
    public function setMainBankConnectionFlag2(int $rowIndex, ItemLock $flag): void {
        parent::setItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb2->getPosition(), $flag);
    }

    /**
     * Checks if bank 2 is the main bank account.
     */
    public function isMainBankConnection2(int $rowIndex): bool {
        $flag = $this->getMainBankConnectionFlag2($rowIndex);
        return $flag !== null && $flag->isLocked();
    }

    /**
     * Returns the main bank account indicator for bank 3 (field 83).
     */
    public function getMainBankConnectionFlag3(int $rowIndex): ?ItemLock {
        return parent::getItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb3->getPosition());
    }

    /**
     * Sets the main bank account indicator for bank 3 (field 83).
     */
    public function setMainBankConnectionFlag3(int $rowIndex, ItemLock $flag): void {
        parent::setItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb3->getPosition(), $flag);
    }

    /**
     * Checks if bank 3 is the main bank account.
     */
    public function isMainBankConnection3(int $rowIndex): bool {
        $flag = $this->getMainBankConnectionFlag3($rowIndex);
        return $flag !== null && $flag->isLocked();
    }

    /**
     * Returns the main bank account indicator for bank 4 (field 94).
     */
    public function getMainBankConnectionFlag4(int $rowIndex): ?ItemLock {
        return parent::getItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb4->getPosition());
    }

    /**
     * Sets the main bank account indicator for bank 4 (field 94).
     */
    public function setMainBankConnectionFlag4(int $rowIndex, ItemLock $flag): void {
        parent::setItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb4->getPosition(), $flag);
    }

    /**
     * Checks if bank 4 is the main bank account.
     */
    public function isMainBankConnection4(int $rowIndex): bool {
        $flag = $this->getMainBankConnectionFlag4($rowIndex);
        return $flag !== null && $flag->isLocked();
    }

    /**
     * Returns the main bank account indicator for bank 5 (field 105).
     */
    public function getMainBankConnectionFlag5(int $rowIndex): ?ItemLock {
        return parent::getItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb5->getPosition());
    }

    /**
     * Sets the main bank account indicator for bank 5 (field 105).
     */
    public function setMainBankConnectionFlag5(int $rowIndex, ItemLock $flag): void {
        parent::setItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb5->getPosition(), $flag);
    }

    /**
     * Checks if bank 5 is the main bank account.
     */
    public function isMainBankConnection5(int $rowIndex): bool {
        $flag = $this->getMainBankConnectionFlag5($rowIndex);
        return $flag !== null && $flag->isLocked();
    }

    /**
     * Returns the main bank account indicator for bank 6 (field 116).
     */
    public function getMainBankConnectionFlag6(int $rowIndex): ?ItemLock {
        return parent::getItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb6->getPosition());
    }

    /**
     * Sets the main bank account indicator for bank 6 (field 116).
     */
    public function setMainBankConnectionFlag6(int $rowIndex, ItemLock $flag): void {
        parent::setItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb6->getPosition(), $flag);
    }

    /**
     * Checks if bank 6 is the main bank account.
     */
    public function isMainBankConnection6(int $rowIndex): bool {
        $flag = $this->getMainBankConnectionFlag6($rowIndex);
        return $flag !== null && $flag->isLocked();
    }

    /**
     * Returns the main bank account indicator for bank 7 (field 127).
     */
    public function getMainBankConnectionFlag7(int $rowIndex): ?ItemLock {
        return parent::getItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb7->getPosition());
    }

    /**
     * Sets the main bank account indicator for bank 7 (field 127).
     */
    public function setMainBankConnectionFlag7(int $rowIndex, ItemLock $flag): void {
        parent::setItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb7->getPosition(), $flag);
    }

    /**
     * Checks if bank 7 is the main bank account.
     */
    public function isMainBankConnection7(int $rowIndex): bool {
        $flag = $this->getMainBankConnectionFlag7($rowIndex);
        return $flag !== null && $flag->isLocked();
    }

    /**
     * Returns the main bank account indicator for bank 8 (field 138).
     */
    public function getMainBankConnectionFlag8(int $rowIndex): ?ItemLock {
        return parent::getItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb8->getPosition());
    }

    /**
     * Sets the main bank account indicator for bank 8 (field 138).
     */
    public function setMainBankConnectionFlag8(int $rowIndex, ItemLock $flag): void {
        parent::setItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb8->getPosition(), $flag);
    }

    /**
     * Checks if bank 8 is the main bank account.
     */
    public function isMainBankConnection8(int $rowIndex): bool {
        $flag = $this->getMainBankConnectionFlag8($rowIndex);
        return $flag !== null && $flag->isLocked();
    }

    /**
     * Returns the main bank account indicator for bank 9 (field 149).
     */
    public function getMainBankConnectionFlag9(int $rowIndex): ?ItemLock {
        return parent::getItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb9->getPosition());
    }

    /**
     * Sets the main bank account indicator for bank 9 (field 149).
     */
    public function setMainBankConnectionFlag9(int $rowIndex, ItemLock $flag): void {
        parent::setItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb9->getPosition(), $flag);
    }

    /**
     * Checks if bank 9 is the main bank account.
     */
    public function isMainBankConnection9(int $rowIndex): bool {
        $flag = $this->getMainBankConnectionFlag9($rowIndex);
        return $flag !== null && $flag->isLocked();
    }

    /**
     * Returns the main bank account indicator for bank 10 (field 160).
     */
    public function getMainBankConnectionFlag10(int $rowIndex): ?ItemLock {
        return parent::getItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb10->getPosition());
    }

    /**
     * Sets the main bank account indicator for bank 10 (field 160).
     */
    public function setMainBankConnectionFlag10(int $rowIndex, ItemLock $flag): void {
        parent::setItemLock($rowIndex, VariousAddressesHeaderField::KennzHauptbankverb10->getPosition(), $flag);
    }

    /**
     * Checks if bank 10 is the main bank account.
     */
    public function isMainBankConnection10(int $rowIndex): bool {
        $flag = $this->getMainBankConnectionFlag10($rowIndex);
        return $flag !== null && $flag->isLocked();
    }

    // ==== CONVENIENCE METHODS ====

    /**
     * Checks if a various address belongs to a natural person.
     */
    public function isNaturalPerson(int $rowIndex): bool {
        $type = $this->getAddresseeTypeValue($rowIndex);
        return $type !== null && $type->isNaturalPerson();
    }

    /**
     * Checks if a various address belongs to a company.
     */
    public function isCompany(int $rowIndex): bool {
        $type = $this->getAddresseeTypeValue($rowIndex);
        return $type !== null && $type->isCompany();
    }

    /**
     * Determines the main bank account for a various address.
     * Returns the bank number (1-10) or null if no main bank is set.
     */
    public function getMainBankNumber(int $rowIndex): ?int {
        if ($this->isMainBankConnection1($rowIndex)) return 1;
        if ($this->isMainBankConnection2($rowIndex)) return 2;
        if ($this->isMainBankConnection3($rowIndex)) return 3;
        if ($this->isMainBankConnection4($rowIndex)) return 4;
        if ($this->isMainBankConnection5($rowIndex)) return 5;
        if ($this->isMainBankConnection6($rowIndex)) return 6;
        if ($this->isMainBankConnection7($rowIndex)) return 7;
        if ($this->isMainBankConnection8($rowIndex)) return 8;
        if ($this->isMainBankConnection9($rowIndex)) return 9;
        if ($this->isMainBankConnection10($rowIndex)) return 10;
        return null;
    }

    /**
     * Returns all various addresses belonging to natural persons.
     *
     * @return int[] Array der Row-Indices
     */
    public function getNaturalPersons(): array {
        $result = [];
        $count = count($this->getRows());
        for ($i = 0; $i < $count; $i++) {
            if ($this->isNaturalPerson($i)) {
                $result[] = $i;
            }
        }
        return $result;
    }

    /**
     * Returns all various addresses belonging to companies.
     *
     * @return int[] Array der Row-Indices
     */
    public function getCompanies(): array {
        $result = [];
        $count = count($this->getRows());
        for ($i = 0; $i < $count; $i++) {
            if ($this->isCompany($i)) {
                $result[] = $i;
            }
        }
        return $result;
    }
}
