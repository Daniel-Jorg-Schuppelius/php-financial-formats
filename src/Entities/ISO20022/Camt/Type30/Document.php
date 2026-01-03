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

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type30;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use CommonToolkit\FinancialFormats\Traits\XmlDocumentExportTrait;
use DateTimeImmutable;
use DOMDocument;

/**
 * CAMT.030 Document (Notification of Case Assignment).
 *
 * Represents a notification about a case assignment
 * according to ISO 20022 camt.030.001.xx Standard.
 *
 * Used to inform a participant that
 * ihm ein Untersuchungsfall zugewiesen wurde.
 *
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type30
 */
class Document implements CamtDocumentInterface {
    use XmlDocumentExportTrait;

    protected string $headerMessageId;
    protected DateTimeImmutable $creationDateTime;
    protected ?string $assignerAgentBic = null;
    protected ?string $assignerPartyName = null;
    protected ?string $assigneeAgentBic = null;
    protected ?string $assigneePartyName = null;
    protected ?string $caseId = null;
    protected ?string $caseCreator = null;
    protected ?string $notificationJustification = null;

    public function __construct(
        string $headerMessageId,
        DateTimeImmutable|string $creationDateTime,
        ?string $assignerAgentBic = null,
        ?string $assignerPartyName = null,
        ?string $assigneeAgentBic = null,
        ?string $assigneePartyName = null,
        ?string $caseId = null,
        ?string $caseCreator = null,
        ?string $notificationJustification = null
    ) {
        $this->headerMessageId = $headerMessageId;
        $this->creationDateTime = $creationDateTime instanceof DateTimeImmutable
            ? $creationDateTime
            : new DateTimeImmutable($creationDateTime);
        $this->assignerAgentBic = $assignerAgentBic;
        $this->assignerPartyName = $assignerPartyName;
        $this->assigneeAgentBic = $assigneeAgentBic;
        $this->assigneePartyName = $assigneePartyName;
        $this->caseId = $caseId;
        $this->caseCreator = $caseCreator;
        $this->notificationJustification = $notificationJustification;
    }

    public function getCamtType(): CamtType {
        return CamtType::CAMT030;
    }

    public function getHeaderMessageId(): string {
        return $this->headerMessageId;
    }

    public function getCreationDateTime(): DateTimeImmutable {
        return $this->creationDateTime;
    }

    public function getAssignerAgentBic(): ?string {
        return $this->assignerAgentBic;
    }

    public function getAssignerPartyName(): ?string {
        return $this->assignerPartyName;
    }

    public function getAssigneeAgentBic(): ?string {
        return $this->assigneeAgentBic;
    }

    public function getAssigneePartyName(): ?string {
        return $this->assigneePartyName;
    }

    public function getCaseId(): ?string {
        return $this->caseId;
    }

    public function getCaseCreator(): ?string {
        return $this->caseCreator;
    }

    public function getNotificationJustification(): ?string {
        return $this->notificationJustification;
    }

    protected function getDefaultXml(): string {
        return $this->toXml();
    }

    public function toXml(CamtVersion $version = CamtVersion::V06): string {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $namespace = $version->getNamespace($this->getCamtType());
        $root = $dom->createElementNS($namespace, 'Document');
        $dom->appendChild($root);

        $ntfctn = $dom->createElement('NtfctnOfCaseAssgnmt');
        $root->appendChild($ntfctn);

        // Header
        $hdr = $dom->createElement('Hdr');
        $ntfctn->appendChild($hdr);
        $hdr->appendChild($dom->createElement('Id', htmlspecialchars($this->headerMessageId)));
        $hdr->appendChild($dom->createElement('CreDtTm', $this->creationDateTime->format('Y-m-d\TH:i:s.vP')));

        // Case
        if ($this->caseId !== null) {
            $case = $dom->createElement('Case');
            $ntfctn->appendChild($case);
            $case->appendChild($dom->createElement('Id', htmlspecialchars($this->caseId)));

            if ($this->caseCreator !== null) {
                $cretr = $dom->createElement('Cretr');
                $case->appendChild($cretr);
                $pty = $dom->createElement('Pty');
                $cretr->appendChild($pty);
                $pty->appendChild($dom->createElement('Nm', htmlspecialchars($this->caseCreator)));
            }
        }

        // Assgnmt
        $assgnmt = $dom->createElement('Assgnmt');
        $ntfctn->appendChild($assgnmt);

        // Assigner
        if ($this->assignerAgentBic !== null || $this->assignerPartyName !== null) {
            $assgnr = $dom->createElement('Assgnr');
            $assgnmt->appendChild($assgnr);

            if ($this->assignerAgentBic !== null) {
                $agt = $dom->createElement('Agt');
                $assgnr->appendChild($agt);
                $finInstnId = $dom->createElement('FinInstnId');
                $agt->appendChild($finInstnId);
                $finInstnId->appendChild($dom->createElement('BICFI', htmlspecialchars($this->assignerAgentBic)));
            } elseif ($this->assignerPartyName !== null) {
                $pty = $dom->createElement('Pty');
                $assgnr->appendChild($pty);
                $pty->appendChild($dom->createElement('Nm', htmlspecialchars($this->assignerPartyName)));
            }
        }

        // Assignee
        if ($this->assigneeAgentBic !== null || $this->assigneePartyName !== null) {
            $assgne = $dom->createElement('Assgne');
            $assgnmt->appendChild($assgne);

            if ($this->assigneeAgentBic !== null) {
                $agt = $dom->createElement('Agt');
                $assgne->appendChild($agt);
                $finInstnId = $dom->createElement('FinInstnId');
                $agt->appendChild($finInstnId);
                $finInstnId->appendChild($dom->createElement('BICFI', htmlspecialchars($this->assigneeAgentBic)));
            } elseif ($this->assigneePartyName !== null) {
                $pty = $dom->createElement('Pty');
                $assgne->appendChild($pty);
                $pty->appendChild($dom->createElement('Nm', htmlspecialchars($this->assigneePartyName)));
            }
        }

        // Justification
        if ($this->notificationJustification !== null) {
            $justfn = $dom->createElement('Justfn');
            $ntfctn->appendChild($justfn);
            $justfn->appendChild($dom->createElement('Rsn', htmlspecialchars($this->notificationJustification)));
        }

        return $dom->saveXML() ?: '';
    }
}
