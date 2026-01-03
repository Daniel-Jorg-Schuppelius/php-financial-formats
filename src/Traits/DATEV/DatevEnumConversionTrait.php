<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DatevEnumConversionTrait.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Traits\DATEV;

use CommonToolkit\FinancialFormats\Enums\DATEV\{
    AddresseeType,
    AddressType,
    CurrencyControl,
    DirectDebitIndicator,
    DiscountLock,
    DiscountType,
    DunningIndicator,
    DunningSubject,
    EndType,
    InterestCalculationIndicator,
    InterestLock,
    ItemLock,
    Language,
    OutputTarget,
    PaymentCarrierIndicator,
    PaymentMethod,
    PostingLock,
    ReceiptFieldHandling,
    StatementIndicator,
    TimeIntervalType,
    WeekdayOrdinal
};
use CommonToolkit\Enums\{CreditDebit, CurrencyCode, CountryCode, LanguageCode};
use InvalidArgumentException;

/**
 * Trait for DATEV enum conversions.
 * 
 * Stellt Methoden zum Lesen und Schreiben von DATEV-spezifischen Enum-Werten
 * in CSV fields.
 * 
 * Erfordert, dass die verwendende Klasse die Methoden getFieldValue() und setFieldValue() implementiert.
 */
trait DatevEnumConversionTrait {
    /**
     * Returns the raw field value.
     */
    abstract protected function getFieldValue(int $rowIndex, int $fieldIndex): ?string;

    /**
     * Sets the raw field value.
     */
    abstract protected function setFieldValue(int $rowIndex, int $fieldIndex, string $value): void;

    // ==================== GRUNDLEGENDE ENUMS ====================

    /**
     * Returns a field value as CreditDebit enum.
     */
    protected function getCreditDebit(int $rowIndex, int $fieldIndex): ?CreditDebit {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if (!$value) {
            return null;
        }

        $cleanValue = trim($value, '"');
        return match ($cleanValue) {
            'S' => CreditDebit::CREDIT,
            'H' => CreditDebit::DEBIT,
            default => null
        };
    }

    /**
     * Sets a field value from a CreditDebit enum.
     */
    protected function setCreditDebit(int $rowIndex, int $fieldIndex, CreditDebit $creditDebit): void {
        $datevValue = match ($creditDebit) {
            CreditDebit::CREDIT => '"S"',
            CreditDebit::DEBIT => '"H"'
        };
        $this->setFieldValue($rowIndex, $fieldIndex, $datevValue);
    }

