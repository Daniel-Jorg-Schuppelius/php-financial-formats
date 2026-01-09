<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt910Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\Mt;

use CommonToolkit\FinancialFormats\Entities\Mt9\Type910\Document;
use InvalidArgumentException;

/**
 * Generator for MT910 Confirmation of Credit.
 * 
 * Generates SWIFT MT910 messages from Document objects.
 * 
 * @package CommonToolkit\Generators\Common\Banking\Mt
 */
class Mt910Generator {
    protected const LINE_SEPARATOR = "\r\n";

    /**
     * Generates the MT910 SWIFT message.
     * 
     * @param object $document The MT910 document
     * @return string The formatted SWIFT message
     */
    public function generate(object $document): string {
        if (!$document instanceof Document) {
            throw new InvalidArgumentException('Expected Mt9\Type910\Document');
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

        // Ordering Customer (:50a:) - optional
        $orderingCustomer = $document->getOrderingCustomer();
        if ($orderingCustomer !== null) {
            if ($orderingCustomer->hasAccount()) {
                $lines[] = ':50K:' . $orderingCustomer->toOptionK();
            } elseif ($orderingCustomer->isBicOnly()) {
                $lines[] = ':50A:' . $orderingCustomer->toOptionA();
            } else {
                $lines[] = ':50K:' . $orderingCustomer->toOptionK();
            }
        }

        // Ordering Institution (:52a:) - optional
        $orderingInstitution = $document->getOrderingInstitution();
        if ($orderingInstitution !== null) {
            if ($orderingInstitution->isBicOnly()) {
                $lines[] = ':52A:' . $orderingInstitution->toOptionA();
            } else {
                $lines[] = ':52D:' . $orderingInstitution->toOptionK();
            }
        }

        // Intermediary (:56a:) - optional
        $intermediary = $document->getIntermediary();
        if ($intermediary !== null) {
            if ($intermediary->isBicOnly()) {
                $lines[] = ':56A:' . $intermediary->toOptionA();
            } else {
                $lines[] = ':56D:' . $intermediary->toOptionK();
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
