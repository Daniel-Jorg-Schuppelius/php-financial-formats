<?php
/*
 * Created on   : Mon Dec 16 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : HeaderLineAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV;

use CommonToolkit\Contracts\Interfaces\CSV\FieldInterface;
use CommonToolkit\FinancialFormats\Contracts\Interfaces\DATEV\FieldHeaderInterface;
use CommonToolkit\Entities\CSV\{HeaderField, HeaderLine};
use CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV\Document;
use CommonToolkit\Enums\CountryCode;
use InvalidArgumentException;

/**
 * Abstract base class for DATEV header lines (column descriptions).
 * Encapsulates the common functionality of all DATEV header lines.
 * 
 * Arbeitet direkt mit den HeaderField-Enums, die FieldHeaderInterface implementieren.
 */
abstract class HeaderLineAbstract extends HeaderLine {
    /** @var class-string<FieldHeaderInterface> */
    protected string $fieldEnumClass;
    protected array $fieldIndex = [];

    /**
     * @param class-string<FieldHeaderInterface> $fieldEnumClass Der HeaderField-Enum-Klassenname
     * @param string $delimiter CSV-Trennzeichen
     * @param string $enclosure CSV-Textbegrenzer
     */
    public function __construct(
        string $fieldEnumClass,
        string $delimiter = Document::DEFAULT_DELIMITER,
        string $enclosure = FieldInterface::DEFAULT_ENCLOSURE
    ) {
        if (!enum_exists($fieldEnumClass) || !is_subclass_of($fieldEnumClass, FieldHeaderInterface::class)) {
            throw new InvalidArgumentException("$fieldEnumClass muss ein Enum sein, das FieldHeaderInterface implementiert.");
        }

        $this->fieldEnumClass = $fieldEnumClass;

        // Alle Felder aus dem Enum als Header setzen
        $rawFields = [];
        /** @var FieldHeaderInterface[] $fields */
        $fields = $fieldEnumClass::ordered();

        foreach ($fields as $index => $field) {
            $this->fieldIndex[$field->value] = $index;
            // Quoting basierend auf isQuotedHeader() des Feldes
            // headerName() für tatsächlichen CSV-Header-Namen verwenden
            $rawFields[$index] = $field->isQuotedHeader()
                ? $enclosure . $field->headerName() . $enclosure
                : $field->headerName();
        }

        parent::__construct($rawFields, $delimiter, $enclosure);
    }

    /**
     * Factory method for minimal header (required fields only).
     * 
     * @param class-string<FieldHeaderInterface> $fieldEnumClass
     */
    public static function createMinimal(
        string $fieldEnumClass,
        string $delimiter = Document::DEFAULT_DELIMITER,
        string $enclosure = FieldInterface::DEFAULT_ENCLOSURE
    ): static {
        $instance = new static($fieldEnumClass, $delimiter, $enclosure);

        // Nur Pflichtfelder setzen
        /** @var FieldHeaderInterface[] $requiredFields */
        $requiredFields = $fieldEnumClass::required();
        $rawFields = [];
        $fieldIndex = [];

        foreach ($requiredFields as $index => $field) {
            // Quoting basierend auf isQuotedHeader() des Feldes
            // headerName() für tatsächlichen CSV-Header-Namen verwenden
            $rawFields[$index] = $field->isQuotedHeader()
                ? $enclosure . $field->headerName() . $enclosure
                : $field->headerName();
            $fieldIndex[$field->value] = $index;
        }

        // Neu initialisieren mit reduzierten Feldern
        $instance->fields = [];
        $instance->fieldIndex = $fieldIndex;

        foreach ($rawFields as $rawField) {
            $instance->fields[] = new HeaderField($rawField, $enclosure);
        }

        return $instance;
    }

    /**
     * Returns the enum class for header fields.
     * 
     * @return class-string<FieldHeaderInterface>
     */
    public function getFieldEnumClass(): string {
        return $this->fieldEnumClass;
    }

    /**
     * Checks if a field is present in this header.
     */
    public function hasField(FieldHeaderInterface|string $field): bool {
        $fieldName = $field instanceof FieldHeaderInterface ? $field->value : $field;
        return isset($this->fieldIndex[$fieldName]);
    }

    /**
     * Liefert den Index eines Feldes oder -1 wenn nicht gefunden.
     */
    public function getFieldIndex(FieldHeaderInterface|string $field): int {
        $fieldName = $field instanceof FieldHeaderInterface ? $field->value : $field;
        return $this->fieldIndex[$fieldName] ?? -1;
    }

    /**
     * Validiert den Header gegen den Enum.
     */
    public function validate(): void {
        $fieldEnumClass = $this->fieldEnumClass;
        $requiredFields = $fieldEnumClass::required();
        $headerFields = array_map(fn($f) => trim($f->getValue(), '"'), $this->getFields());

        $requiredValues = array_map(fn($f) => $f->value, $requiredFields);
        $missing = array_diff($requiredValues, $headerFields);

        if (!empty($missing)) {
            throw new InvalidArgumentException(
                'Verpflichtende Felder fehlen: ' . implode(', ', $missing)
            );
        }
    }

    /**
     * Checks if this header matches a specific DATEV format.
     */
    public function isCompatibleWithEnum(string $enumClass): bool {
        if (!enum_exists($enumClass)) {
            return false;
        }

        $headerFields = array_map(fn($f) => trim($f->getValue(), '"'), $this->getFields());
        $enumValues = array_map(fn($case) => $case->value, $enumClass::cases());

        foreach ($headerFields as $headerField) {
            if (!in_array($headerField, $enumValues, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Ermittelt zu welchem DATEV-Format dieser Header passt.
     * 
     * @param string[] $candidateEnums List of enum classes to check
     * @return string|null Erste passende Enum-Klasse oder null
     */
    public function detectFormat(array $candidateEnums): ?string {
        foreach ($candidateEnums as $enumClass) {
            if ($this->isCompatibleWithEnum($enumClass)) {
                return $enumClass;
            }
        }
        return null;
    }

    /**
     * Returns the format name for compatibility checks.
     * Can be overridden by concrete implementations.
     */
    protected function getFormatName(): string {
        $className = static::class;
        $baseName = basename(str_replace('\\', '/', $className));
        return str_replace('HeaderLine', '', $baseName);
    }

    protected static function createField(string $rawValue, string $enclosure): FieldInterface {
        return new HeaderField($rawValue, $enclosure, CountryCode::Germany);
    }
}
