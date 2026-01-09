<?php

/*
 * Created on   : Tue Dec 31 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Document.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type57;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt057Generator;
use CommonToolkit\FinancialFormats\Traits\XmlDocumentExportTrait;
use DateTimeImmutable;

/**
 * CAMT.057 Document (Notification to Receive).
 *
 * Represents a notification about an expected incoming payment
 * according to ISO 20022 camt.057.001.xx Standard.
 *
 * Used by a bank to inform a customer or another financial institution
 * about an expected payment receipt zu informieren.
 *
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type57
 */
class Document implements CamtDocumentInterface {
    use XmlDocumentExportTrait;

    protected string $groupHeaderMessageId;
    protected DateTimeImmutable $creationDateTime;
    protected ?string $initiatingPartyName = null;
    protected ?string $messageRecipientBic = null;

    /** @var NotificationItem[] */
    protected array $items = [];

    public function __construct(
        string $groupHeaderMessageId,
        DateTimeImmutable|string $creationDateTime,
        ?string $initiatingPartyName = null,
        ?string $messageRecipientBic = null,
        array $items = []
    ) {
        $this->groupHeaderMessageId = $groupHeaderMessageId;
        $this->creationDateTime = $creationDateTime instanceof DateTimeImmutable
            ? $creationDateTime
            : new DateTimeImmutable($creationDateTime);
        $this->initiatingPartyName = $initiatingPartyName;
        $this->messageRecipientBic = $messageRecipientBic;
        $this->items = $items;
    }

    public function getCamtType(): CamtType {
        return CamtType::CAMT057;
    }

    public function getGroupHeaderMessageId(): string {
        return $this->groupHeaderMessageId;
    }

    public function getCreationDateTime(): DateTimeImmutable {
        return $this->creationDateTime;
    }

    public function getInitiatingPartyName(): ?string {
        return $this->initiatingPartyName;
    }

    public function getMessageRecipientBic(): ?string {
        return $this->messageRecipientBic;
    }

    /**
     * @return NotificationItem[]
     */
    public function getItems(): array {
        return $this->items;
    }

    public function addItem(NotificationItem $item): self {
        $this->items[] = $item;
        return $this;
    }

    protected function getDefaultXml(): string {
        return $this->toXml();
    }

    public function toXml(CamtVersion $version = CamtVersion::V08): string {
        return (new Camt057Generator())->generate($this, $version);
    }
}
