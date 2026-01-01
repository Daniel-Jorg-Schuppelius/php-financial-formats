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

namespace CommonToolkit\FinancialFormats\Entities\Camt\Type59;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use DateTimeImmutable;
use DOMDocument;

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

    public function toXml(CamtVersion $version = CamtVersion::V08): string {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $namespace = $version->getNamespace($this->getCamtType());
        $root = $dom->createElementNS($namespace, 'Document');
        $dom->appendChild($root);

        $ntfctnToRcvStsRpt = $dom->createElement('NtfctnToRcvStsRpt');
        $root->appendChild($ntfctnToRcvStsRpt);

        // GrpHdr (Group Header)
        $grpHdr = $dom->createElement('GrpHdr');
        $ntfctnToRcvStsRpt->appendChild($grpHdr);

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

        // OrgnlNtfctnAndSts (Original Notification and Status)
        $orgnlNtfctnAndSts = $dom->createElement('OrgnlNtfctnAndSts');
        $ntfctnToRcvStsRpt->appendChild($orgnlNtfctnAndSts);

        if ($this->originalMessageId !== null) {
            $orgnlNtfctnAndSts->appendChild($dom->createElement('OrgnlMsgId', htmlspecialchars($this->originalMessageId)));
        }
        if ($this->originalMessageNameId !== null) {
            $orgnlNtfctnAndSts->appendChild($dom->createElement('OrgnlMsgNmId', htmlspecialchars($this->originalMessageNameId)));
        }
        if ($this->originalCreationDateTime !== null) {
            $orgnlNtfctnAndSts->appendChild($dom->createElement('OrgnlCreDtTm', $this->originalCreationDateTime->format('Y-m-d\TH:i:s.vP')));
        }
        if ($this->originalGroupStatusCode !== null) {
            $orgnlNtfctnAndSts->appendChild($dom->createElement('OrgnlNtfctnSts', htmlspecialchars($this->originalGroupStatusCode)));
        }

        // Status Items
        foreach ($this->items as $item) {
            $orgnlItmAndSts = $dom->createElement('OrgnlItmAndSts');
            $orgnlNtfctnAndSts->appendChild($orgnlItmAndSts);

            $orgnlItmAndSts->appendChild($dom->createElement('OrgnlItmId', htmlspecialchars($item->getOriginalItemId())));

            if ($item->getItemStatus() !== null) {
                $orgnlItmAndSts->appendChild($dom->createElement('ItmSts', htmlspecialchars($item->getItemStatus())));
            }

            if ($item->getReasonCode() !== null || $item->getReasonProprietary() !== null) {
                $stsRsnInf = $dom->createElement('StsRsnInf');
                $orgnlItmAndSts->appendChild($stsRsnInf);

                $rsn = $dom->createElement('Rsn');
                $stsRsnInf->appendChild($rsn);

                if ($item->getReasonCode() !== null) {
                    $rsn->appendChild($dom->createElement('Cd', htmlspecialchars($item->getReasonCode())));
                } elseif ($item->getReasonProprietary() !== null) {
                    $rsn->appendChild($dom->createElement('Prtry', htmlspecialchars($item->getReasonProprietary())));
                }

                if ($item->getAdditionalInformation() !== null) {
                    $stsRsnInf->appendChild($dom->createElement('AddtlInf', htmlspecialchars($item->getAdditionalInformation())));
                }
            }
        }

        return $dom->saveXML() ?: '';
    }
}