    /**
     * Returns a field value as CurrencyCode enum.
     */
    protected function getCurrencyCode(int $rowIndex, int $fieldIndex): ?CurrencyCode {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if (!$value) {
            return null;
        }

        $cleanValue = trim($value, '"');
        try {
            return CurrencyCode::fromCode($cleanValue);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Sets a field value from a CurrencyCode enum.
     */
    protected function setCurrencyCode(int $rowIndex, int $fieldIndex, CurrencyCode $currencyCode): void {
        $datevValue = '"' . $currencyCode->value . '"';
        $this->setFieldValue($rowIndex, $fieldIndex, $datevValue);
    }

    /**
     * Returns a field value as CountryCode enum.
     */
    protected function getCountryCode(int $rowIndex, int $fieldIndex): ?CountryCode {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if (!$value) {
            return null;
        }

        $cleanValue = trim($value, '"');
        try {
            return CountryCode::fromStringValue($cleanValue);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Sets a field value from a CountryCode enum.
     */
    protected function setCountryCode(int $rowIndex, int $fieldIndex, CountryCode $countryCode): void {
        $datevValue = '"' . $countryCode->value . '"';
        $this->setFieldValue($rowIndex, $fieldIndex, $datevValue);
    }

    // ==================== SPERR-ENUMS ====================

    /**
     * Returns a field value as PostingLock enum.
     */
    protected function getPostingLock(int $rowIndex, int $fieldIndex): ?PostingLock {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        try {
            return PostingLock::fromStringValue(trim($value, '"'));
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Sets a field value from a PostingLock enum.
     */
    protected function setPostingLock(int $rowIndex, int $fieldIndex, PostingLock $postingLock): void {
        $this->setFieldValue($rowIndex, $fieldIndex, (string) $postingLock->value);
    }

    /**
     * Returns a field value as InterestLock enum.
     */
    protected function getInterestLock(int $rowIndex, int $fieldIndex): ?InterestLock {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        $cleanValue = trim($value, '"');
        if ($cleanValue === '') {
            return null;
        }

        return InterestLock::fromInt((int) $cleanValue);
    }

    /**
     * Sets a field value from an InterestLock enum.
     */
    protected function setInterestLock(int $rowIndex, int $fieldIndex, InterestLock $interestLock): void {
        $this->setFieldValue($rowIndex, $fieldIndex, (string) $interestLock->value);
    }

    /**
     * Returns a field value as DiscountLock enum.
     */
    protected function getDiscountLock(int $rowIndex, int $fieldIndex): ?DiscountLock {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        $cleanValue = trim($value, '"');
        if ($cleanValue === '') {
            return null;
        }

        return DiscountLock::fromInt((int) $cleanValue);
    }

    /**
     * Sets a field value from a DiscountLock enum.
     */
    protected function setDiscountLock(int $rowIndex, int $fieldIndex, DiscountLock $discountLock): void {
        $this->setFieldValue($rowIndex, $fieldIndex, (string) $discountLock->value);
    }

    /**
     * Returns a field value as ItemLock enum (0/1 lock).
     */
    protected function getItemLock(int $rowIndex, int $fieldIndex): ?ItemLock {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        $cleanValue = trim($value, '"');
        if ($cleanValue === '') {
            return null;
        }

        return ItemLock::fromInt((int) $cleanValue);
    }

    /**
     * Sets a field value from an ItemLock enum.
     */
    protected function setItemLock(int $rowIndex, int $fieldIndex, ItemLock $itemLock): void {
        $this->setFieldValue($rowIndex, $fieldIndex, (string) $itemLock->value);
    }

    // ==================== ZAHLUNGS-ENUMS ====================

    /**
     * Returns a field value as DiscountType enum.
     */
    protected function getDiscountType(int $rowIndex, int $fieldIndex): ?DiscountType {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        $cleanValue = trim($value, '"');
        if ($cleanValue === '') {
            return null;
        }

        try {
            return DiscountType::fromInt((int) $cleanValue);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Sets a field value from a DiscountType enum.
     */
    protected function setDiscountType(int $rowIndex, int $fieldIndex, DiscountType $discountType): void {
        $this->setFieldValue($rowIndex, $fieldIndex, (string) $discountType->value);
    }

    /**
     * Returns a field value as PaymentMethod enum.
     */
    protected function getPaymentMethod(int $rowIndex, int $fieldIndex): ?PaymentMethod {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        $cleanValue = trim($value, '"');
        if ($cleanValue === '') {
            return null;
        }

        try {
            return PaymentMethod::fromInt((int) $cleanValue);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * Sets a field value from a PaymentMethod enum.
     */
    protected function setPaymentMethod(int $rowIndex, int $fieldIndex, PaymentMethod $paymentMethod): void {
        $this->setFieldValue($rowIndex, $fieldIndex, (string) $paymentMethod->value);
    }

    // ==================== DEBITOREN/KREDITOREN-SPEZIFISCHE ENUMS ====================

    /**
     * Returns a field value as AddresseeType enum.
     */
    protected function getAddresseeType(int $rowIndex, int $fieldIndex): ?AddresseeType {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        return AddresseeType::tryFromString($value);
    }

    /**
     * Sets a field value from an AddresseeType enum.
     */
    protected function setAddresseeType(int $rowIndex, int $fieldIndex, AddresseeType $addresseeType): void {
        $this->setFieldValue($rowIndex, $fieldIndex, '"' . $addresseeType->value . '"');
    }

    /**
     * Returns a field value as Language enum.
     */
    protected function getLanguage(int $rowIndex, int $fieldIndex): ?Language {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        return Language::tryFromString($value);
    }

    /**
     * Sets a field value from a Language enum.
     */
    protected function setLanguage(int $rowIndex, int $fieldIndex, Language $language): void {
        $this->setFieldValue($rowIndex, $fieldIndex, (string) $language->value);
    }

    /**
     * Returns a field value as OutputTarget enum.
     */
    protected function getOutputTarget(int $rowIndex, int $fieldIndex): ?OutputTarget {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        return OutputTarget::tryFromString($value);
    }

    /**
     * Sets a field value from an OutputTarget enum.
     */
    protected function setOutputTarget(int $rowIndex, int $fieldIndex, OutputTarget $outputTarget): void {
        $this->setFieldValue($rowIndex, $fieldIndex, (string) $outputTarget->value);
    }

    /**
     * Returns a field value as AddressType enum.
     */
    protected function getAddressType(int $rowIndex, int $fieldIndex): ?AddressType {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        return AddressType::tryFromString($value);
    }

    /**
     * Sets a field value from an AddressType enum.
     */
    protected function setAddressType(int $rowIndex, int $fieldIndex, AddressType $addressType): void {
        $this->setFieldValue($rowIndex, $fieldIndex, '"' . $addressType->value . '"');
    }

    /**
     * Returns a field value as DirectDebitIndicator enum.
     */
    protected function getDirectDebitIndicator(int $rowIndex, int $fieldIndex): ?DirectDebitIndicator {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        return DirectDebitIndicator::tryFromString($value);
    }

    /**
     * Sets a field value from a DirectDebitIndicator enum.
     */
    protected function setDirectDebitIndicator(int $rowIndex, int $fieldIndex, DirectDebitIndicator $indicator): void {
        $this->setFieldValue($rowIndex, $fieldIndex, '"' . $indicator->value . '"');
    }

    /**
     * Returns a field value as PaymentCarrierIndicator enum.
     */
    protected function getPaymentCarrierIndicator(int $rowIndex, int $fieldIndex): ?PaymentCarrierIndicator {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        return PaymentCarrierIndicator::tryFromString($value);
    }

    /**
     * Sets a field value from a PaymentCarrierIndicator enum.
     */
    protected function setPaymentCarrierIndicator(int $rowIndex, int $fieldIndex, PaymentCarrierIndicator $indicator): void {
        $this->setFieldValue($rowIndex, $fieldIndex, '"' . $indicator->value . '"');
    }

    /**
     * Returns a field value as DunningIndicator enum.
     */
    protected function getDunningIndicator(int $rowIndex, int $fieldIndex): ?DunningIndicator {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        return DunningIndicator::tryFromString($value);
    }

    /**
     * Sets a field value from a DunningIndicator enum.
     */
    protected function setDunningIndicator(int $rowIndex, int $fieldIndex, DunningIndicator $indicator): void {
        $this->setFieldValue($rowIndex, $fieldIndex, (string) $indicator->value);
    }

    /**
     * Returns a field value as StatementIndicator enum.
     */
    protected function getStatementIndicator(int $rowIndex, int $fieldIndex): ?StatementIndicator {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        return StatementIndicator::tryFromString($value);
    }

    /**
     * Sets a field value from a StatementIndicator enum.
     */
    protected function setStatementIndicator(int $rowIndex, int $fieldIndex, StatementIndicator $indicator): void {
        $this->setFieldValue($rowIndex, $fieldIndex, (string) $indicator->value);
    }

    /**
     * Returns a field value as InterestCalculationIndicator enum.
     */
    protected function getInterestCalculationIndicator(int $rowIndex, int $fieldIndex): ?InterestCalculationIndicator {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        return InterestCalculationIndicator::tryFromString($value);
    }

    /**
     * Sets a field value from an InterestCalculationIndicator enum.
     */
    protected function setInterestCalculationIndicator(int $rowIndex, int $fieldIndex, InterestCalculationIndicator $indicator): void {
        $this->setFieldValue($rowIndex, $fieldIndex, (string) $indicator->value);
    }

    /**
     * Returns a field value as CurrencyControl enum.
     */
    protected function getCurrencyControl(int $rowIndex, int $fieldIndex): ?CurrencyControl {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        return CurrencyControl::tryFromString($value);
    }

    /**
     * Sets a field value from a CurrencyControl enum.
     */
    protected function setCurrencyControl(int $rowIndex, int $fieldIndex, CurrencyControl $currencyControl): void {
        $this->setFieldValue($rowIndex, $fieldIndex, (string) $currencyControl->value);
    }

    /**
     * Returns a field value as LanguageCode enum (string-based language ID).
     */
    protected function getLanguageCode(int $rowIndex, int $fieldIndex): ?LanguageCode {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        return LanguageCode::tryFromString($value);
    }

    /**
     * Sets a field value from a LanguageCode enum.
     */
    protected function setLanguageCode(int $rowIndex, int $fieldIndex, LanguageCode $languageCode): void {
        $this->setFieldValue($rowIndex, $fieldIndex, $languageCode->toCsvValue());
    }

    // ==================== WIEDERKEHRENDE BUCHUNGEN-SPEZIFISCHE ENUMS ====================

    /**
     * Returns a field value as ReceiptFieldHandling enum.
     */
    protected function getReceiptFieldHandling(int $rowIndex, int $fieldIndex): ?ReceiptFieldHandling {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        return ReceiptFieldHandling::tryFromString($value);
    }

    /**
     * Sets a field value from a ReceiptFieldHandling enum.
     */
    protected function setReceiptFieldHandling(int $rowIndex, int $fieldIndex, ReceiptFieldHandling $handling): void {
        $this->setFieldValue($rowIndex, $fieldIndex, (string) $handling->value);
    }

    /**
     * Returns a field value as DunningSubject enum.
     */
    protected function getDunningSubject(int $rowIndex, int $fieldIndex): ?DunningSubject {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        return DunningSubject::tryFromString($value);
    }

    /**
     * Sets a field value from a DunningSubject enum.
     */
    protected function setDunningSubject(int $rowIndex, int $fieldIndex, DunningSubject $subject): void {
        $value = $subject->value === 0 ? '' : (string) $subject->value;
        $this->setFieldValue($rowIndex, $fieldIndex, $value);
    }

    /**
     * Returns a field value as TimeIntervalType enum.
     */
    protected function getTimeIntervalType(int $rowIndex, int $fieldIndex): ?TimeIntervalType {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        return TimeIntervalType::tryFromString($value);
    }

    /**
     * Sets a field value from a TimeIntervalType enum.
     */
    protected function setTimeIntervalType(int $rowIndex, int $fieldIndex, TimeIntervalType $intervalType): void {
        $this->setFieldValue($rowIndex, $fieldIndex, $intervalType->toCsvValue());
    }

    /**
     * Returns a field value as weekday bitmask (Weekday::toBitmask()).
     */
    protected function getWeekdayMask(int $rowIndex, int $fieldIndex): ?int {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        $cleanValue = trim($value, '"');
        if ($cleanValue === '' || !is_numeric($cleanValue)) {
            return null;
        }

        return (int) $cleanValue;
    }

    /**
     * Sets a field value from a weekday bitmask (Weekday::createMask()).
     */
    protected function setWeekdayMask(int $rowIndex, int $fieldIndex, int $mask): void {
        $this->setFieldValue($rowIndex, $fieldIndex, (string) $mask);
    }

    /**
     * Returns a field value as WeekdayOrdinal enum.
     */
    protected function getWeekdayOrdinal(int $rowIndex, int $fieldIndex): ?WeekdayOrdinal {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        return WeekdayOrdinal::tryFromString($value);
    }

    /**
     * Sets a field value from a WeekdayOrdinal enum.
     */
    protected function setWeekdayOrdinal(int $rowIndex, int $fieldIndex, WeekdayOrdinal $ordinal): void {
        $this->setFieldValue($rowIndex, $fieldIndex, (string) $ordinal->value);
    }

    /**
     * Returns a field value as EndType enum.
     */
    protected function getEndType(int $rowIndex, int $fieldIndex): ?EndType {
        $value = $this->getFieldValue($rowIndex, $fieldIndex);
        if ($value === null) {
            return null;
        }

        return EndType::tryFromString($value);
    }

    /**
     * Sets a field value from an EndType enum.
     */
    protected function setEndType(int $rowIndex, int $fieldIndex, EndType $endType): void {
        $this->setFieldValue($rowIndex, $fieldIndex, (string) $endType->value);
    }
}
