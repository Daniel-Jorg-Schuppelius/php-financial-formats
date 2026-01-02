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

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type58;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use CommonToolkit\FinancialFormats\Traits\XmlDocumentExportTrait;
use DateTimeImmutable;
use DOMDocument;

/**
 * CAMT.058 Document (Notification to Receive Cancellation Advice).
 *
 * Repräsentiert einen Stornierungshinweis zu einer Benachrichtigung
 * über einen erwarteten Zahlungseingang gemäß ISO 20022 camt.058.001.xx Standard.
 *
 * Wird verwendet, um eine zuvor gesendete CAMT.057-Benachrichtigung zu stornieren.
 *
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type58
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

    /** @var CancellationItem[] */
    protected array $items = [];

    public function __construct(
        string $groupHeaderMessageId,
        DateTimeImmutable|string $creationDateTime,
        ?string $initiatingPartyName = null,
        ?string $messageRecipientBic = null,
        ?string $originalMessageId = null,
        ?string $originalMessageNameId = null,
        DateTimeImmutable|string|null $originalCreationDateTime = null,
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
        $this->items = $items;
    }

    public function getCamtType(): CamtType {
        return CamtType::CAMT058;
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

    /**
     * @return CancellationItem[]
     */
    public function getItems(): array {
        return $this->items;
    }

    public function addItem(CancellationItem $item): self {
        $this->items[] = $item;
        return $this;
    }

    protected function getDefaultXml(): string {
        return $this->toXml();
    }

    public function toXml(CamtVersion $version = CamtVersion::V09): string {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $namespace = $version->getNamespace($this->getCamtType());
        $root = $dom->createElementNS($namespace, 'Document');
        $dom->appendChild($root);

        $ntfctnToRcvCxlAdvc = $dom->createElement('NtfctnToRcvCxlAdvc');
        $root->appendChild($ntfctnToRcvCxlAdvc);

        // GrpHdr (Group Header)
        $grpHdr = $dom->createElement('GrpHdr');
        $ntfctnToRcvCxlAdvc->appendChild($grpHdr);

        $grpHdr->appendChild($dom->createElement('MsgId', htmlspecialchars($this->groupHeaderMessageId)));
        $grpHdr->appendChild($dom->createElement('CreDtTm', $this->creationDateTime->format('Y-m-d\TH:i:s.vP')));

        if ($this->initiatingPartyName !== null) {
            $initgPty = $dom->createElement('InitgPty');
            $grpHdr->appendChild($initgPty);
            $initgPty->appendChild($dom->createElement('Nm', htmlspecialchars($this->initiatingPartyName)));
        }

        if ($this->messageRecipientBic !== null) {
            $msgRcpt = $dom->createElement('MsgRcpt');
            $grpHdr->appendChild($msgRcpt);
            $finInstnId = $dom->createElement('FinInstnId');
            $msgRcpt->appendChild($finInstnId);
            $finInstnId->appendChild($dom->createElement('BICFI', htmlspecialchars($this->messageRecipientBic)));
        }

        // OrgnlNtfctn (Original Notification Reference)
        $orgnlNtfctn = $dom->createElement('OrgnlNtfctn');
        $ntfctnToRcvCxlAdvc->appendChild($orgnlNtfctn);

        if ($this->originalMessageId !== null) {
            $orgnlNtfctn->appendChild($dom->createElement('OrgnlMsgId', htmlspecialchars($this->originalMessageId)));
        }
        if ($this->originalMessageNameId !== null) {
            $orgnlNtfctn->appendChild($dom->createElement('OrgnlMsgNmId', htmlspecialchars($this->originalMessageNameId)));
        }
        if ($this->originalCreationDateTime !== null) {
            $orgnlNtfctn->appendChild($dom->createElement('OrgnlCreDtTm', $this->originalCreationDateTime->format('Y-m-d\TH:i:s.vP')));
        }

        // Cxl Items (Cancellation Items)
        foreach ($this->items as $item) {
            $orgnlItm = $dom->createElement('OrgnlItm');
            $orgnlNtfctn->appendChild($orgnlItm);

            $orgnlItm->appendChild($dom->createElement('OrgnlItmId', htmlspecialchars($item->getOriginalItemId())));

            if ($item->getCancellationReasonCode() !== null) {
                $cxlRsnInf = $dom->createElement('CxlRsnInf');
                $orgnlItm->appendChild($cxlRsnInf);
                $rsn = $dom->createElement('Rsn');
                $cxlRsnInf->appendChild($rsn);
                $rsn->appendChild($dom->createElement('Cd', htmlspecialchars($item->getCancellationReasonCode())));

                if ($item->getCancellationAdditionalInfo() !== null) {
                    $cxlRsnInf->appendChild($dom->createElement('AddtlInf', htmlspecialchars($item->getCancellationAdditionalInfo())));
                }
            }
        }

        return $dom->saveXML() ?: '';
    }
}
