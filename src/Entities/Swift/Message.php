<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Message.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Swift;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt1\MtDocumentAbstract as Mt1DocumentAbstract;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\MtDocumentAbstract as Mt9DocumentAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type101\Document as Mt101Document;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use CommonToolkit\FinancialFormats\Generators\Swift\SwiftMessageGenerator;
use CommonToolkit\FinancialFormats\Parsers\Mt10xParser;
use CommonToolkit\FinancialFormats\Parsers\Mt940DocumentParser;
use RuntimeException;

/**
 * SWIFT FIN Message - Complete SWIFT message with all 5 blocks
 * 
 * Format:
 * {1:F01BANKBEBB2222123456}           - Basic Header
 * {2:O9401200250101BANKDEFF...}       - Application Header
 * {3:{108:MUR12345}}                   - User Header (optional)
 * {4:                                  - Text Block (MT-Daten)
 * :20:REFERENCE
 * :25:ACCOUNTID
 * ...
 * -}
 * {5:{CHK:123456789ABC}}               - Trailer
 */
final class Message {
    public function __construct(
        private readonly BasicHeader $basicHeader,
        private readonly ApplicationHeader $applicationHeader,
        private readonly string $textBlock,
        private readonly ?UserHeader $userHeader = null,
        private readonly ?Trailer $trailer = null
    ) {
    }

    public function getBasicHeader(): BasicHeader {
        return $this->basicHeader;
    }

    public function getApplicationHeader(): ApplicationHeader {
        return $this->applicationHeader;
    }

    public function getUserHeader(): ?UserHeader {
        return $this->userHeader;
    }

    /**
     * Returns the Text Block (Block 4) - the actual MT data
     */
    public function getTextBlock(): string {
        return $this->textBlock;
    }

    public function getTrailer(): ?Trailer {
        return $this->trailer;
    }

    /**
     * Returns the Message Type
     */
    public function getMessageType(): MtType {
        return $this->applicationHeader->getMessageType();
    }

    /**
     * Returns the sender's BIC
     */
    public function getSenderBic(): string {
        return $this->basicHeader->getBic();
    }

    /**
     * Returns the receiver's BIC (only for input messages)
     */
    public function getReceiverBic(): ?string {
        return $this->applicationHeader->getReceiverBic();
    }

    /**
     * Checks if the message is an output message
     */
    public function isOutput(): bool {
        return $this->applicationHeader->isOutput();
    }

    /**
     * Checks if the message is an input message
     */
    public function isInput(): bool {
        return $this->applicationHeader->isInput();
    }

    /**
     * Checks if the message is a training message
     */
    public function isTraining(): bool {
        return $this->trailer?->isTraining() ?? false;
    }

    /**
     * Returns the checksum
     */
    public function getChecksum(): ?string {
        return $this->trailer?->getChecksum();
    }

    /**
     * Returns the Message User Reference (MUR)
     */
    public function getMur(): ?string {
        return $this->userHeader?->getMur();
    }

    /**
     * Returns the UETR (for gpi tracking)
     */
    public function getUetr(): ?string {
        return $this->userHeader?->getUetr();
    }

    /**
     * Checks if STP is enabled
     */
    public function isStp(): bool {
        return $this->userHeader?->isStp() ?? false;
    }

    /**
     * Parst den Text-Block automatisch basierend auf dem MT-Typ.
     * 
     * Supported types:
     * - MT101 → Mt101Document (batch transfer)
     * - MT103 → Mt1DocumentAbstract (single transfer)
     * - MT940, MT941, MT942 → Mt9DocumentAbstract (account statements)
     * 
     * @return Mt101Document|Mt1DocumentAbstract|Mt9DocumentAbstract Das geparste Dokument
     * @throws RuntimeException If the MT type is not supported
     */
    public function parseDocument(): Mt101Document|Mt1DocumentAbstract|Mt9DocumentAbstract {
        return match ($this->getMessageType()) {
            MtType::MT101 => Mt10xParser::parseMt101($this->textBlock),
            MtType::MT103 => Mt10xParser::parseMt103($this->textBlock),
            MtType::MT940, MtType::MT941, MtType::MT942 => Mt940DocumentParser::parse($this->textBlock),
            default => throw new RuntimeException(
                "Nicht unterstützter MT-Typ für automatisches Parsing: {$this->getMessageType()->value}"
            ),
        };
    }

    /**
     * Checks if the MT type is a payment order (MT10x).
     */
    public function isPaymentOrder(): bool {
        return in_array($this->getMessageType(), [MtType::MT101, MtType::MT103], true);
    }

    /**
     * Checks if the MT type is an account statement (MT9xx).
     */
    public function isStatement(): bool {
        return in_array($this->getMessageType(), [MtType::MT940, MtType::MT941, MtType::MT942], true);
    }

    /**
     * Returns the complete SWIFT message as string
     */
    public function __toString(): string {
        return (new SwiftMessageGenerator())->generate($this);
    }
}
