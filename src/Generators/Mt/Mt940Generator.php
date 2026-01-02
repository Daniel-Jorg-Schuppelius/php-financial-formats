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

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\Mt9GeneratorAbstract;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\MtDocumentAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type940\Document;

/**
 * Generator für MT940 Customer Statement Message.
 * 
 * Generiert SWIFT MT940 Nachrichten aus Document-Objekten.
 * 
 * @package CommonToolkit\Generators\Common\Banking\Mt
 */
class Mt940Generator extends Mt9GeneratorAbstract {
    /**
     * Generiert die MT940 SWIFT-Nachricht.
     * 
     * @param MtDocumentAbstract $document Das MT940-Dokument
     * @return string Die formatierte SWIFT-Nachricht
     */
    public function generate(MtDocumentAbstract $document): string {
        if (!$document instanceof Document) {
            throw new \InvalidArgumentException('Expected Mt9\Type940\Document');
        }

        $lines = [];

        // Header-Felder
        $this->appendHeaderFields($lines, $document);

        // Opening Balance (:60F:)
        $lines[] = $this->formatBalance(':60F:', $document->getOpeningBalance());

        // Transaktionen
        foreach ($document->getTransactions() as $txn) {
            $lines[] = (string) $txn;
        }

        // Closing Balance (:62F:)
        $lines[] = $this->formatBalance(':62F:', $document->getClosingBalance());

        // Closing Available Balance (:64:) - optional
        if ($document->getClosingAvailableBalance() !== null) {
            $lines[] = $this->formatBalance(':64:', $document->getClosingAvailableBalance());
        }

        // Forward Available Balance (:65:) - optional
        if ($document->getForwardAvailableBalance() !== null) {
            $lines[] = $this->formatBalance(':65:', $document->getForwardAvailableBalance());
        }

        $this->appendEndMarker($lines);

        return $this->joinLines($lines);
    }
}
