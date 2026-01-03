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
use CommonToolkit\Contracts\Interfaces\CSV\LineInterface;
use CommonToolkit\FinancialFormats\Contracts\Interfaces\DATEV\FieldHeaderInterface;
use CommonToolkit\Entities\CSV\{HeaderField, HeaderLine};
use CommonToolkit\Enums\CountryCode;
use ERRORToolkit\Traits\ErrorLog;
use InvalidArgumentException;

/**
 * Abstract base class for DATEV header lines (column descriptions).
 * 
 * Erweitert die CSV HeaderLine um DATEV-spezifische Enum-Unterstützung.
 * Die meiste Funktionalität wird von der Elternklasse geerbt.
 */
abstract class HeaderLineAbstract extends HeaderLine {
    use ErrorLog;

    /** @var class-string<FieldHeaderInterface> */
    protected string $fieldEnumClass;

    /**
     * @param class-string<FieldHeaderInterface> $fieldEnumClass Der HeaderField-Enum-Klassenname
     * @param string $delimiter CSV-Trennzeichen
     * @param string $enclosure CSV-Textbegrenzer
     */
    public function __construct(string $fieldEnumClass, string $delimiter = Document::DEFAULT_DELIMITER, string $enclosure = FieldInterface::DEFAULT_ENCLOSURE) {
        if (!enum_exists($fieldEnumClass) || !is_subclass_of($fieldEnumClass, FieldHeaderInterface::class)) {
            $this->logError("Ungültige Enum-Klasse für Header-Felder: $fieldEnumClass");
            throw new InvalidArgumentException("$fieldEnumClass muss ein Enum sein, das FieldHeaderInterface implementiert.");
        }

        $this->fieldEnumClass = $fieldEnumClass;

        // Alle Felder aus dem Enum als Header setzen
        $rawFields = [];
        /** @var FieldHeaderInterface[] $fields */
        $fields = $fieldEnumClass::ordered();

        foreach ($fields as $index => $field) {
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
    public static function createMinimal(string $fieldEnumClass, string $delimiter = Document::DEFAULT_DELIMITER, string $enclosure = FieldInterface::DEFAULT_ENCLOSURE): static {
        // Nur Pflichtfelder
        /** @var FieldHeaderInterface[] $requiredFields */
        $requiredFields = $fieldEnumClass::required();
        $rawFields = [];

        foreach ($requiredFields as $index => $field) {
            $rawFields[$index] = $field->isQuotedHeader()
                ? $enclosure . $field->headerName() . $enclosure
                : $field->headerName();
        }

        $instance = new static($fieldEnumClass, $delimiter, $enclosure);
        $instance->fields = [];

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
     * Liefert den Index eines Feldes (Enum oder Feldname).
     * Nutzt intern getColumnIndex() der Elternklasse.
     */
    public function getFieldIndex(FieldHeaderInterface|string $field): int {
        $fieldName = $field instanceof FieldHeaderInterface ? $field->headerName() : $field;
        return $this->getColumnIndex($fieldName) ?? -1;
    }

    /**
     * Checks if a field is present in this header.
     */
    public function hasField(FieldHeaderInterface|string $field): bool {
        return $this->getFieldIndex($field) >= 0;
    }

    /**
     * Validiert den Header gegen den Enum.
     */
    public function validate(): void {
        $requiredFields = ($this->fieldEnumClass)::required();
        $missing = [];

        foreach ($requiredFields as $field) {
            if (!$this->hasColumn($field->headerName())) {
                $missing[] = $field->value;
            }
        }

        if (!empty($missing)) {
            $this->logError('Verpflichtende Felder fehlen: ' . implode(', ', $missing));
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

        $enumValues = array_map(fn($case) => $case->value, $enumClass::cases());

        foreach ($this->getColumnNames() as $headerField) {
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

    protected static function createField(string $rawValue, string $enclosure): FieldInterface {
        return new HeaderField($rawValue, $enclosure, CountryCode::Germany);
    }

    // ==== Enum-basierter Feldzugriff ====

    /**
     * Retrieves a field value using a FieldHeaderInterface enum.
     * Delegates to getValueByName() from parent class.
     */
    public function getFieldValue(LineInterface $row, FieldHeaderInterface $field): ?string {
        return $this->getValueByName($row, $field->headerName());
    }

    /**
     * Checks if a field has a non-empty value using a FieldHeaderInterface enum.
     */
    public function hasFieldValue(LineInterface $row, FieldHeaderInterface $field): bool {
        return $this->hasValueByName($row, $field->headerName());
    }
}
