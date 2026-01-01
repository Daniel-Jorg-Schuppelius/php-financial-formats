<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Document.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Camt\Type29;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use DateTimeImmutable;
use DOMDocument;

/**
 * CAMT.029 Document (Resolution of Investigation).
 * 
 * Repräsentiert eine Antwort auf eine Untersuchungsanfrage 
 * gemäß ISO 20022 camt.029.001.xx Standard.
 * 
 * Verwendet <RsltnOfInvstgtn> als Root und enthält Status zu Stornierungsanfragen.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type29
 */
class Document implements CamtDocumentInterface {
    protected string $assignmentId;
    protected DateTimeImmutable $creationDateTime;
    protected ?string $assignerAgentBic = null;
    protected ?string $assignerPartyName = null;
    protected ?string $assigneeAgentBic = null;
    protected ?string $assigneePartyName = null;
    protected ?string $caseId = null;
    protected ?string $caseCreator = null;
    protected ?string $investigationStatus = null;
    protected ?string $investigationStatusProprietary = null;

    /** @var CancellationDetails[] */
    protected array $cancellationDetails = [];

    public function __construct(
        string $assignmentId,
        DateTimeImmutable|string $creationDateTime,
        ?string $assignerAgentBic = null,
        ?string $assignerPartyName = null,
        ?string $assigneeAgentBic = null,
        ?string $assigneePartyName = null,
        ?string $caseId = null,
        ?string $caseCreator = null,
        ?string $investigationStatus = null,
        ?string $investigationStatusProprietary = null
    ) {
        $this->assignmentId = $assignmentId;
        $this->creationDateTime = $creationDateTime instanceof DateTimeImmutable
            ? $creationDateTime
            : new DateTimeImmutable($creationDateTime);
        $this->assignerAgentBic = $assignerAgentBic;
        $this->assignerPartyName = $assignerPartyName;
        $this->assigneeAgentBic = $assigneeAgentBic;
        $this->assigneePartyName = $assigneePartyName;
        $this->caseId = $caseId;
        $this->caseCreator = $caseCreator;
        $this->investigationStatus = $investigationStatus;
        $this->investigationStatusProprietary = $investigationStatusProprietary;
    }

    public function getCamtType(): CamtType {
        return CamtType::CAMT029;
    }

