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

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type38;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;
use CommonToolkit\FinancialFormats\Traits\XmlDocumentExportTrait;
use DateTimeImmutable;
use DOMDocument;

/**
 * CAMT.038 Document (Case Status Report Request).
 *
 * Represents a request for the status of a case
 * according to ISO 20022 camt.038.001.xx Standard.
 *
 * Used to query the current status of an ongoing
 * Untersuchung oder eines Falls anzufordern.
 *
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type38
 */
class Document implements CamtDocumentInterface {
    use XmlDocumentExportTrait;

    protected string $requestId;
    protected DateTimeImmutable $creationDateTime;
    protected ?string $caseId = null;
    protected ?string $caseCreator = null;
    protected ?string $requesterAgentBic = null;
    protected ?string $requesterPartyName = null;
    protected ?string $responderAgentBic = null;
    protected ?string $responderPartyName = null;

    public function __construct(
        string $requestId,
        DateTimeImmutable|string $creationDateTime,
        ?string $caseId = null,
        ?string $caseCreator = null,
        ?string $requesterAgentBic = null,
        ?string $requesterPartyName = null,
        ?string $responderAgentBic = null,
        ?string $responderPartyName = null
    ) {
        $this->requestId = $requestId;
        $this->creationDateTime = $creationDateTime instanceof DateTimeImmutable
            ? $creationDateTime
            : new DateTimeImmutable($creationDateTime);
        $this->caseId = $caseId;
        $this->caseCreator = $caseCreator;
        $this->requesterAgentBic = $requesterAgentBic;
        $this->requesterPartyName = $requesterPartyName;
        $this->responderAgentBic = $responderAgentBic;
        $this->responderPartyName = $responderPartyName;
    }

    public function getCamtType(): CamtType {
        return CamtType::CAMT038;
    }

    public function getRequestId(): string {
        return $this->requestId;
    }

    public function getCreationDateTime(): DateTimeImmutable {
        return $this->creationDateTime;
    }

    public function getCaseId(): ?string {
        return $this->caseId;
    }

    public function getCaseCreator(): ?string {
        return $this->caseCreator;
    }

    public function getRequesterAgentBic(): ?string {
        return $this->requesterAgentBic;
    }

    public function getRequesterPartyName(): ?string {
        return $this->requesterPartyName;
    }

    public function getResponderAgentBic(): ?string {
        return $this->responderAgentBic;
    }

    public function getResponderPartyName(): ?string {
        return $this->responderPartyName;
    }

    protected function getDefaultXml(): string {
        return $this->toXml();
    }

    public function toXml(CamtVersion $version = CamtVersion::V05): string {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $namespace = $version->getNamespace($this->getCamtType());
        $root = $dom->createElementNS($namespace, 'Document');
        $dom->appendChild($root);

        $caseStsRptReq = $dom->createElement('CaseStsRptReq');
        $root->appendChild($caseStsRptReq);

        // ReqHdr (Request Header)
        $reqHdr = $dom->createElement('ReqHdr');
        $caseStsRptReq->appendChild($reqHdr);

        $reqHdr->appendChild($dom->createElement('Id', htmlspecialchars($this->requestId)));
        $reqHdr->appendChild($dom->createElement('CreDtTm', $this->creationDateTime->format('Y-m-d\TH:i:s.vP')));

        // Requester
        if ($this->requesterAgentBic !== null || $this->requesterPartyName !== null) {
            $reqstr = $dom->createElement('Reqstr');
            $reqHdr->appendChild($reqstr);

            if ($this->requesterAgentBic !== null) {
                $agt = $dom->createElement('Agt');
                $reqstr->appendChild($agt);
                $finInstnId = $dom->createElement('FinInstnId');
                $agt->appendChild($finInstnId);
                $finInstnId->appendChild($dom->createElement('BICFI', htmlspecialchars($this->requesterAgentBic)));
            } elseif ($this->requesterPartyName !== null) {
                $pty = $dom->createElement('Pty');
                $reqstr->appendChild($pty);
                $pty->appendChild($dom->createElement('Nm', htmlspecialchars($this->requesterPartyName)));
            }
        }

        // Responder
        if ($this->responderAgentBic !== null || $this->responderPartyName !== null) {
            $rspndr = $dom->createElement('Rspndr');
            $reqHdr->appendChild($rspndr);

            if ($this->responderAgentBic !== null) {
                $agt = $dom->createElement('Agt');
                $rspndr->appendChild($agt);
                $finInstnId = $dom->createElement('FinInstnId');
                $agt->appendChild($finInstnId);
                $finInstnId->appendChild($dom->createElement('BICFI', htmlspecialchars($this->responderAgentBic)));
            } elseif ($this->responderPartyName !== null) {
                $pty = $dom->createElement('Pty');
                $rspndr->appendChild($pty);
                $pty->appendChild($dom->createElement('Nm', htmlspecialchars($this->responderPartyName)));
            }
        }

        // Case
        if ($this->caseId !== null) {
            $case = $dom->createElement('Case');
            $caseStsRptReq->appendChild($case);
            $case->appendChild($dom->createElement('Id', htmlspecialchars($this->caseId)));

            if ($this->caseCreator !== null) {
                $cretr = $dom->createElement('Cretr');
                $case->appendChild($cretr);
                $pty = $dom->createElement('Pty');
                $cretr->appendChild($pty);
                $pty->appendChild($dom->createElement('Nm', htmlspecialchars($this->caseCreator)));
            }
        }

        return $dom->saveXML() ?: '';
    }
}
