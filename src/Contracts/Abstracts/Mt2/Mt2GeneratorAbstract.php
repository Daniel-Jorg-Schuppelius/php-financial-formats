<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt2GeneratorAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt2;

use CommonToolkit\FinancialFormats\Entities\Mt1\Party;

/**
 * Abstract base class for MT2xx generators.
 * 
 * Common methods for generating SWIFT MT2 messages
 * (financial institution transfers such as MT200, MT202).
 * 
 * @package CommonToolkit\Contracts\Abstracts\Common\Banking\Mt2
 */
abstract class Mt2GeneratorAbstract {
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
     * @param Party $party The party
     */
    protected function formatPartyOptionA(string $tag, Party $party): string {
        return $tag . $party->toOptionA();
    }

    /**
     * Formats a party for Option D (Name + Address).
     * 
     * @param string $tag Tag prefix (e.g. ':58D:')
     * @param Party $party The party
     */
    protected function formatPartyOptionD(string $tag, Party $party): string {
        return $tag . $party->toOptionK();
    }

    /**
     * Adds an optional party if present.
     * 
     * @param string[] $lines Reference to the array of lines
     * @param Party|null $party The party (optional)
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
            $lines[] = $this->formatPartyOptionD($fullTag, $party);
        }
    }

    /**
     * Joins lines to a SWIFT message.
     * 
     * @param string[] $lines The lines
     * @return string The formatted SWIFT message
     */
    protected function joinLines(array $lines): string {
        return implode(self::LINE_SEPARATOR, $lines);
    }
}