    public function getAssignmentId(): string {
        return $this->assignmentId;
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

    public function getInvestigationStatus(): ?string {
        return $this->investigationStatus;
    }

    public function getInvestigationStatusProprietary(): ?string {
        return $this->investigationStatusProprietary;
    }

    public function getStatus(): ?string {
        return $this->investigationStatus ?? $this->investigationStatusProprietary;
    }

    public function addCancellationDetails(CancellationDetails $details): void {
        $this->cancellationDetails[] = $details;
    }

    /**
     * @return CancellationDetails[]
     */
    public function getCancellationDetails(): array {
        return $this->cancellationDetails;
    }

    /**
     * Alle TransactionInformationAndStatus aus allen CancellationDetails.
     * 
     * @return TransactionInformationAndStatus[]
     */
    public function getAllTransactionStatus(): array {
        $all = [];
        foreach ($this->cancellationDetails as $details) {
            foreach ($details->getAllTransactionInformationAndStatus() as $txInfo) {
                $all[] = $txInfo;
            }
        }
        return $all;
    }

    /**
     * Prüft ob die Untersuchung abgeschlossen ist.
     */
    public function isResolved(): bool {
        return in_array($this->investigationStatus, ['CNCL', 'MODI', 'ACCP', 'RJCR'], true);
    }

    /**
     * Prüft ob die Stornierung akzeptiert wurde.
     */
    public function isAccepted(): bool {
        return $this->investigationStatus === 'ACCP' || $this->investigationStatus === 'CNCL';
    }

    /**
     * Prüft ob die Stornierung abgelehnt wurde.
     */
    public function isRejected(): bool {
        return $this->investigationStatus === 'RJCR';
    }

    public function toXml(CamtVersion $version = CamtVersion::V13): string {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $namespace = $version->getNamespace($this->getCamtType());
        $root = $dom->createElementNS($namespace, 'Document');
        $dom->appendChild($root);

        $rsltnOfInvstgtn = $dom->createElement('RsltnOfInvstgtn');
        $root->appendChild($rsltnOfInvstgtn);

        // Assgnmt (Assignment)
        $assgnmt = $dom->createElement('Assgnmt');
        $rsltnOfInvstgtn->appendChild($assgnmt);

        $assgnmt->appendChild($dom->createElement('Id', htmlspecialchars($this->assignmentId)));
        $assgnmt->appendChild($dom->createElement('CreDtTm', $this->creationDateTime->format('Y-m-d\TH:i:s.vP')));

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

        // Case (optional)
        if ($this->caseId !== null) {
            $case = $dom->createElement('Case');
            $rsltnOfInvstgtn->appendChild($case);
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
        if ($this->investigationStatus !== null || $this->investigationStatusProprietary !== null) {
            $sts = $dom->createElement('Sts');
            $rsltnOfInvstgtn->appendChild($sts);

            if ($this->investigationStatus !== null) {
                $sts->appendChild($dom->createElement('Conf', htmlspecialchars($this->investigationStatus)));
            } else {
                $prtry = $dom->createElement('Prtry');
                $sts->appendChild($prtry);
                $prtry->appendChild($dom->createElement('Id', htmlspecialchars($this->investigationStatusProprietary)));
            }
        }

        // CxlDtls (Cancellation Details)
        foreach ($this->cancellationDetails as $details) {
            $cxlDtls = $dom->createElement('CxlDtls');
            $rsltnOfInvstgtn->appendChild($cxlDtls);

            // OrgnlGrpInfAndSts
            $grpInfAndSts = $details->getOriginalGroupInformationAndStatus();
            if ($grpInfAndSts !== null && $grpInfAndSts->getOriginalMessageId() !== null) {
                $orgnlGrpInfAndSts = $dom->createElement('OrgnlGrpInfAndSts');
                $cxlDtls->appendChild($orgnlGrpInfAndSts);

                $orgnlGrpInfAndSts->appendChild(
                    $dom->createElement('OrgnlMsgId', htmlspecialchars($grpInfAndSts->getOriginalMessageId()))
                );

                if ($grpInfAndSts->getOriginalMessageNameId() !== null) {
                    $orgnlGrpInfAndSts->appendChild(
                        $dom->createElement('OrgnlMsgNmId', htmlspecialchars($grpInfAndSts->getOriginalMessageNameId()))
                    );
                }

                if ($grpInfAndSts->getOriginalCreationDateTime() !== null) {
                    $orgnlGrpInfAndSts->appendChild(
                        $dom->createElement('OrgnlCreDtTm', $grpInfAndSts->getOriginalCreationDateTime()->format('Y-m-d\TH:i:s.vP'))
                    );
                }

                if ($grpInfAndSts->getGroupCancellationStatus() !== null) {
                    $orgnlGrpInfAndSts->appendChild(
                        $dom->createElement('GrpCxlSts', htmlspecialchars($grpInfAndSts->getGroupCancellationStatus()))
                    );
                }
            }

            // TxInfAndSts (Transaction Information and Status)
            foreach ($details->getTransactionInformationAndStatus() as $txInfAndSts) {
                $txInfAndStsElement = $dom->createElement('TxInfAndSts');
                $cxlDtls->appendChild($txInfAndStsElement);

                if ($txInfAndSts->getCancellationStatusId() !== null) {
                    $txInfAndStsElement->appendChild(
                        $dom->createElement('CxlStsId', htmlspecialchars($txInfAndSts->getCancellationStatusId()))
                    );
                }

                if ($txInfAndSts->getOriginalEndToEndId() !== null) {
                    $txInfAndStsElement->appendChild(
                        $dom->createElement('OrgnlEndToEndId', htmlspecialchars($txInfAndSts->getOriginalEndToEndId()))
                    );
                }

                if ($txInfAndSts->getOriginalTransactionId() !== null) {
                    $txInfAndStsElement->appendChild(
                        $dom->createElement('OrgnlTxId', htmlspecialchars($txInfAndSts->getOriginalTransactionId()))
                    );
                }

                if ($txInfAndSts->getTransactionCancellationStatus() !== null) {
                    $txInfAndStsElement->appendChild(
                        $dom->createElement('TxCxlSts', htmlspecialchars($txInfAndSts->getTransactionCancellationStatus()))
                    );
                }

                // CxlStsRsnInf
                foreach ($txInfAndSts->getCancellationStatusReasonInformation() as $stsRsn) {
                    $cxlStsRsnInf = $dom->createElement('CxlStsRsnInf');
                    $txInfAndStsElement->appendChild($cxlStsRsnInf);

                    $rsn = $dom->createElement('Rsn');
                    $cxlStsRsnInf->appendChild($rsn);

                    if ($stsRsn->getStatusCode() !== null) {
                        $rsn->appendChild($dom->createElement('Cd', htmlspecialchars($stsRsn->getStatusCode())));
                    } elseif ($stsRsn->getStatusProprietary() !== null) {
                        $rsn->appendChild($dom->createElement('Prtry', htmlspecialchars($stsRsn->getStatusProprietary())));
                    }

                    if ($stsRsn->getAdditionalInformation() !== null) {
                        $cxlStsRsnInf->appendChild(
                            $dom->createElement('AddtlInf', htmlspecialchars($stsRsn->getAdditionalInformation()))
                        );
                    }
                }
            }
        }

        return $dom->saveXML() ?: '';
    }
}
