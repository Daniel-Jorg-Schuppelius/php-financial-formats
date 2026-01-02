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
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use CommonToolkit\FinancialFormats\Traits\XmlDocumentExportTrait;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use DOMDocument;

/**
 * CAMT.057 Document (Notification to Receive).
 *
 * Repräsentiert eine Benachrichtigung über einen erwarteten Zahlungseingang
 * gemäß ISO 20022 camt.057.001.xx Standard.
 *
 * Wird von einer Bank verwendet, um einen Kunden oder ein anderes Finanzinstitut
 * über einen erwarteten Zahlungseingang zu informieren.
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
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $namespace = $version->getNamespace($this->getCamtType());
        $root = $dom->createElementNS($namespace, 'Document');
        $dom->appendChild($root);

        $ntfctnToRcv = $dom->createElement('NtfctnToRcv');
        $root->appendChild($ntfctnToRcv);

        // GrpHdr (Group Header)
        $grpHdr = $dom->createElement('GrpHdr');
        $ntfctnToRcv->appendChild($grpHdr);

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

        // Ntfctn (Notification Items)
        foreach ($this->items as $item) {
            $ntfctn = $dom->createElement('Ntfctn');
            $ntfctnToRcv->appendChild($ntfctn);

            $ntfctn->appendChild($dom->createElement('Id', htmlspecialchars($item->getId())));

            if ($item->getExpectedValueDate() !== null) {
                $ntfctn->appendChild($dom->createElement('XpctdValDt', $item->getExpectedValueDate()->format('Y-m-d')));
            }

            if ($item->getAmount() !== null && $item->getCurrency() !== null) {
                $amt = $dom->createElement('Amt', htmlspecialchars($item->getAmount()));
                $amt->setAttribute('Ccy', $item->getCurrency()->value);
                $ntfctn->appendChild($amt);
            }

            if ($item->getDebtorName() !== null) {
                $dbtr = $dom->createElement('Dbtr');
                $ntfctn->appendChild($dbtr);
                $dbtr->appendChild($dom->createElement('Nm', htmlspecialchars($item->getDebtorName())));
            }

            if ($item->getDebtorAgentBic() !== null) {
                $dbtrAgt = $dom->createElement('DbtrAgt');
                $ntfctn->appendChild($dbtrAgt);
                $finInstnId = $dom->createElement('FinInstnId');
                $dbtrAgt->appendChild($finInstnId);
                $finInstnId->appendChild($dom->createElement('BICFI', htmlspecialchars($item->getDebtorAgentBic())));
            }

            if ($item->getRemittanceInformation() !== null) {
                $rmtInf = $dom->createElement('RmtInf');
                $ntfctn->appendChild($rmtInf);
                $rmtInf->appendChild($dom->createElement('Ustrd', htmlspecialchars($item->getRemittanceInformation())));
            }
        }

        return $dom->saveXML() ?: '';
    }
}
