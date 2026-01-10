<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt940Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\Mt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\GeneratorAbstract;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\DocumentAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type940\Document;
use CommonToolkit\FinancialFormats\Enums\Mt\Mt940OutputFormat;

/**
 * Generator for MT940 Customer Statement Message.
 * 
 * Generates SWIFT MT940 messages from Document objects.
 * 
 * @package CommonToolkit\Generators\Common\Banking\Mt
 */
class Mt940Generator extends GeneratorAbstract {
    /**
     * Generates the MT940 SWIFT message.
     *
     * @param DocumentAbstract $document Das MT940-Dokument
     * @param Mt940OutputFormat $format Output format (SWIFT or DATEV)
     * @return string Die formatierte SWIFT-Nachricht
     */
    public function generate(DocumentAbstract $document, Mt940OutputFormat $format = Mt940OutputFormat::SWIFT): string {
        if (!$document instanceof Document) {
            throw new \InvalidArgumentException('Expected Mt9\Type940\Document');
        }

        $lines = [];

        // Header fields
        $this->appendHeaderFields($lines, $document);

        // Opening Balance (:60F:)
        $lines[] = $this->formatBalance(':60F:', $document->getOpeningBalance());

        // Transaktionen
        foreach ($document->getTransactions() as $txn) {
            foreach ($txn->toMt940Lines($format) as $line) {
                $lines[] = $line;
            }
        }

        // Closing Balance (:62F:)
        $lines[] = $this->formatBalance(':62F:', $document->getClosingBalance());

        // Closing Available Balance (:64:) - optional
        if ($document->getClosingAvailableBalance() !== null) {
            $lines[] = $this->formatBalance(':64:', $document->getClosingAvailableBalance());
        }

        // Forward Available Balances (:65:) - optional, can be repeated
        foreach ($document->getForwardAvailableBalances() as $forwardBalance) {
            $lines[] = $this->formatBalance(':65:', $forwardBalance);
        }

        // Statement-Level Information (:86: after balances) - optional
        if ($document->getStatementInfo() !== null) {
            $lines[] = $this->formatPurpose(':86:', $document->getStatementInfo());
        }

        $this->appendEndMarker($lines);

        return $this->joinLines($lines);
    }
}
