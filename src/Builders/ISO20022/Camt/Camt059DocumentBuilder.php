<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt059DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type59\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type59\StatusItem;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder for CAMT.059 Documents (Notification to Receive Status Report).
 * 
 * Creates status reports for notifications about expected payment receipts.
 * Meldet den Status einer zuvor gesendeten CAMT.057-Benachrichtigung.
 * 
 * Verwendung:
 * ```php
 * $document = Camt059DocumentBuilder::forCamt057('MSG-002', 'MSG-001')
 *     ->withInitiatingParty('Bank AG')
 *     ->withGroupStatus('ACCP')
 *     ->addStatusItem(new StatusItem(
 *         originalItemId: 'ITEM-001',
 *         itemStatus: 'ACCP'
 *     ))
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Camt
 */
final class Camt059DocumentBuilder {
    private string $messageId;
    private DateTimeImmutable $creationDateTime;
    private ?string $initiatingPartyName = null;
    private ?string $messageRecipientBic = null;
    private ?string $originalMessageId = null;
    private ?string $originalMessageNameId = null;
    private ?DateTimeImmutable $originalCreationDateTime = null;
    private ?string $originalGroupStatusCode = null;

    /** @var StatusItem[] */
    private array $items = [];

    private function __construct(string $messageId) {
        if (strlen($messageId) > 35) {
            throw new InvalidArgumentException('MsgId must not exceed 35 characters');
        }
        $this->messageId = $messageId;
        $this->creationDateTime = new DateTimeImmutable();
    }

    /**
     * Creates a new builder with message ID.
     */
    public static function create(string $messageId): self {
        return new self($messageId);
    }

    /**
     * Creates builder for status of a CAMT.057 message.
     */
    public static function forCamt057(string $messageId, string $originalCamt057MessageId): self {
        $builder = new self($messageId);
        $builder->originalMessageId = $originalCamt057MessageId;
        $builder->originalMessageNameId = 'camt.057.001.08';
        return $builder;
    }

    /**
     * Sets the creation timestamp (default: now).
     */
    public function withCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    /**
     * Sets the initiating party (bank).
     */
    public function withInitiatingParty(string $name): self {
        $clone = clone $this;
        $clone->initiatingPartyName = $name;
        return $clone;
    }

    /**
     * Sets the receiver (BIC).
     */
    public function withMessageRecipient(string $bic): self {
        $clone = clone $this;
        $clone->messageRecipientBic = $bic;
        return $clone;
    }

    /**
     * Sets the reference to the original message.
     */
    public function withOriginalMessage(
        string $messageId,
        ?string $messageNameId = null,
        ?DateTimeImmutable $creationDateTime = null
    ): self {
        $clone = clone $this;
        $clone->originalMessageId = $messageId;
        $clone->originalMessageNameId = $messageNameId ?? 'camt.057.001.08';
        $clone->originalCreationDateTime = $creationDateTime;
        return $clone;
    }

    /**
     * Sets the group status code.
     * 
     * Common status codes:
     * - ACCP: Accepted (Akzeptiert)
     * - RJCT: Rejected (Abgelehnt)
     * - PDNG: Pending (In Bearbeitung)
     * - RCVD: Received (Empfangen)
     */
    public function withGroupStatus(string $statusCode): self {
        $clone = clone $this;
        $clone->originalGroupStatusCode = $statusCode;
        return $clone;
    }

    /**
     * Adds a status item.
     */
    public function addStatusItem(StatusItem $item): self {
        $clone = clone $this;
        $clone->items[] = $item;
        return $clone;
    }

    /**
     * Convenience: Adds an accepted item.
     */
    public function addAccepted(string $originalItemId): self {
        return $this->addStatusItem(new StatusItem(
            originalItemId: $originalItemId,
            itemStatus: 'ACCP'
        ));
    }

    /**
     * Convenience: Adds a rejected item.
     */
    public function addRejected(
        string $originalItemId,
        ?string $reasonCode = null,
        ?string $additionalInfo = null
    ): self {
        return $this->addStatusItem(new StatusItem(
            originalItemId: $originalItemId,
            itemStatus: 'RJCT',
            reasonCode: $reasonCode,
            additionalInformation: $additionalInfo
        ));
    }

    /**
     * Convenience: Adds a pending item.
     */
    public function addPending(string $originalItemId): self {
        return $this->addStatusItem(new StatusItem(
            originalItemId: $originalItemId,
            itemStatus: 'PDNG'
        ));
    }

    /**
     * Adds multiple status items.
     * 
     * @param StatusItem[] $items
     */
    public function addStatusItems(array $items): self {
        $clone = clone $this;
        $clone->items = array_merge($clone->items, $items);
        return $clone;
    }

    /**
     * Creates the CAMT.059 Document.
     * 
     * @throws InvalidArgumentException wenn Pflichtfelder fehlen
     */
    public function build(): Document {
        if (empty($this->items)) {
            throw new InvalidArgumentException('Mindestens ein Status Item erforderlich');
        }

        if ($this->originalMessageId === null) {
            throw new InvalidArgumentException('Original Message-ID muss angegeben werden');
        }

        return new Document(
            groupHeaderMessageId: $this->messageId,
            creationDateTime: $this->creationDateTime,
            initiatingPartyName: $this->initiatingPartyName,
            messageRecipientBic: $this->messageRecipientBic,
            originalMessageId: $this->originalMessageId,
            originalMessageNameId: $this->originalMessageNameId,
            originalCreationDateTime: $this->originalCreationDateTime,
            originalGroupStatusCode: $this->originalGroupStatusCode,
            items: $this->items
        );
    }

    // === Static Factory Methods ===

    /**
     * Creates einen einfachen Akzeptanz-Statusbericht.
     */
    public static function createAllAccepted(
        string $messageId,
        string $originalMessageId,
        array $itemIds
    ): Document {
        $builder = self::forCamt057($messageId, $originalMessageId)
            ->withGroupStatus('ACCP');

        foreach ($itemIds as $itemId) {
            $builder = $builder->addAccepted($itemId);
        }

        return $builder->build();
    }

    /**
     * Creates einen Ablehnungs-Statusbericht.
     */
    public static function createAllRejected(
        string $messageId,
        string $originalMessageId,
        array $itemIds,
        ?string $reasonCode = null
    ): Document {
        $builder = self::forCamt057($messageId, $originalMessageId)
            ->withGroupStatus('RJCT');

        foreach ($itemIds as $itemId) {
            $builder = $builder->addRejected($itemId, $reasonCode);
        }

        return $builder->build();
    }
}
