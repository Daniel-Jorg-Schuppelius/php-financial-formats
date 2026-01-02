<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt942Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\Mt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\Mt9GeneratorAbstract;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\MtDocumentAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type942\Document;

/**
 * Generator für MT942 Interim Transaction Report.
 * 
 * Generiert SWIFT MT942 Nachrichten aus Document-Objekten.
 * 
 * @package CommonToolkit\Generators\Common\Banking\Mt
 */
class Mt942Generator extends Mt9GeneratorAbstract {
    /**
     * Generiert die MT942 SWIFT-Nachricht.
     * 
     * @param MtDocumentAbstract $document Das MT942-Dokument
     * @return string Die formatierte SWIFT-Nachricht
     */
    public function generate(MtDocumentAbstract $document): string {
        if (!$document instanceof Document) {
            throw new \InvalidArgumentException('Expected Mt9\Type942\Document');
        }

        $lines = [];

        // Header-Felder
        $this->appendHeaderFields($lines, $document);

        // DateTime Indicator (:13D:) - optional
        if ($document->getDateTimeIndicator() !== null) {
            $lines[] = ':13D:' . $document->getDateTimeIndicator()->format('ymdHi') . '+0000';
        }

        // Floor Limit Indicator (:34F:) - optional
        if ($document->getFloorLimitIndicator() !== null) {
            $lines[] = ':34F:' . $document->getCurrency()->value .
                number_format($document->getFloorLimitIndicator(), 2, ',', '');
        }

        // Opening Balance (:60M:) - optional bei MT942
        if ($document->getOpeningBalance() !== null) {
            $lines[] = $this->formatBalance(':60M:', $document->getOpeningBalance());
        }

        // Transaktionen
        foreach ($document->getTransactions() as $txn) {
            $lines[] = (string) $txn;
        }

        // Summary fields (:90D:, :90C:)
        $this->appendSummaryFields($lines, $document);

        // Closing Balance (:62M:)
        $lines[] = $this->formatBalance(':62M:', $document->getClosingBalance());

        $this->appendEndMarker($lines);

        return $this->joinLines($lines);
    }

    /**
     * Fügt die Zusammenfassungsfelder hinzu.
     * 
     * @param string[] $lines Referenz auf das Array der Zeilen
     * @param Document $document Das MT942-Dokument
     */
    private function appendSummaryFields(array &$lines, Document $document): void {
        $debitCount = $this->countDebitEntries($document);
        $creditCount = $this->countCreditEntries($document);

        if ($debitCount > 0) {
            $lines[] = sprintf(
                ':90D:%d%s%s',
                $debitCount,
                $document->getCurrency()->value,
                number_format($document->getTotalDebit(), 2, ',', '')
            );
        }

        if ($creditCount > 0) {
            $lines[] = sprintf(
                ':90C:%d%s%s',
                $creditCount,
                $document->getCurrency()->value,
                number_format($document->getTotalCredit(), 2, ',', '')
            );
        }
    }

    /**
     * Zählt die Soll-Buchungen.
     */
    private function countDebitEntries(Document $document): int {
        return count(array_filter(
            $document->getTransactions(),
            fn($txn) => $txn->isDebit()
        ));
    }

    /**
     * Zählt die Haben-Buchungen.
     */
    private function countCreditEntries(Document $document): int {
        return count(array_filter(
            $document->getTransactions(),
            fn($txn) => $txn->isCredit()
        ));
    }
}
