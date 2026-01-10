<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt950Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\Mt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\GeneratorAbstract;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\DocumentAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type950\Document;

/**
 * Generator for MT950 Statement Message.
 * 
 * Generates SWIFT MT950 messages from Document objects.
 * 
 * @package CommonToolkit\Generators\Common\Banking\Mt
 */
class Mt950Generator extends GeneratorAbstract {
    /**
     * Generates the MT950 SWIFT message.
     *
     * @param DocumentAbstract $document The MT950 document
     * @return string The formatted SWIFT message
     */
    public function generate(DocumentAbstract $document): string {
        if (!$document instanceof Document) {
            throw new \InvalidArgumentException('Expected Mt9\Type950\Document');
        }

        $lines = [];

        // Header fields
        $this->appendHeaderFields($lines, $document);

        // Opening Balance (:60F:)
        $lines[] = $this->formatBalance(':60F:', $document->getOpeningBalance());

        // Transactions (:61:)
        foreach ($document->getTransactions() as $txn) {
            foreach ($txn->toMt950Lines() as $line) {
                $lines[] = $line;
            }
        }

        // Closing Balance (:62F:)
        $lines[] = $this->formatBalance(':62F:', $document->getClosingBalance());

        // Closing Available Balance (:64:) - optional
        if ($document->getClosingAvailableBalance() !== null) {
            $lines[] = $this->formatBalance(':64:', $document->getClosingAvailableBalance());
        }

        $this->appendEndMarker($lines);

        return $this->joinLines($lines);
    }
}
