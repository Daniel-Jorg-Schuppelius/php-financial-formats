<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt057DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type57\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type57\NotificationItem;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder für CAMT.057 Documents (Notification to Receive).
 * 
 * Erstellt Benachrichtigungen über erwartete Zahlungseingänge.
 * 
 * Verwendung:
 * ```php
 * $document = Camt057DocumentBuilder::create('MSG-001')
 *     ->withInitiatingParty('Bank AG')
 *     ->withMessageRecipient('COBADEFFXXX')
 *     ->addItem(new NotificationItem(
 *         id: 'ITEM-001',
 *         expectedValueDate: new DateTimeImmutable('2024-01-15'),
 *         amount: '1000.00',
 *         currency: CurrencyCode::Euro,
 *         debtorName: 'Schuldner GmbH'
 *     ))
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Camt
 */
final class Camt057DocumentBuilder {
    private string $messageId;
    private DateTimeImmutable $creationDateTime;
    private ?string $initiatingPartyName = null;
    private ?string $messageRecipientBic = null;

    /** @var NotificationItem[] */
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
     * Fügt ein Notification Item hinzu.
     */
    public function addItem(NotificationItem $item): self {
        $clone = clone $this;
        $clone->items[] = $item;
        return $clone;
    }

    /**
     * Convenience: Fügt ein einfaches Notification Item hinzu.
     */
    public function addSimpleItem(
        string $id,
        DateTimeImmutable $expectedValueDate,
        string $amount,
        CurrencyCode $currency,
        string $debtorName,
        ?string $debtorIban = null,
        ?string $debtorBic = null,
        ?string $remittanceInfo = null
    ): self {
        return $this->addItem(new NotificationItem(
            id: $id,
            expectedValueDate: $expectedValueDate,
            amount: $amount,
            currency: $currency,
            debtorName: $debtorName,
            debtorAccountIban: $debtorIban,
            debtorAgentBic: $debtorBic,
            remittanceInformation: $remittanceInfo
        ));
    }

    /**
     * Fügt mehrere Notification Items hinzu.
     * 
     * @param NotificationItem[] $items
     */
    public function addItems(array $items): self {
        $clone = clone $this;
        $clone->items = array_merge($clone->items, $items);
        return $clone;
    }

    /**
     * Erstellt das CAMT.057 Document.
     * 
     * @throws InvalidArgumentException wenn Pflichtfelder fehlen
     */
    public function build(): Document {
        if (empty($this->items)) {
            throw new InvalidArgumentException('Mindestens ein Notification Item erforderlich');
        }

        return new Document(
            groupHeaderMessageId: $this->messageId,
            creationDateTime: $this->creationDateTime,
            initiatingPartyName: $this->initiatingPartyName,
            messageRecipientBic: $this->messageRecipientBic,
            items: $this->items
        );
    }

    // === Static Factory Methods ===

    /**
     * Erstellt eine einfache Zahlungsbenachrichtigung.
     */
    public static function createSingleNotification(
        string $messageId,
        string $initiatorName,
        string $itemId,
        DateTimeImmutable $expectedValueDate,
        string $amount,
        CurrencyCode $currency,
        string $debtorName
    ): Document {
        return self::create($messageId)
            ->withInitiatingParty($initiatorName)
            ->addSimpleItem(
                id: $itemId,
                expectedValueDate: $expectedValueDate,
                amount: $amount,
                currency: $currency,
                debtorName: $debtorName
            )
            ->build();
    }
}
