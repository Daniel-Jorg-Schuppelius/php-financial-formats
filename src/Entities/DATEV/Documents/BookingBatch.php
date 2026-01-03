<?php
/*
 * Created on   : Mon Dec 15 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : BookingBatch.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\DATEV\Documents;

use CommonToolkit\Entities\CSV\ColumnWidthConfig;
use CommonToolkit\Entities\CSV\HeaderLine;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV\Document;
use CommonToolkit\FinancialFormats\Entities\DATEV\{DocumentInfo, MetaHeaderLine};
use CommonToolkit\Enums\Common\CSV\TruncationStrategy;
use CommonToolkit\FinancialFormats\Enums\DATEV\{DiscountLock, DiscountType, InterestLock, PostingLock};
use CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields\Format\Category;
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\V700\BookingBatchHeaderField;
use CommonToolkit\Enums\{CreditDebit, CurrencyCode, CountryCode};
use RuntimeException;

/**
 * DATEV-BookingBatch-Dokument.
 * Special document class for BookingBatch format (Category 21).
 * 
 * Die Spaltenbreiten werden automatisch basierend auf den DATEV-Spezifikationen
 * aus BookingBatchHeaderField::getMaxLength() angewendet.
 */
final class BookingBatch extends Document {
    public function __construct(?MetaHeaderLine $metaHeader, ?HeaderLine $header, array $rows = []) {
        parent::__construct($metaHeader, $header, $rows);
    }

    /**
     * Erstellt eine ColumnWidthConfig basierend auf den DATEV-Spezifikationen.
     * Maximum field lengths are derived from BookingBatchHeaderField::getMaxLength().
     * 
     * @param TruncationStrategy $strategy Truncation strategy (Default: TRUNCATE for DATEV conformity)
     * @return ColumnWidthConfig
     */
    public static function createDatevColumnWidthConfig(TruncationStrategy $strategy = TruncationStrategy::TRUNCATE): ColumnWidthConfig {
        $config = new ColumnWidthConfig(null, $strategy);

        foreach (BookingBatchHeaderField::ordered() as $index => $field) {
            $maxLength = $field->getMaxLength();
            if ($maxLength !== null) {
                $config->setColumnWidth($index, $maxLength);
            }
        }

        return $config;
    }

    /**
     * Returns the DATEV format type.
     */
    public function getFormatType(): string {
        return Category::Buchungsstapel->nameValue();
    }

    /**
     * Returns the format information.
     */
    public function getDocumentInfo(): DocumentInfo {
        return new DocumentInfo(Category::Buchungsstapel, 700);
    }

    /**
     * Validiert, dass es sich um ein BookingBatch-Format handelt.
     */
    public function validate(): void {
        parent::validate();

        // Zusätzliche Validierung für BookingBatch
        if ($this->getMetaHeader() !== null) {
            $metaFields = $this->getMetaHeader()->getFields();
            if (count($metaFields) > 2 && $metaFields[2]->getValue() !== '21') {
                throw new RuntimeException('Document ist kein BookingBatch-Format');
            }
        }
    }


    // ==== BOOKINGBATCH-SPEZIFISCHE ENUM GETTER/SETTER ====

    /**
     * Returns the debit/credit indicator of a booking.
     */
    public function getSollHabenKennzeichen(int $rowIndex): ?CreditDebit {
        return $this->getCreditDebit($rowIndex, BookingBatchHeaderField::SollHabenKennzeichen->getPosition());
    }

    /**
     * Setzt das Soll/Haben-Kennzeichen einer Buchung.
     */
    public function setSollHabenKennzeichen(int $rowIndex, CreditDebit $creditDebit): void {
        $this->setCreditDebit($rowIndex, BookingBatchHeaderField::SollHabenKennzeichen->getPosition(), $creditDebit);
    }

    /**
     * Returns the base currency of a booking.
     */
    public function getWKZBasisUmsatz(int $rowIndex): ?CurrencyCode {
        return $this->getCurrencyCode($rowIndex, BookingBatchHeaderField::WKZBasisUmsatz->getPosition());
    }

    /**
     * Sets the base currency of a booking.
     */
    public function setWKZBasisUmsatz(int $rowIndex, CurrencyCode $currencyCode): void {
        $this->setCurrencyCode($rowIndex, BookingBatchHeaderField::WKZBasisUmsatz->getPosition(), $currencyCode);
    }

    /**
     * Returns the transaction currency of a booking.
     */
    public function getWKZUmsatz(int $rowIndex): ?CurrencyCode {
        return $this->getCurrencyCode($rowIndex, BookingBatchHeaderField::WKZUmsatz->getPosition());
    }

