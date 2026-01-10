<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt202Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\Mt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt2\GeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt2\Type202\Document;
use InvalidArgumentException;

/**
 * Generator for MT202 General Financial Institution Transfer.
 * 
 * Generates SWIFT MT202 and MT202COV messages from Document objects.
 * 
 * @package CommonToolkit\Generators\Common\Banking\Mt
 */
class Mt202Generator extends GeneratorAbstract {
    /**
     * Generates the MT202/MT202COV SWIFT message.
     * 
     * @param object $document The MT202 document
     * @return string The formatted SWIFT message
     */
    public function generate(object $document): string {
        if (!$document instanceof Document) {
            throw new InvalidArgumentException('Expected Mt2\Type202\Document');
        }

        $lines = [];

        // Transaction Reference (:20:)
        $lines[] = ':20:' . $document->getTransactionReference();

        // Related Reference (:21:)
        $lines[] = ':21:' . $document->getRelatedReference();

        // Time Indication (:13C:) - optional
        if ($document->getTimeIndication() !== null) {
            $lines[] = ':13C:' . $document->getTimeIndication();
        }

        // Value Date/Currency/Amount (:32A:)
        $lines[] = ':32A:' . $document->toField32A();

        // Ordering Institution (:52a:) - optional
        $this->appendOptionalParty(
            $lines,
            $document->getOrderingInstitution(),
            ':52A:',
            ':52D:'
        );

        // Sender's Correspondent (:53a:) - optional
        $this->appendOptionalParty(
            $lines,
            $document->getSendersCorrespondent(),
            ':53A:',
            ':53D:'
        );

        // Receiver's Correspondent (:54a:) - optional
        $this->appendOptionalParty(
            $lines,
            $document->getReceiversCorrespondent(),
            ':54A:',
            ':54D:'
        );

        // Intermediary (:56a:) - optional
        $this->appendOptionalParty(
            $lines,
            $document->getIntermediary(),
            ':56A:',
            ':56D:'
        );

        // Account With Institution (:57a:) - optional
        $this->appendOptionalParty(
            $lines,
            $document->getAccountWithInstitution(),
            ':57A:',
            ':57D:'
        );

        // Beneficiary Institution (:58a:)
        $beneficiary = $document->getBeneficiaryInstitution();
        if ($beneficiary->isBicOnly()) {
            $lines[] = $this->formatPartyOptionA(':58A:', $beneficiary);
        } else {
            $lines[] = $this->formatPartyOptionD(':58D:', $beneficiary);
        }

        // Sender to Receiver Information (:72:) - optional
        if ($document->getSenderToReceiverInfo() !== null) {
            $lines[] = ':72:' . $document->getSenderToReceiverInfo();
        }

        return $this->joinLines($lines);
    }
}
