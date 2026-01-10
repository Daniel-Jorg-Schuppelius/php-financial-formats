<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt101Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\Mt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt1\GeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type101\Document;

/**
 * Generator for MT101 Request for Transfer.
 * 
 * Generates SWIFT MT101 messages from Document objects.
 * 
 * @package CommonToolkit\Generators\Common\Banking\Mt
 */
class Mt101Generator extends GeneratorAbstract {
    /**
     * Generates the MT101 SWIFT message.
     * 
     * @param object $document Das MT101-Dokument
     * @return string Die formatierte SWIFT-Nachricht
     */
    public function generate(object $document): string {
        if (!$document instanceof Document) {
            throw new \InvalidArgumentException('Expected Mt1\Type101\Document');
        }

        $lines = [];

        // Sequence A - General Information
        $lines[] = ':20:' . $document->getSendersReference();

        // Customer Specified Reference (:21R:) - optional
        if ($document->getCustomerReference() !== null) {
            $lines[] = ':21R:' . $document->getCustomerReference();
        }

        // Message Index (:28D:)
        $lines[] = ':28D:' . $document->getMessageIndex();

        // Ordering Customer (:50H: oder :50K:)
        $orderingCustomer = $document->getOrderingCustomer();
        if ($orderingCustomer->hasAccount()) {
            $lines[] = $this->formatPartyOptionK(':50H:', $orderingCustomer);
        } else {
            $lines[] = $this->formatPartyOptionK(':50K:', $orderingCustomer);
        }

        // Ordering Institution (:52A: oder :52C:) - optional
        $this->appendOrderingInstitution($lines, $document);

        // Requested Execution Date (:30:)
        $lines[] = ':30:' . $document->getRequestedExecutionDate()->format('ymd');

        // Sequence B - Transaction Details
        foreach ($document->getTransactions() as $txn) {
            $lines[] = (string) $txn;
        }

        return $this->joinLines($lines);
    }

    /**
     * Adds the ordering institution if present.
     * 
     * @param string[] $lines Referenz auf das Array der Zeilen
     * @param Document $document Das Dokument
     */
    private function appendOrderingInstitution(array &$lines, Document $document): void {
        $orderingInstitution = $document->getOrderingInstitution();

        if ($orderingInstitution === null) {
            return;
        }

        if ($orderingInstitution->isBicOnly()) {
            $lines[] = $this->formatPartyOptionA(':52A:', $orderingInstitution);
        } else {
            $lines[] = $this->formatPartyOptionK(':52C:', $orderingInstitution);
        }
    }
}
