<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt920Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\Mt;

use CommonToolkit\FinancialFormats\Entities\Mt9\Type920\Document;
use InvalidArgumentException;

/**
 * Generator for MT920 Request Message.
 * 
 * Generates SWIFT MT920 messages from Document objects.
 * 
 * @package CommonToolkit\Generators\Common\Banking\Mt
 */
class Mt920Generator {
    protected const LINE_SEPARATOR = "\r\n";

    /**
     * Generates the MT920 SWIFT message.
     * 
     * @param object $document The MT920 document
     * @return string The formatted SWIFT message
     */
    public function generate(object $document): string {
        if (!$document instanceof Document) {
            throw new InvalidArgumentException('Expected Mt9\Type920\Document');
        }

        $lines = [];

        // Transaction Reference (:20:)
        $lines[] = ':20:' . $document->getTransactionReference();

        // Requested Message Type (:12:)
        $lines[] = ':12:' . $document->getRequestedMessageType();

        // Account Identification (:25:)
        $lines[] = ':25:' . $document->getAccountId();

        // Floor Limit Indicator (:34F:) - optional
        $field34F = $document->toField34F();
        if ($field34F !== null) {
            $lines[] = ':34F:' . $field34F;
        }

        // End marker
        $lines[] = '-';

        return implode(self::LINE_SEPARATOR, $lines);
    }
}
