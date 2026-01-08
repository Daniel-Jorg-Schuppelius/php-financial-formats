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
 * Abstract base class for MT9xx generators.
 * 
 * Common methods for generating SWIFT MT9 messages.
 * 
 * @package CommonToolkit\Contracts\Abstracts\Common\Banking\Mt9
 */
abstract class Mt9GeneratorAbstract {
    /**
     * Line separator for SWIFT messages.
     */
    protected const LINE_SEPARATOR = "\r\n";

    /**
     * Generates the document as a SWIFT MT string.
     */
    abstract public function generate(MtDocumentAbstract $document): string;

    /**
     * Formatiert einen Balance im SWIFT-Format.
     * 
     * @param string $tag Tag prefix (e.g. ':60F:', ':62F:', ':60M:', ':62M:')
     * @param Balance $balance Das Balance-Objekt
     */
    protected function formatBalance(string $tag, Balance $balance): string {
        return $tag . (string) $balance;
    }

    /**
     * Adds the standard header fields.
     * 
     * @param string[] $lines Referenz auf das Array der Zeilen
     * @param MtDocumentAbstract $document Das Dokument
     */
    protected function appendHeaderFields(array &$lines, MtDocumentAbstract $document): void {
        $lines[] = ':20:' . $document->getReferenceId();

        // :21: Related Reference - optional
        if ($document->getRelatedReference() !== null) {
            $lines[] = ':21:' . $document->getRelatedReference();
        }

        $lines[] = ':25:' . $document->getAccountId();
        $lines[] = ':28C:' . $document->getStatementNumber();
    }

    /**
     * Formats a purpose/information field with line wrapping.
     * MT940 :86: field allows max 6 lines of 65 characters each.
     * 
     * @param string $tag Tag prefix (e.g. ':86:')
     * @param string $content The content to format
     * @param int $lineLength Maximum characters per line (default 65)
     * @param int $maxLines Maximum number of lines (default 6)
     */
    protected function formatPurpose(string $tag, string $content, int $lineLength = 65, int $maxLines = 6): string {
        // First line includes tag, subsequent lines are continuations
        $firstLineLength = $lineLength - strlen($tag) + 4; // Adjust for tag
        $result = $tag . substr($content, 0, $firstLineLength);

        if (strlen($content) > $firstLineLength) {
            $remaining = substr($content, $firstLineLength);
            $lines = str_split($remaining, $lineLength);
            $lines = array_slice($lines, 0, $maxLines - 1); // Limit to remaining lines
            $result .= self::LINE_SEPARATOR . implode(self::LINE_SEPARATOR, $lines);
        }

        return $result;
    }

    /**
     * Adds the closing marker.
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
