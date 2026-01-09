<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt102Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\Mt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt1\Mt1GeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type102\Document;
use InvalidArgumentException;

/**
 * Generator for MT102 Multiple Customer Credit Transfer.
 * 
 * Generates SWIFT MT102 messages from Document objects.
 * 
 * @package CommonToolkit\Generators\Common\Banking\Mt
 */
class Mt102Generator extends Mt1GeneratorAbstract {
    /**
     * Generates the MT102 SWIFT message.
     * 
     * @param object $document The MT102 document
     * @return string The formatted SWIFT message
     */
    public function generate(object $document): string {
        if (!$document instanceof Document) {
            throw new InvalidArgumentException('Expected Mt1\Type102\Document');
        }

        $lines = [];

        // Sequence A - General Information
        $lines[] = ':20:' . $document->getSendersReference();

        // Bank Operation Code (:23:)
        $lines[] = ':23:' . $document->getBankOperationCode();

        // Ordering Customer (:50H: or :50K:)
        $orderingCustomer = $document->getOrderingCustomer();
        if ($orderingCustomer->hasAccount()) {
            $lines[] = $this->formatPartyOptionK(':50H:', $orderingCustomer);
        } else {
            $lines[] = $this->formatPartyOptionK(':50K:', $orderingCustomer);
        }

        // Ordering Institution (:52A: or :52C:) - optional
        $this->appendOptionalParty(
            $lines,
            $document->getOrderingInstitution(),
            ':52A:',
            ':52C:'
        );

        // Transaction Type Code (:26T:) - optional
        if ($document->getTransactionTypeCode() !== null) {
            $lines[] = ':26T:' . $document->getTransactionTypeCode();
        }

        // Regulatory Reporting (:77B:) - optional
        if ($document->getRegulatoryReporting() !== null) {
            $lines[] = ':77B:' . $document->getRegulatoryReporting();
        }

        // Details of Charges (:71A:) - optional
        if ($document->getDetailsOfCharges() !== null) {
            $lines[] = ':71A:' . $document->getDetailsOfCharges();
        }

        // Exchange Rate (:36:) - optional
        if ($document->getExchangeRate() !== null) {
            $lines[] = ':36:' . number_format($document->getExchangeRate(), 6, ',', '');
        }

        // Sequence B - Transaction Details
        foreach ($document->getTransactions() as $txn) {
            $lines[] = (string) $txn;
        }

        // Sequence C - Summary
        $lines[] = ':32A:' . $document->toField32A();

        return $this->joinLines($lines);
    }
}
