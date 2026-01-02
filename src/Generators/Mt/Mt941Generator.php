<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt941Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\Mt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\Mt9GeneratorAbstract;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\MtDocumentAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type941\Document;

/**
 * Generator für MT941 Balance Report.
 * 
 * Generiert SWIFT MT941 Nachrichten aus Document-Objekten.
 * 
 * @package CommonToolkit\Generators\Common\Banking\Mt
 */
class Mt941Generator extends Mt9GeneratorAbstract {
    /**
     * Generiert die MT941 SWIFT-Nachricht.
     * 
     * @param MtDocumentAbstract $document Das MT941-Dokument
     * @return string Die formatierte SWIFT-Nachricht
     */
    public function generate(MtDocumentAbstract $document): string {
        if (!$document instanceof Document) {
            throw new \InvalidArgumentException('Expected Mt9\Type941\Document');
        }

        $lines = [];

        // Header-Felder
        $this->appendHeaderFields($lines, $document);

        // Opening Balance (:60F:)
        $lines[] = $this->formatBalance(':60F:', $document->getOpeningBalance());

        // Closing Balance (:62F:)
        $lines[] = $this->formatBalance(':62F:', $document->getClosingBalance());

        // Closing Available Balance (:64:) - optional
        if ($document->getClosingAvailableBalance() !== null) {
            $lines[] = $this->formatBalance(':64:', $document->getClosingAvailableBalance());
        }

        // Forward Available Balances (:65:) - können mehrfach vorkommen
        foreach ($document->getForwardAvailableBalances() as $balance) {
            $lines[] = $this->formatBalance(':65:', $balance);
        }

        $this->appendEndMarker($lines);

        return $this->joinLines($lines);
    }
}
