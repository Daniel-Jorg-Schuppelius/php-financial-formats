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
use CommonToolkit\FinancialFormats\Traits\DATEV\DatevEnumConversionTrait;
use CommonToolkit\Helper\Data\StringHelper;
use RuntimeException;

/**
 * Abstrakte Basisklasse für DATEV-Dokumente.
 * 
 * Erweitert die CSV-Document-Klasse um DATEV-spezifische Funktionalität:
 * - MetaHeader-Unterstützung
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
     * Erstellt eine ColumnWidthConfig basierend auf den DATEV-Spezifikationen.
     * Muss von abgeleiteten Klassen überschrieben werden, um die spezifischen Feldbreiten zu definieren.
     * 
     * @return ColumnWidthConfig|null
     */
    public static function createDatevColumnWidthConfig(): ?ColumnWidthConfig {
        // Standardimplementierung gibt null zurück
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
            throw new RuntimeException('DATEV-Feldheader fehlt.');
        }

        $metaValues = array_map(fn($f) => trim($f->getValue(), "\"'"), $this->metaHeader->getFields());
        if ($metaValues[0] !== 'EXTF') {
            throw new RuntimeException('Ungültiger DATEV-Metadatenheader – "EXTF" erwartet.');
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
     * Gibt den DATEV-Format-Typ zurück.
     * Muss von abgeleiteten Klassen implementiert werden.
     */
    abstract public function getFormatType(): string;

    /**
     * Wandelt das gesamte DATEV-Dokument in eine rohe CSV-Zeichenkette um.
     * Überschreibt die Parent-Methode, um den MetaHeader mit einzubeziehen.
     *
     * @param string|null $delimiter Das Trennzeichen. Wenn null, wird das Standard-Trennzeichen verwendet.
     * @param string|null $enclosure Das Einschlusszeichen. Wenn null, wird das Standard-Einschlusszeichen verwendet.
     * @param int|null $enclosureRepeat Die Anzahl der Enclosure-Wiederholungen.
     * @param string|null $targetEncoding Das Ziel-Encoding. Wenn null, wird das Dokument-Encoding verwendet.
     * @return string
     */
    public function toString(?string $delimiter = null, ?string $enclosure = null, ?int $enclosureRepeat = null, ?string $targetEncoding = null): string {
        $delimiter ??= $this->delimiter;
        $enclosure ??= $this->enclosure;
        $targetEncoding ??= $this->encoding;

        $lines = [];

        // MetaHeader als erste Zeile
        if ($this->metaHeader) {
            $lines[] = $this->metaHeader->toString($delimiter, $enclosure);
        }

        // Parent-Logik für Header und Datenzeilen (immer UTF-8, Konvertierung am Ende)
        $parentContent = parent::toString($delimiter, $enclosure, $enclosureRepeat, CSVDocument::DEFAULT_ENCODING);
        if ($parentContent !== '') {
            $lines[] = $parentContent;
        }

        $result = implode("\n", $lines);

        // Encoding-Konvertierung falls nötig - nutze StringHelper
        if ($targetEncoding !== CSVDocument::DEFAULT_ENCODING) {
            return StringHelper::convertEncoding($result, CSVDocument::DEFAULT_ENCODING, $targetEncoding);
        }

        return $result;
    }

    /**
     * Gibt das Dokument als String zurück (DATEV-CSV-Format).
     */
    public function __toString(): string {
        return $this->toString();
    }
}
