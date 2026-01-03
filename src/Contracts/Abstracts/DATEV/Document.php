<?php
/*
 * Created on   : Wed Nov 05 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Document.php
 * License      : MIT License
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV;

use CommonToolkit\Entities\CSV\ColumnWidthConfig;
use CommonToolkit\Entities\CSV\Document as CSVDocument;
use CommonToolkit\Entities\CSV\HeaderLine;
use CommonToolkit\FinancialFormats\Entities\DATEV\{DataLine, MetaHeaderLine};
use CommonToolkit\FinancialFormats\Generators\DATEV\DatevDocumentGenerator;
use CommonToolkit\FinancialFormats\Traits\DATEV\DatevEnumConversionTrait;
use RuntimeException;

/**
 * Abstract base class for DATEV documents.
 * 
 * Extends the CSV Document class with DATEV-specific functionality:
 * - MetaHeader support
 * - DATEV-spezifische Validierung
 * - Enum-Konvertierungen via Trait
 * 
 * @package CommonToolkit\Contracts\Abstracts\DATEV
 */
abstract class Document extends CSVDocument {
    use DatevEnumConversionTrait;
    public const DEFAULT_DELIMITER = ';';

    private ?MetaHeaderLine $metaHeader = null;

    /** @param DataLine[] $rows */
    public function __construct(?MetaHeaderLine $metaHeader, ?HeaderLine $header, array $rows = [], ?ColumnWidthConfig $columnWidthConfig = null, string $encoding = CSVDocument::DEFAULT_ENCODING) {
        // Falls keine ColumnWidthConfig übergeben wurde, erstelle eine basierend auf DATEV-Spezifikation
        $columnWidthConfig ??= static::createDatevColumnWidthConfig();

        parent::__construct($header, $rows, ';', '"', $columnWidthConfig, $encoding);
        $this->metaHeader  = $metaHeader;
    }

    /**
     * Creates a ColumnWidthConfig based on DATEV specifications.
     * Must be overridden by derived classes to define specific field widths.
     * 
     * @return ColumnWidthConfig|null
     */
    public static function createDatevColumnWidthConfig(): ?ColumnWidthConfig {
        // Default implementation returns null
        // Abgeleitete Klassen sollten dies überschreiben
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
     * Muss von abgeleiteten Klassen implementiert werden.
     */
    abstract public function getFormatType(): string;

    /**
     * Wandelt das gesamte DATEV-Dokument in eine rohe CSV-Zeichenkette um.
     * Overrides the parent method to include the MetaHeader.
     *
     * @param string|null $delimiter The delimiter. If null, the default delimiter is used.
     * @param string|null $enclosure The enclosure. If null, the default enclosure is used.
     * @param int|null $enclosureRepeat Die Anzahl der Enclosure-Wiederholungen.
     * @param string|null $targetEncoding The target encoding. If null, the document encoding is used.
     * @return string
     */
    public function toString(?string $delimiter = null, ?string $enclosure = null, ?int $enclosureRepeat = null, ?string $targetEncoding = null): string {
        return (new DatevDocumentGenerator())->generate(
            $this,
            $delimiter ?? $this->delimiter,
            $enclosure ?? $this->enclosure,
            $enclosureRepeat,
            $targetEncoding ?? $this->encoding
        );
    }

    /**
     * Returns the document as string (DATEV CSV format).
     */
    public function __toString(): string {
        return $this->toString();
    }
}
