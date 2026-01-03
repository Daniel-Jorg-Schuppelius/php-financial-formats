<?php
/*
 * Created on   : Mon Jan 06 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : SwiftMessageGenerator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\Swift;

use CommonToolkit\FinancialFormats\Entities\Swift\Message;

/**
 * Generator for SWIFT FIN messages.
 * 
 * Generates complete SWIFT messages with all 5 blocks:
 * - Block 1: Basic Header
 * - Block 2: Application Header
 * - Block 3: User Header (optional)
 * - Block 4: Text Block (MT-Daten)
 * - Block 5: Trailer (optional)
 * 
 * @package CommonToolkit\FinancialFormats\Generators\Swift
 */
class SwiftMessageGenerator {
    /**
     * Generates the SWIFT message as a string.
     * 
     * @param Message $message Die SWIFT-Nachricht
     * @return string Die formatierte SWIFT-Nachricht
     */
    public function generate(Message $message): string {
        $result = $this->generateBasicHeader($message);
        $result .= $this->generateApplicationHeader($message);
        $result .= $this->generateUserHeader($message);
        $result .= $this->generateTextBlock($message);
        $result .= $this->generateTrailer($message);

        return $result;
    }

    /**
     * Generates Block 1: Basic Header
     */
    protected function generateBasicHeader(Message $message): string {
        return (string) $message->getBasicHeader();
    }

    /**
     * Generates Block 2: Application Header
     */
    protected function generateApplicationHeader(Message $message): string {
        return (string) $message->getApplicationHeader();
    }

    /**
     * Generates Block 3: User Header (optional)
     */
    protected function generateUserHeader(Message $message): string {
        $userHeader = $message->getUserHeader();
        if ($userHeader === null) {
            return '';
        }

        $userHeaderStr = (string) $userHeader;
        return $userHeaderStr !== '' ? $userHeaderStr : '';
    }

    /**
     * Generates Block 4: Text Block (MT data)
     */
    protected function generateTextBlock(Message $message): string {
        return "{4:\n" . $message->getTextBlock() . "\n-}";
    }

    /**
     * Generates Block 5: Trailer (optional)
     */
    protected function generateTrailer(Message $message): string {
        $trailer = $message->getTrailer();
        if ($trailer === null) {
            return '';
        }

        $trailerStr = (string) $trailer;
        return $trailerStr !== '' ? $trailerStr : '';
    }
}