    /**
     * Sets the transaction currency of a booking.
     */
    public function setWKZUmsatz(int $rowIndex, CurrencyCode $currencyCode): void {
        $this->setCurrencyCode($rowIndex, BookingBatchHeaderField::WKZUmsatz->getPosition(), $currencyCode);
    }

    /**
     * Returns the EU country of a booking.
     */
    public function getEULandUStID(int $rowIndex): ?CountryCode {
        return $this->getCountryCode($rowIndex, BookingBatchHeaderField::EULandUStID->getPosition());
    }

    /**
     * Setzt das EU-Land einer Buchung.
     */
    public function setEULandUStID(int $rowIndex, CountryCode $countryCode): void {
        $this->setCountryCode($rowIndex, BookingBatchHeaderField::EULandUStID->getPosition(), $countryCode);
    }

    /**
     * Returns the country of a booking.
     */
    public function getLand(int $rowIndex): ?CountryCode {
        return $this->getCountryCode($rowIndex, BookingBatchHeaderField::Land->getPosition());
    }

    /**
     * Setzt das Land einer Buchung.
     */
    public function setLand(int $rowIndex, CountryCode $countryCode): void {
        $this->setCountryCode($rowIndex, BookingBatchHeaderField::Land->getPosition(), $countryCode);
    }


    // ==== CONVENIENCE METHODS ====

    /**
     * Checks if a booking has an EU country.
     */
    public function isEUBooking(int $rowIndex): bool {
        $country = $this->getEULandUStID($rowIndex) ?? $this->getLand($rowIndex);
        return $country?->isEU() ?? false;
    }

    /**
     * Checks if a booking uses Euro as currency.
     */
    public function isEuroCurrency(int $rowIndex): bool {
        $currency = $this->getWKZUmsatz($rowIndex) ?? $this->getWKZBasisUmsatz($rowIndex);
        return $currency === CurrencyCode::Euro;
    }

    /**
     * Returns the interest lock of a booking.
     */
    public function getZinssperre(int $rowIndex): ?InterestLock {
        return $this->getInterestLock($rowIndex, BookingBatchHeaderField::Zinssperre->getPosition());
    }

    /**
     * Setzt die Zinssperre einer Buchung.
     */
    public function setZinssperre(int $rowIndex, InterestLock $interestLock): void {
        $this->setInterestLock($rowIndex, BookingBatchHeaderField::Zinssperre->getPosition(), $interestLock);
    }

    /**
     * Returns the discount lock of a booking.
     */
    public function getSkontosperre(int $rowIndex): ?DiscountLock {
        return $this->getDiscountLock($rowIndex, BookingBatchHeaderField::Skontosperre->getPosition());
    }

    /**
     * Setzt die Skontosperre einer Buchung.
     */
    public function setSkontosperre(int $rowIndex, DiscountLock $discountLock): void {
        $this->setDiscountLock($rowIndex, BookingBatchHeaderField::Skontosperre->getPosition(), $discountLock);
    }

    /**
     * Returns the discount type of a booking.
     */
    public function getSkontoTyp(int $rowIndex): ?DiscountType {
        return $this->getDiscountType($rowIndex, BookingBatchHeaderField::SkontoTyp->getPosition());
    }

    /**
     * Setzt den Skontotyp einer Buchung.
     */
    public function setSkontoTyp(int $rowIndex, DiscountType $discountType): void {
        $this->setDiscountType($rowIndex, BookingBatchHeaderField::SkontoTyp->getPosition(), $discountType);
    }

    /**
     * Returns the lock status of a booking.
     */
    public function getFestschreibung(int $rowIndex): ?PostingLock {
        return $this->getPostingLock($rowIndex, BookingBatchHeaderField::Festschreibung->getPosition());
    }

    /**
     * Setzt die Festschreibung einer Buchung.
     */
    public function setFestschreibung(int $rowIndex, PostingLock $postingLock): void {
        $this->setPostingLock($rowIndex, BookingBatchHeaderField::Festschreibung->getPosition(), $postingLock);
    }

    /**
     * Checks if a booking is locked.
     */
    public function isLocked(int $rowIndex): bool {
        return $this->getFestschreibung($rowIndex)?->isLocked() ?? false;
    }

    /**
     * Returns all bookings with a specific debit/credit indicator.
     */
    public function getRowsByCreditDebit(CreditDebit $creditDebit): array {
        $result = [];

        foreach ($this->rows as $index => $row) {
            if ($this->getSollHabenKennzeichen($index) === $creditDebit) {
                $result[] = $index;
            }
        }

        return $result;
    }
}
