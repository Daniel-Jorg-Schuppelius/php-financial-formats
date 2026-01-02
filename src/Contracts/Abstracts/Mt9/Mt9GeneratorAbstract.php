<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt9GeneratorAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9;

use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;

/**
 * Abstrakte Basisklasse für MT9xx-Generatoren.
 * 
 * Gemeinsame Methoden für die Generierung von SWIFT MT9-Nachrichten.
 * 
 * @package CommonToolkit\Contracts\Abstracts\Common\Banking\Mt9
 */
abstract class Mt9GeneratorAbstract {
    /**
     * Zeilentrennzeichen für SWIFT-Nachrichten.
     */
    protected const LINE_SEPARATOR = "\r\n";

    /**
     * Generiert das Dokument als SWIFT MT-String.
     */
    abstract public function generate(MtDocumentAbstract $document): string;

    /**
     * Formatiert einen Balance im SWIFT-Format.
     * 
     * @param string $tag Tag-Präfix (z.B. ':60F:', ':62F:', ':60M:', ':62M:')
     * @param Balance $balance Das Balance-Objekt
     */
    protected function formatBalance(string $tag, Balance $balance): string {
        return $tag . (string) $balance;
    }

    /**
     * Fügt die Standard-Header-Felder hinzu.
     * 
     * @param string[] $lines Referenz auf das Array der Zeilen
     * @param MtDocumentAbstract $document Das Dokument
     */
    protected function appendHeaderFields(array &$lines, MtDocumentAbstract $document): void {
        $lines[] = ':20:' . $document->getReferenceId();
        $lines[] = ':25:' . $document->getAccountId();
        $lines[] = ':28C:' . $document->getStatementNumber();
    }

    /**
     * Fügt den Abschluss-Marker hinzu.
     * 
     * @param string[] $lines Referenz auf das Array der Zeilen
     */
    protected function appendEndMarker(array &$lines): void {
        $lines[] = '-';
    }

    /**
     * Verbindet die Zeilen zu einer SWIFT-Nachricht.
     * 
     * @param string[] $lines Die Zeilen
     * @return string Die formatierte SWIFT-Nachricht
     */
    protected function joinLines(array $lines): string {
        return implode(self::LINE_SEPARATOR, $lines) . self::LINE_SEPARATOR;
    }
}
