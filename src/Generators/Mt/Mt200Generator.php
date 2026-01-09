<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt200Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\Mt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt2\Mt2GeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt2\Type200\Document;
use InvalidArgumentException;

/**
 * Generator for MT200 Financial Institution Transfer for its Own Account.
 * 
 * Generates SWIFT MT200 messages from Document objects.
 * 
 * @package CommonToolkit\Generators\Common\Banking\Mt
 */
class Mt200Generator extends Mt2GeneratorAbstract {
    /**
     * Generates the MT200 SWIFT message.
     * 
     * @param object $document The MT200 document
     * @return string The formatted SWIFT message
     */
    public function generate(object $document): string {
        if (!$document instanceof Document) {
            throw new InvalidArgumentException('Expected Mt2\Type200\Document');
        }

        $lines = [];

        // Transaction Reference (:20:)
        $lines[] = ':20:' . $document->getTransactionReference();

        // Value Date/Currency/Amount (:32A:)
        $lines[] = ':32A:' . $document->toField32A();

        // Sender's Correspondent (:53a:) - optional
        $this->appendOptionalParty(
            $lines,
            $document->getSendersCorrespondent(),
            ':53A:',
            ':53D:'
        );

        // Intermediary (:56a:) - optional
        $this->appendOptionalParty(
            $lines,
            $document->getIntermediary(),
            ':56A:',
            ':56D:'
        );

        // Account With Institution (:57a:)
        $accountWith = $document->getAccountWithInstitution();
        if ($accountWith->isBicOnly()) {
            $lines[] = $this->formatPartyOptionA(':57A:', $accountWith);
        } else {
            $lines[] = $this->formatPartyOptionD(':57D:', $accountWith);
        }

        // Sender to Receiver Information (:72:) - optional
        if ($document->getSenderToReceiverInfo() !== null) {
            $lines[] = ':72:' . $document->getSenderToReceiverInfo();
        }

        return $this->joinLines($lines);
    }
}
