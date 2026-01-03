<?php
/*
 * Created on   : Wed Nov 05 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Document.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV;

use CommonToolkit\Entities\CSV\ColumnWidthConfig;
use CommonToolkit\Entities\CSV\Document as CSVDocument;
use CommonToolkit\Entities\CSV\HeaderLine;
use CommonToolkit\Enums\{CreditDebit, CurrencyCode, CountryCode};
use CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV\HeaderLineAbstract;
use CommonToolkit\FinancialFormats\Contracts\Interfaces\DATEV\FieldHeaderInterface;
use CommonToolkit\FinancialFormats\Entities\DATEV\{DataLine, MetaHeaderLine};
use CommonToolkit\FinancialFormats\Enums\DATEV\{
    AddresseeType,
    AddressType,
    CurrencyControl,
    DiscountLock,
    DiscountType,
    InterestLock,
    ItemLock,
    Language,
    OutputTarget,
    PostingLock
};
use CommonToolkit\Enums\LanguageCode;
use CommonToolkit\FinancialFormats\Generators\DATEV\DatevDocumentGenerator;
use InvalidArgumentException;
use RuntimeException;

/**
 * Abstract base class for DATEV documents.
 * 
 * Extends the CSV Document class with DATEV-specific functionality:
 * - MetaHeader support
 * - DATEV-specific validation
 * - Enum-based field access via FieldHeaderInterface
 * - DATEV enum conversion methods for typed field access
 * 
 * Uses the CSV base class methods for all field operations.
 * 
 * @package CommonToolkit\Contracts\Abstracts\DATEV
 */
abstract class Document extends CSVDocument {
    public const DEFAULT_DELIMITER = ';';

    private ?MetaHeaderLine $metaHeader = null;

    /** @param DataLine[] $rows */
    public function __construct(?MetaHeaderLine $metaHeader, ?HeaderLine $header, array $rows = [], ?ColumnWidthConfig $columnWidthConfig = null, string $encoding = CSVDocument::DEFAULT_ENCODING) {
        // Falls keine ColumnWidthConfig übergeben wurde, erstelle eine basierend auf DATEV-Spezifikation
        $columnWidthConfig ??= static::createDatevColumnWidthConfig();

        parent::__construct($header, $rows, ';', '"', $columnWidthConfig, $encoding);
        $this->metaHeader = $metaHeader;
    }

    /**
     * Creates a ColumnWidthConfig based on DATEV specifications.
     * Must be overridden by derived classes to define specific field widths.
     */
    public static function createDatevColumnWidthConfig(): ?ColumnWidthConfig {
        return null;
    }

    public function getMetaHeader(): ?MetaHeaderLine {
        return $this->metaHeader;
    }

    public function validate(): void {
        if (!$this->metaHeader) {
            throw new RuntimeException('DATEV-Metadatenheader fehlt.');
        }
        if (!$this->header) {
            throw new RuntimeException('DATEV field header is missing.');
        }

        $metaValues = array_map(fn($f) => trim($f->getValue(), "\"'"), $this->metaHeader->getFields());
        if ($metaValues[0] !== 'EXTF') {
            throw new RuntimeException('Invalid DATEV metadata header - "EXTF" expected.');
        }
    }

    public function toAssoc(): array {
        $rows = parent::toAssoc();

        return [
            'meta' => [
                'format' => 'DATEV',
                'formatType' => $this->getFormatType(),
                'metaHeader' => $this->metaHeader?->toAssoc(),
                'columns' => $this->header?->countFields() ?? 0,
                'rows' => count($rows),
            ],
            'data' => $rows,
        ];
    }

    /**
     * Returns the DATEV format type.
     */
    abstract public function getFormatType(): string;

    /**
     * Converts the DATEV document to a CSV string including the MetaHeader.
     */
    public function toString(?string $delimiter = null, ?string $enclosure = null, ?int $enclosureRepeat = null, ?string $targetEncoding = null): string {
        return (new DatevDocumentGenerator())->generate($this, $delimiter ?? $this->delimiter, $enclosure ?? $this->enclosure, $enclosureRepeat, $targetEncoding ?? $this->encoding);
    }

    public function __toString(): string {
        return $this->toString();
    }

    // ==== Enum-based Field Access ====

    /**
     * Retrieves a field value using a FieldHeaderInterface enum.
     * 
     * @param int $rowIndex Row index (0-based)
     * @param FieldHeaderInterface $field The field enum
     * @return string|null The trimmed field value or null
     */
    public function getField(int $rowIndex, FieldHeaderInterface $field): ?string {
        $row = $this->getRow($rowIndex);
        if ($row === null || !$this->header instanceof HeaderLineAbstract) {
            return null;
        }

        return $this->header->getFieldValue($row, $field);
    }

    /**
     * Checks if a field has a non-empty value.
     */
    public function hasField(int $rowIndex, FieldHeaderInterface $field): bool {
        $value = $this->getField($rowIndex, $field);
        return $value !== null && $value !== '';
    }

