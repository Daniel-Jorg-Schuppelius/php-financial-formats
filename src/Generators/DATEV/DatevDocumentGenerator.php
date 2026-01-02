<?php
/*
 * Created on   : Fri Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DatevDocumentGenerator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\DATEV;

use CommonToolkit\Entities\CSV\Document as CSVDocument;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV\Document;
use CommonToolkit\Helper\Data\StringHelper;

/**
 * Generator für DATEV-Dokumente.
 * 
 * Generiert DATEV-konforme CSV-Dateien mit:
 * - MetaHeader (Zeile 1)
 * - Header (Zeile 2)
 * - Datenzeilen
 * 
 * @package CommonToolkit\FinancialFormats\Generators\DATEV
 */
class DatevDocumentGenerator {
    /**
     * Generiert das DATEV-Dokument als CSV-String.
     * 
     * @param Document $document Das DATEV-Dokument
     * @param string|null $delimiter Das Trennzeichen (Standard: ;)
     * @param string|null $enclosure Das Einschlusszeichen (Standard: ")
     * @param int|null $enclosureRepeat Die Anzahl der Enclosure-Wiederholungen
     * @param string|null $targetEncoding Das Ziel-Encoding (Standard: UTF-8)
     * @return string Die formatierte CSV-Zeichenkette
     */
    public function generate(
        Document $document,
        ?string $delimiter = null,
        ?string $enclosure = null,
        ?int $enclosureRepeat = null,
        ?string $targetEncoding = null
    ): string {
        $delimiter ??= Document::DEFAULT_DELIMITER;
        $enclosure ??= '"';
        $targetEncoding ??= CSVDocument::DEFAULT_ENCODING;

        $lines = [];

        // MetaHeader als erste Zeile
        $metaHeader = $document->getMetaHeader();
        if ($metaHeader !== null) {
            $lines[] = $metaHeader->toString($delimiter, $enclosure);
        }

        // Header und Datenzeilen via Parent-Logik (CSVDocument)
        $csvContent = $this->generateCsvContent($document, $delimiter, $enclosure, $enclosureRepeat);
        if ($csvContent !== '') {
            $lines[] = $csvContent;
        }

        $result = implode("\n", $lines);

        // Encoding-Konvertierung falls nötig
        if ($targetEncoding !== CSVDocument::DEFAULT_ENCODING) {
            return StringHelper::convertEncoding($result, CSVDocument::DEFAULT_ENCODING, $targetEncoding);
        }

        return $result;
    }

    /**
     * Generiert den CSV-Inhalt (Header + Datenzeilen) ohne MetaHeader.
     */
    protected function generateCsvContent(
        Document $document,
        string $delimiter,
        string $enclosure,
        ?int $enclosureRepeat
    ): string {
        $lines = [];

        // Header-Zeile
        $header = $document->getHeader();
        if ($header !== null) {
            $lines[] = $header->toString($delimiter, $enclosure, $enclosureRepeat);
        }

        // Datenzeilen
        foreach ($document->getRows() as $row) {
            $lines[] = $row->toString($delimiter, $enclosure, $enclosureRepeat);
        }

        return implode("\n", $lines);
    }
}
