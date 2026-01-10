<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : GeneratorAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt1;

use CommonToolkit\FinancialFormats\Entities\Mt1\Party;

/**
 * Abstract base class for MT1xx generators.
 * 
 * Common methods for generating SWIFT MT1 messages
 * (payment orders such as MT101, MT103).
 * 
 * @package CommonToolkit\Contracts\Abstracts\Common\Banking\Mt1
 */
abstract class GeneratorAbstract {
    /**
     * Line separator for SWIFT messages.
     */
    protected const LINE_SEPARATOR = "\r\n";

    /**
     * Generates the document as a SWIFT MT string.
     */
    abstract public function generate(object $document): string;

    /**
     * Formats a party for Option A (BIC only).
     * 
     * @param string $tag Tag prefix (e.g. ':52A:', ':57A:')
     * @param Party $party Die Party
     */
    protected function formatPartyOptionA(string $tag, Party $party): string {
        return $tag . $party->toOptionA();
    }

    /**
     * Formats a party for Option K (Name + Address).
     * 
     * @param string $tag Tag prefix (e.g. ':50K:', ':59:')
     * @param Party $party Die Party
     */
    protected function formatPartyOptionK(string $tag, Party $party): string {
        return $tag . $party->toOptionK();
    }

    /**
     * Adds an optional party if present.
     * 
     * @param string[] $lines Referenz auf das Array der Zeilen
     * @param Party|null $party Die Party (optional)
     * @param string $bicTag Tag for BIC-only format
     * @param string $fullTag Tag for full format
     */
    protected function appendOptionalParty(
        array &$lines,
        ?Party $party,
        string $bicTag,
        string $fullTag
    ): void {
        if ($party === null) {
            return;
        }

        if ($party->isBicOnly()) {
            $lines[] = $this->formatPartyOptionA($bicTag, $party);
        } else {
            $lines[] = $this->formatPartyOptionK($fullTag, $party);
        }
    }

    /**
     * Verbindet die Zeilen zu einer SWIFT-Nachricht.
     * 
     * @param string[] $lines Die Zeilen
     * @return string Die formatierte SWIFT-Nachricht
     */
    protected function joinLines(array $lines): string {
        return implode(self::LINE_SEPARATOR, $lines);
    }
}