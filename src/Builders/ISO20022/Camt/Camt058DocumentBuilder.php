<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt058DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type58\CancellationItem;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type58\Document;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder für CAMT.058 Documents (Notification to Receive Cancellation Advice).
 * 
 * Erstellt Stornierungshinweise zu Benachrichtigungen über erwartete Zahlungseingänge.
 * Storniert eine zuvor gesendete CAMT.057-Benachrichtigung.
 * 
 * Verwendung:
 * ```php
 * $document = Camt058DocumentBuilder::forCamt057('MSG-002', 'MSG-001')
 *     ->withInitiatingParty('Bank AG')
 *     ->addCancellationItem(new CancellationItem(
 *         originalItemId: 'ITEM-001',
 *         cancellationReasonCode: 'CUST'
 *     ))
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Camt
 */
final class Camt058DocumentBuilder {
    private string $messageId;
    private DateTimeImmutable $creationDateTime;
    private ?string $initiatingPartyName = null;
    private ?string $messageRecipientBic = null;
    private ?string $originalMessageId = null;
    private ?string $originalMessageNameId = null;
    private ?DateTimeImmutable $originalCreationDateTime = null;

    /** @var CancellationItem[] */
    private array $items = [];

    private function __construct(string $messageId) {
        if (strlen($messageId) > 35) {
            throw new InvalidArgumentException('MsgId darf maximal 35 Zeichen lang sein');
        }
        $this->messageId = $messageId;
        $this->creationDateTime = new DateTimeImmutable();
    }

    /**
     * Erzeugt neuen Builder mit Message-ID.
     */
    public static function create(string $messageId): self {
        return new self($messageId);
    }

    /**
     * Erzeugt Builder für Stornierung einer CAMT.057-Nachricht.
     */
    public static function forCamt057(string $messageId, string $originalCamt057MessageId): self {
        $builder = new self($messageId);
        $builder->originalMessageId = $originalCamt057MessageId;
        $builder->originalMessageNameId = 'camt.057.001.08';
        return $builder;
    }

    /**
     * Setzt den Erstellungszeitpunkt (Standard: jetzt).
     */
    public function withCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    /**
     * Setzt die initiierende Partei (Bank).
     */
    public function withInitiatingParty(string $name): self {
        $clone = clone $this;
        $clone->initiatingPartyName = $name;
        return $clone;
    }

    /**
     * Setzt den Empfänger (BIC).
     */
    public function withMessageRecipient(string $bic): self {
        $clone = clone $this;
        $clone->messageRecipientBic = $bic;
        return $clone;
    }

    /**
     * Setzt die Referenz auf die Original-Nachricht.
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
     * Fügt ein Cancellation Item hinzu.
     */
    public function addCancellationItem(CancellationItem $item): self {
        $clone = clone $this;
        $clone->items[] = $item;
        return $clone;
    }

    /**
     * Convenience: Fügt ein einfaches Cancellation Item hinzu.
     */
    public function addSimpleCancellation(
        string $originalItemId,
        ?string $reasonCode = null,
        ?string $additionalInfo = null
    ): self {
        return $this->addCancellationItem(new CancellationItem(
            originalItemId: $originalItemId,
            cancellationReasonCode: $reasonCode,
            cancellationAdditionalInfo: $additionalInfo
        ));
    }

    /**
     * Fügt mehrere Cancellation Items hinzu.
     * 
     * @param CancellationItem[] $items
     */
    public function addCancellationItems(array $items): self {
        $clone = clone $this;
        $clone->items = array_merge($clone->items, $items);
        return $clone;
    }

    /**
     * Erstellt das CAMT.058 Document.
     * 
     * @throws InvalidArgumentException wenn Pflichtfelder fehlen
     */
    public function build(): Document {
        if (empty($this->items)) {
            throw new InvalidArgumentException('Mindestens ein Cancellation Item erforderlich');
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
            items: $this->items
        );
    }

    // === Static Factory Methods ===

    /**
     * Erstellt eine einfache Stornierung für eine Benachrichtigung.
     */
    public static function createSingleCancellation(
        string $messageId,
        string $originalMessageId,
        string $originalItemId,
        ?string $reasonCode = null
    ): Document {
        return self::forCamt057($messageId, $originalMessageId)
            ->addSimpleCancellation($originalItemId, $reasonCode)
            ->build();
    }

    /**
     * Storniert alle Items einer CAMT.057 Nachricht.
     * 
     * @param string[] $itemIds
     */
    public static function cancelAll(
        string $messageId,
        string $originalMessageId,
        array $itemIds,
        ?string $reasonCode = null
    ): Document {
        $builder = self::forCamt057($messageId, $originalMessageId);

        foreach ($itemIds as $itemId) {
            $builder = $builder->addSimpleCancellation($itemId, $reasonCode);
        }

        return $builder->build();
    }
}
