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

namespace CommonToolkit\FinancialFormats\Entities\Camt\Type39;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use DateTimeImmutable;
use DOMDocument;

/**
 * CAMT.039 Document (Case Status Report).
 *
 * Repräsentiert einen Fallstatusbericht
 * gemäß ISO 20022 camt.039.001.xx Standard.
 *
 * Antwort auf CAMT.038 mit dem aktuellen Status
 * einer laufenden Untersuchung oder eines Falls.
 *
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type39
 */
class Document implements CamtDocumentInterface {
    protected string $reportId;
    protected DateTimeImmutable $creationDateTime;
    protected ?string $caseId = null;
    protected ?string $caseCreator = null;
    protected ?string $reporterAgentBic = null;
    protected ?string $reporterPartyName = null;
    protected ?string $receiverAgentBic = null;
    protected ?string $receiverPartyName = null;

    // Status
    protected ?string $statusCode = null;
    protected ?string $statusReason = null;
    protected ?string $additionalInformation = null;

    public function __construct(
        string $reportId,
        DateTimeImmutable|string $creationDateTime,
        ?string $statusCode = null,
        ?string $statusReason = null,
        ?string $caseId = null,
        ?string $caseCreator = null,
        ?string $reporterAgentBic = null,
        ?string $reporterPartyName = null,
        ?string $receiverAgentBic = null,
        ?string $receiverPartyName = null,
        ?string $additionalInformation = null
    ) {
        $this->reportId = $reportId;
        $this->creationDateTime = $creationDateTime instanceof DateTimeImmutable
            ? $creationDateTime
            : new DateTimeImmutable($creationDateTime);
        $this->statusCode = $statusCode;
        $this->statusReason = $statusReason;
        $this->caseId = $caseId;
        $this->caseCreator = $caseCreator;
        $this->reporterAgentBic = $reporterAgentBic;
        $this->reporterPartyName = $reporterPartyName;
        $this->receiverAgentBic = $receiverAgentBic;
        $this->receiverPartyName = $receiverPartyName;
        $this->additionalInformation = $additionalInformation;
    }

    public function getCamtType(): CamtType {
        return CamtType::CAMT039;
    }

    public function getReportId(): string {
        return $this->reportId;
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

    public function getReporterAgentBic(): ?string {
        return $this->reporterAgentBic;
    }

    public function getReporterPartyName(): ?string {
        return $this->reporterPartyName;
    }

    public function getReceiverAgentBic(): ?string {
        return $this->receiverAgentBic;
    }

    public function getReceiverPartyName(): ?string {
        return $this->receiverPartyName;
    }

    public function getStatusCode(): ?string {
        return $this->statusCode;
    }

    public function getStatusReason(): ?string {
        return $this->statusReason;
    }

    public function getAdditionalInformation(): ?string {
        return $this->additionalInformation;
    }

    public function toXml(CamtVersion $version = CamtVersion::V06): string {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $namespace = $version->getNamespace($this->getCamtType());
        $root = $dom->createElementNS($namespace, 'Document');
        $dom->appendChild($root);

        $caseStsRpt = $dom->createElement('CaseStsRpt');
        $root->appendChild($caseStsRpt);

        // Hdr (Header)
        $hdr = $dom->createElement('Hdr');
        $caseStsRpt->appendChild($hdr);

        $hdr->appendChild($dom->createElement('Id', htmlspecialchars($this->reportId)));
        $hdr->appendChild($dom->createElement('CreDtTm', $this->creationDateTime->format('Y-m-d\TH:i:s.vP')));

        // Reporter (From)
        if ($this->reporterAgentBic !== null || $this->reporterPartyName !== null) {
            $fr = $dom->createElement('Fr');
            $hdr->appendChild($fr);

            if ($this->reporterAgentBic !== null) {
                $agt = $dom->createElement('Agt');
                $fr->appendChild($agt);
                $finInstnId = $dom->createElement('FinInstnId');
                $agt->appendChild($finInstnId);
                $finInstnId->appendChild($dom->createElement('BICFI', htmlspecialchars($this->reporterAgentBic)));
            } elseif ($this->reporterPartyName !== null) {
                $pty = $dom->createElement('Pty');
                $fr->appendChild($pty);
                $pty->appendChild($dom->createElement('Nm', htmlspecialchars($this->reporterPartyName)));
            }
        }

        // Receiver (To)
        if ($this->receiverAgentBic !== null || $this->receiverPartyName !== null) {
            $to = $dom->createElement('To');
            $hdr->appendChild($to);

            if ($this->receiverAgentBic !== null) {
                $agt = $dom->createElement('Agt');
                $to->appendChild($agt);
                $finInstnId = $dom->createElement('FinInstnId');
                $agt->appendChild($finInstnId);
                $finInstnId->appendChild($dom->createElement('BICFI', htmlspecialchars($this->receiverAgentBic)));
            } elseif ($this->receiverPartyName !== null) {
                $pty = $dom->createElement('Pty');
                $to->appendChild($pty);
                $pty->appendChild($dom->createElement('Nm', htmlspecialchars($this->receiverPartyName)));
            }
        }

        // Case
        if ($this->caseId !== null) {
            $case = $dom->createElement('Case');
            $caseStsRpt->appendChild($case);
            $case->appendChild($dom->createElement('Id', htmlspecialchars($this->caseId)));

            if ($this->caseCreator !== null) {
                $cretr = $dom->createElement('Cretr');
                $case->appendChild($cretr);
                $pty = $dom->createElement('Pty');
                $cretr->appendChild($pty);
                $pty->appendChild($dom->createElement('Nm', htmlspecialchars($this->caseCreator)));
            }
        }

        // Sts (Status)
        $sts = $dom->createElement('Sts');
        $caseStsRpt->appendChild($sts);

        if ($this->statusCode !== null) {
            $sts->appendChild($dom->createElement('Conf', htmlspecialchars($this->statusCode)));
        }

        // StsRsn (Status Reason)
        if ($this->statusReason !== null || $this->additionalInformation !== null) {
            $stsRsn = $dom->createElement('StsRsn');
            $sts->appendChild($stsRsn);

            if ($this->statusReason !== null) {
                $rsn = $dom->createElement('Rsn');
                $stsRsn->appendChild($rsn);
                $rsn->appendChild($dom->createElement('Prtry', htmlspecialchars($this->statusReason)));
            }

            if ($this->additionalInformation !== null) {
                $stsRsn->appendChild($dom->createElement('AddtlInf', htmlspecialchars($this->additionalInformation)));
            }
        }

        return $dom->saveXML() ?: '';
    }
}
