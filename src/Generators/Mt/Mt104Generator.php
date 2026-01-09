<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt104Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\Mt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt1\Mt1GeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type104\Document;
use InvalidArgumentException;

/**
 * Generator for MT104 Direct Debit Message.
 * 
 * Generates SWIFT MT104 messages from Document objects.
 * 
 * @package CommonToolkit\Generators\Common\Banking\Mt
 */
class Mt104Generator extends Mt1GeneratorAbstract {
    /**
     * Generates the MT104 SWIFT message.
     * 
     * @param object $document The MT104 document
     * @return string The formatted SWIFT message
     */
    public function generate(object $document): string {
        if (!$document instanceof Document) {
            throw new InvalidArgumentException('Expected Mt1\Type104\Document');
        }

        $lines = [];

        // Sequence A - General Information
        $lines[] = ':20:' . $document->getSendersReference();

        // Mandate Reference (:21E:) - optional
        if ($document->getMandateReference() !== null) {
            $lines[] = ':21E:' . $document->getMandateReference();
        }

        // Instruction Code (:23E:) - optional
        if ($document->getInstructionCode() !== null) {
            $lines[] = ':23E:' . $document->getInstructionCode();
        }

        // Transaction Type Code (:26T:) - optional
        if ($document->getTransactionTypeCode() !== null) {
            $lines[] = ':26T:' . $document->getTransactionTypeCode();
        }

        // Requested Execution Date (:30:)
        $lines[] = ':30:' . $document->getRequestedExecutionDate()->format('ymd');

        // Creditor (:50H: or :50K:)
        $creditor = $document->getCreditor();
        if ($creditor->hasAccount()) {
            $lines[] = $this->formatPartyOptionK(':50H:', $creditor);
        } else {
            $lines[] = $this->formatPartyOptionK(':50K:', $creditor);
        }

        // Sending Institution (:51A:) - optional
        $this->appendOptionalParty(
            $lines,
            $document->getSendingInstitution(),
            ':51A:',
            ':51A:'
        );

        // Creditor's Bank (:52A: or :52D:) - optional
        $this->appendOptionalParty(
            $lines,
            $document->getCreditorsBank(),
            ':52A:',
            ':52D:'
        );

        // Sender's Correspondent (:53A: or :53D:) - optional
        $this->appendOptionalParty(
            $lines,
            $document->getSendersCorrespondent(),
            ':53A:',
            ':53D:'
        );

        // Details of Charges (:71A:) - optional
        if ($document->getDetailsOfCharges() !== null) {
            $lines[] = ':71A:' . $document->getDetailsOfCharges();
        }

        // Sender to Receiver Information (:72:) - optional
        if ($document->getSenderToReceiverInfo() !== null) {
            $lines[] = ':72:' . $document->getSenderToReceiverInfo();
        }

        // Regulatory Reporting (:77B:) - optional
        if ($document->getRegulatoryReporting() !== null) {
            $lines[] = ':77B:' . $document->getRegulatoryReporting();
        }

        // Sequence B - Transaction Details
        foreach ($document->getTransactions() as $txn) {
            $lines[] = (string) $txn;
        }

        // Sequence C - Summary
        $lines[] = ':32B:' . $document->toField32B();

        return $this->joinLines($lines);
    }
}