    /**
     * Sets a field value using a FieldHeaderInterface enum.
     * Uses the enum's isQuotedValue() to determine if the field should be quoted.
     * Accepts mixed values and converts them to string.
     * 
     * @param int $rowIndex Row index (0-based)
     * @param FieldHeaderInterface $field The field enum
     * @param mixed $value The value to set (will be converted to string)
     */
    public function setField(int $rowIndex, FieldHeaderInterface $field, mixed $value): void {
        if (!$this->header instanceof HeaderLineAbstract) {
            return;
        }

        $index = $this->header->getFieldIndex($field);
        if ($index >= 0) {
            $this->setFieldValueWithQuoting($rowIndex, $index, $value, $field->isQuotedValue());
        }
    }

    // ==================== ENUM CONVERSION METHODS ====================

    /**
     * Resolves a field parameter to a numeric index.
     * Accepts either an integer index or a FieldHeaderInterface enum.
     */
    private function resolveFieldIndex(int|FieldHeaderInterface $field): int {
        return $field instanceof FieldHeaderInterface ? $field->getPosition() : $field;
    }

    // ==================== BASIC ENUMS ====================

    /**
     * Returns a field value as CreditDebit enum.
     */
    protected function getCreditDebit(int $rowIndex, int|FieldHeaderInterface $field): ?CreditDebit {
        $value = $this->getFieldValue($rowIndex, $this->resolveFieldIndex($field));
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
    protected function setCreditDebit(int $rowIndex, int|FieldHeaderInterface $field, CreditDebit $creditDebit): void {
        $datevValue = match ($creditDebit) {
            CreditDebit::CREDIT => 'S',
            CreditDebit::DEBIT => 'H'
        };
        $this->setFieldValueWithQuoting($rowIndex, $this->resolveFieldIndex($field), $datevValue, true);
    }

    /**
     * Returns a field value as CurrencyCode enum.
     */
    protected function getCurrencyCode(int $rowIndex, int|FieldHeaderInterface $field): ?CurrencyCode {
        $value = $this->getFieldValue($rowIndex, $this->resolveFieldIndex($field));
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
    protected function setCurrencyCode(int $rowIndex, int|FieldHeaderInterface $field, CurrencyCode $currencyCode): void {
        $this->setFieldValueWithQuoting($rowIndex, $this->resolveFieldIndex($field), $currencyCode->value, true);
    }

    /**
     * Returns a field value as CountryCode enum.
     */
    protected function getCountryCode(int $rowIndex, int|FieldHeaderInterface $field): ?CountryCode {
        $value = $this->getFieldValue($rowIndex, $this->resolveFieldIndex($field));
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
    protected function setCountryCode(int $rowIndex, int|FieldHeaderInterface $field, CountryCode $countryCode): void {
        $this->setFieldValueWithQuoting($rowIndex, $this->resolveFieldIndex($field), $countryCode->value, true);
    }

    // ==================== LOCK ENUMS ====================

    /**
     * Returns a field value as PostingLock enum.
     */
    protected function getPostingLock(int $rowIndex, int|FieldHeaderInterface $field): ?PostingLock {
        $value = $this->getFieldValue($rowIndex, $this->resolveFieldIndex($field));
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
    protected function setPostingLock(int $rowIndex, int|FieldHeaderInterface $field, PostingLock $postingLock): void {
        $this->setFieldValueWithQuoting($rowIndex, $this->resolveFieldIndex($field), (string) $postingLock->value, false);
    }

    /**
     * Returns a field value as InterestLock enum.
     */
    protected function getInterestLock(int $rowIndex, int|FieldHeaderInterface $field): ?InterestLock {
        $value = $this->getFieldValue($rowIndex, $this->resolveFieldIndex($field));
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
    protected function setInterestLock(int $rowIndex, int|FieldHeaderInterface $field, InterestLock $interestLock): void {
        $this->setFieldValueWithQuoting($rowIndex, $this->resolveFieldIndex($field), (string) $interestLock->value, false);
    }

    /**
     * Returns a field value as DiscountLock enum.
     */
    protected function getDiscountLock(int $rowIndex, int|FieldHeaderInterface $field): ?DiscountLock {
        $value = $this->getFieldValue($rowIndex, $this->resolveFieldIndex($field));
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
    protected function setDiscountLock(int $rowIndex, int|FieldHeaderInterface $field, DiscountLock $discountLock): void {
        $this->setFieldValueWithQuoting($rowIndex, $this->resolveFieldIndex($field), (string) $discountLock->value, false);
    }

    /**
     * Returns a field value as ItemLock enum (0/1 lock).
     */
    protected function getItemLock(int $rowIndex, int|FieldHeaderInterface $field): ?ItemLock {
        $value = $this->getFieldValue($rowIndex, $this->resolveFieldIndex($field));
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
    protected function setItemLock(int $rowIndex, int|FieldHeaderInterface $field, ItemLock $itemLock): void {
        $this->setFieldValueWithQuoting($rowIndex, $this->resolveFieldIndex($field), (string) $itemLock->value, false);
    }

    // ==================== PAYMENT ENUMS ====================

    /**
     * Returns a field value as DiscountType enum.
     */
    protected function getDiscountType(int $rowIndex, int|FieldHeaderInterface $field): ?DiscountType {
        $value = $this->getFieldValue($rowIndex, $this->resolveFieldIndex($field));
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
    protected function setDiscountType(int $rowIndex, int|FieldHeaderInterface $field, DiscountType $discountType): void {
        $this->setFieldValueWithQuoting($rowIndex, $this->resolveFieldIndex($field), (string) $discountType->value, false);
    }

    /**
     * Returns a field value as AddresseeType enum.
     */
    protected function getAddresseeType(int $rowIndex, int|FieldHeaderInterface $field): ?AddresseeType {
        $value = $this->getFieldValue($rowIndex, $this->resolveFieldIndex($field));
        if ($value === null) {
            return null;
        }

        return AddresseeType::tryFromString($value);
    }

    /**
     * Sets a field value from an AddresseeType enum.
     */
    protected function setAddresseeType(int $rowIndex, int|FieldHeaderInterface $field, AddresseeType $addresseeType): void {
        $this->setFieldValueWithQuoting($rowIndex, $this->resolveFieldIndex($field), $addresseeType->value, true);
    }

    // ==================== ADDRESS ENUMS ====================

    /**
     * Returns a field value as AddressType enum.
     */
    protected function getAddressType(int $rowIndex, int|FieldHeaderInterface $field): ?AddressType {
        $value = $this->getFieldValue($rowIndex, $this->resolveFieldIndex($field));
        if ($value === null) {
            return null;
        }

        return AddressType::tryFromString($value);
    }

    /**
     * Sets a field value from an AddressType enum.
     */
    protected function setAddressType(int $rowIndex, int|FieldHeaderInterface $field, AddressType $addressType): void {
        $this->setFieldValueWithQuoting($rowIndex, $this->resolveFieldIndex($field), $addressType->value, true);
    }

    // ==================== COMMUNICATION ENUMS ====================

    /**
     * Returns a field value as Language enum.
     */
    protected function getLanguage(int $rowIndex, int|FieldHeaderInterface $field): ?Language {
        $value = $this->getFieldValue($rowIndex, $this->resolveFieldIndex($field));
        if ($value === null) {
            return null;
        }

        return Language::tryFromString($value);
    }

    /**
     * Sets a field value from a Language enum.
     */
    protected function setLanguage(int $rowIndex, int|FieldHeaderInterface $field, Language $language): void {
        $this->setFieldValueWithQuoting($rowIndex, $this->resolveFieldIndex($field), (string) $language->value, false);
    }

    /**
     * Returns a field value as LanguageCode enum.
     */
    protected function getLanguageCode(int $rowIndex, int|FieldHeaderInterface $field): ?LanguageCode {
        $value = $this->getFieldValue($rowIndex, $this->resolveFieldIndex($field));
        if ($value === null) {
            return null;
        }

        return LanguageCode::tryFromString($value);
    }

    /**
     * Sets a field value from a LanguageCode enum.
     */
    protected function setLanguageCode(int $rowIndex, int|FieldHeaderInterface $field, LanguageCode $languageCode): void {
        $this->setFieldValueWithQuoting($rowIndex, $this->resolveFieldIndex($field), $languageCode->toCsvValue(), false);
    }

    /**
     * Returns a field value as OutputTarget enum.
     */
    protected function getOutputTarget(int $rowIndex, int|FieldHeaderInterface $field): ?OutputTarget {
        $value = $this->getFieldValue($rowIndex, $this->resolveFieldIndex($field));
        if ($value === null) {
            return null;
        }

        return OutputTarget::tryFromString($value);
    }

    /**
     * Sets a field value from an OutputTarget enum.
     */
    protected function setOutputTarget(int $rowIndex, int|FieldHeaderInterface $field, OutputTarget $outputTarget): void {
        $this->setFieldValueWithQuoting($rowIndex, $this->resolveFieldIndex($field), (string) $outputTarget->value, false);
    }

    /**
     * Returns a field value as CurrencyControl enum.
     */
    protected function getCurrencyControl(int $rowIndex, int|FieldHeaderInterface $field): ?CurrencyControl {
        $value = $this->getFieldValue($rowIndex, $this->resolveFieldIndex($field));
        if ($value === null) {
            return null;
        }

        return CurrencyControl::tryFromString($value);
    }

    /**
     * Sets a field value from a CurrencyControl enum.
     */
    protected function setCurrencyControl(int $rowIndex, int|FieldHeaderInterface $field, CurrencyControl $currencyControl): void {
        $this->setFieldValueWithQuoting($rowIndex, $this->resolveFieldIndex($field), (string) $currencyControl->value, false);
    }
}
