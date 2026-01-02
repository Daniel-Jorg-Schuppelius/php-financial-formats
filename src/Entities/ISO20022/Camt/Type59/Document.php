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

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type59;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt059Generator;
use CommonToolkit\FinancialFormats\Traits\XmlDocumentExportTrait;
use DateTimeImmutable;

/**
 * CAMT.059 Document (Notification to Receive Status Report).
 *
 * Repräsentiert einen Statusbericht zu einer Benachrichtigung
 * über einen erwarteten Zahlungseingang gemäß ISO 20022 camt.059.001.xx Standard.
 *
 * Wird verwendet, um den Status einer zuvor gesendeten CAMT.057-Benachrichtigung
 * zu melden (z.B. akzeptiert, abgelehnt).
 *
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type59
 */
class Document implements CamtDocumentInterface {
    use XmlDocumentExportTrait;

    protected string $groupHeaderMessageId;
    protected DateTimeImmutable $creationDateTime;
    protected ?string $initiatingPartyName = null;
    protected ?string $messageRecipientBic = null;

    // Original Reference
    protected ?string $originalMessageId = null;
    protected ?string $originalMessageNameId = null;
    protected ?DateTimeImmutable $originalCreationDateTime = null;
    protected ?string $originalGroupStatusCode = null;

    /** @var StatusItem[] */
    protected array $items = [];

    public function __construct(
        string $groupHeaderMessageId,
        DateTimeImmutable|string $creationDateTime,
        ?string $initiatingPartyName = null,
        ?string $messageRecipientBic = null,
        ?string $originalMessageId = null,
        ?string $originalMessageNameId = null,
        DateTimeImmutable|string|null $originalCreationDateTime = null,
        ?string $originalGroupStatusCode = null,
        array $items = []
    ) {
        $this->groupHeaderMessageId = $groupHeaderMessageId;
        $this->creationDateTime = $creationDateTime instanceof DateTimeImmutable
            ? $creationDateTime
            : new DateTimeImmutable($creationDateTime);
        $this->initiatingPartyName = $initiatingPartyName;
        $this->messageRecipientBic = $messageRecipientBic;
        $this->originalMessageId = $originalMessageId;
        $this->originalMessageNameId = $originalMessageNameId;
        $this->originalCreationDateTime = $originalCreationDateTime instanceof DateTimeImmutable
            ? $originalCreationDateTime
            : ($originalCreationDateTime !== null ? new DateTimeImmutable($originalCreationDateTime) : null);
        $this->originalGroupStatusCode = $originalGroupStatusCode;
        $this->items = $items;
    }

    public function getCamtType(): CamtType {
        return CamtType::CAMT059;
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

    public function getOriginalMessageId(): ?string {
        return $this->originalMessageId;
    }

    public function getOriginalMessageNameId(): ?string {
        return $this->originalMessageNameId;
    }

    public function getOriginalCreationDateTime(): ?DateTimeImmutable {
        return $this->originalCreationDateTime;
    }

    public function getOriginalGroupStatusCode(): ?string {
        return $this->originalGroupStatusCode;
    }

    /**
     * @return StatusItem[]
     */
    public function getItems(): array {
        return $this->items;
    }

    public function addItem(StatusItem $item): self {
        $this->items[] = $item;
        return $this;
    }

    protected function getDefaultXml(): string {
        return $this->toXml();
    }

    public function toXml(CamtVersion $version = CamtVersion::V08): string {
        return (new Camt059Generator())->generate($this, $version);
    }
}
