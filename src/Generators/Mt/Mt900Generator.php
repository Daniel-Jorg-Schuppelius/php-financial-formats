<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt900Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\Mt;

use CommonToolkit\FinancialFormats\Entities\Mt9\Type900\Document;
use InvalidArgumentException;

/**
 * Generator for MT900 Confirmation of Debit.
 * 
 * Generates SWIFT MT900 messages from Document objects.
 * 
 * @package CommonToolkit\Generators\Common\Banking\Mt
 */
class Mt900Generator {
    protected const LINE_SEPARATOR = "\r\n";

    /**
     * Generates the MT900 SWIFT message.
     * 
     * @param object $document The MT900 document
     * @return string The formatted SWIFT message
     */
    public function generate(object $document): string {
        if (!$document instanceof Document) {
            throw new InvalidArgumentException('Expected Mt9\Type900\Document');
        }

        $lines = [];

        // Transaction Reference (:20:)
        $lines[] = ':20:' . $document->getTransactionReference();

        // Related Reference (:21:)
        $lines[] = ':21:' . $document->getRelatedReference();

        // Account Identification (:25:)
        $lines[] = ':25:' . $document->getAccountId();

        // Date/Time Indication (:13D:) - optional
        $field13D = $document->toField13D();
        if ($field13D !== null) {
            $lines[] = ':13D:' . $field13D;
        }

        // Value Date/Currency/Amount (:32A:)
        $lines[] = ':32A:' . $document->toField32A();

        // Ordering Institution (:52a:) - optional
        $orderingInstitution = $document->getOrderingInstitution();
        if ($orderingInstitution !== null) {
            if ($orderingInstitution->isBicOnly()) {
                $lines[] = ':52A:' . $orderingInstitution->toOptionA();
            } else {
                $lines[] = ':52D:' . $orderingInstitution->toOptionK();
            }
        }

        // Sender to Receiver Information (:72:) - optional
        if ($document->getSenderToReceiverInfo() !== null) {
            $lines[] = ':72:' . $document->getSenderToReceiverInfo();
        }

        // End marker
        $lines[] = '-';

        return implode(self::LINE_SEPARATOR, $lines);
    }
}
