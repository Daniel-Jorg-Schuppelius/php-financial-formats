<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt920DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\Mt;

use CommonToolkit\FinancialFormats\Entities\Mt9\Type920\Document;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder for MT920 Request Message.
 * 
 * Creates MT920 documents for requesting account statement messages
 * (MT940, MT941, MT942, MT950).
 * 
 * Usage:
 * ```php
 * $document = Mt920DocumentBuilder::create('REQ-001')
 *     ->account('DE89370400440532013000')
 *     ->requestMt940()
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\Builders\Common\Banking\Mt
 */
final class Mt920DocumentBuilder {
    private const VALID_MESSAGE_TYPES = ['940', '941', '942', '950'];

    private string $transactionReference;
    private ?string $accountId = null;
    private ?string $requestedMessageType = null;
    private ?string $floorLimitCurrency = null;
    private ?float $floorLimitAmount = null;
    private ?string $floorLimitIndicator = null;
    private ?DateTimeImmutable $creationDateTime = null;

    private function __construct(string $transactionReference) {
        $this->transactionReference = $transactionReference;
    }

    /**
     * Creates a new builder instance.
     * 
     * @param string $transactionReference Field :20: Transaction reference
     */
    public static function create(string $transactionReference): self {
        return new self($transactionReference);
    }

    /**
     * Sets the account identification (Field :25:).
     */
    public function account(string $accountId): self {
        $this->accountId = $accountId;
        return $this;
    }

    /**
     * Sets the requested message type (Field :12:).
     * Valid types: 940, 941, 942, 950
     * 
     * @throws InvalidArgumentException if message type is invalid
     */
    public function requestMessageType(string $type): self {
        if (!in_array($type, self::VALID_MESSAGE_TYPES, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid message type: %s. Valid types: %s', $type, implode(', ', self::VALID_MESSAGE_TYPES))
            );
        }
        $this->requestedMessageType = $type;
        return $this;
    }

    /**
     * Requests MT940 (Customer Statement Message).
     */
    public function requestMt940(): self {
        $this->requestedMessageType = '940';
        return $this;
    }

    /**
     * Requests MT941 (Balance Report).
     */
    public function requestMt941(): self {
        $this->requestedMessageType = '941';
        return $this;
    }

    /**
     * Requests MT942 (Interim Transaction Report).
     */
    public function requestMt942(): self {
        $this->requestedMessageType = '942';
        return $this;
    }

    /**
     * Requests MT950 (Statement Message).
     */
    public function requestMt950(): self {
        $this->requestedMessageType = '950';
        return $this;
    }

    /**
     * Sets the floor limit for reporting (Field :34F:).
     * Only transactions exceeding this amount will be reported.
     * 
     * @param string $currency ISO currency code
     * @param float $amount Threshold amount
     * @param string|null $indicator D for Debit only, C for Credit only, null for both
     */
    public function floorLimit(string $currency, float $amount, ?string $indicator = null): self {
        $this->floorLimitCurrency = $currency;
        $this->floorLimitAmount = $amount;
        if ($indicator !== null) {
            $indicator = strtoupper($indicator);
            if (!in_array($indicator, ['D', 'C'], true)) {
                throw new InvalidArgumentException('Floor limit indicator must be D (Debit) or C (Credit)');
            }
            $this->floorLimitIndicator = $indicator;
        }
        return $this;
    }

    /**
     * Sets the creation date/time.
     */
    public function creationDateTime(DateTimeImmutable $dateTime): self {
        $this->creationDateTime = $dateTime;
        return $this;
    }

    /**
     * Builds the MT920 document.
     * 
     * @throws InvalidArgumentException if required fields are missing
     */
    public function build(): Document {
        if ($this->accountId === null) {
            throw new InvalidArgumentException('Account ID is required');
        }
        if ($this->requestedMessageType === null) {
            throw new InvalidArgumentException('Requested message type is required (use requestMt940(), requestMt942(), etc.)');
        }

        return new Document(
            transactionReference: $this->transactionReference,
            requestedMessageType: $this->requestedMessageType,
            accountId: $this->accountId,
            floorLimitCurrency: $this->floorLimitCurrency,
            floorLimitAmount: $this->floorLimitAmount,
            floorLimitIndicator: $this->floorLimitIndicator,
            creationDateTime: $this->creationDateTime
        );
    }
}
