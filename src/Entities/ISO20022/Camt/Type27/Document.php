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

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type27;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use CommonToolkit\FinancialFormats\Traits\XmlDocumentExportTrait;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use DOMDocument;

/**
 * CAMT.027 Document (Claim Non Receipt).
 * 
 * Represents a request for proof of a payment not received
 * according to ISO 20022 camt.027.001.xx Standard.
 * 
 * Used by the beneficiary to claim a payment that
 * angeblich gesendet, aber nicht empfangen wurde.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type27
 */
class Document implements CamtDocumentInterface {
    use XmlDocumentExportTrait;

    protected string $assignmentId;
    protected DateTimeImmutable $creationDateTime;
    protected ?string $assignerAgentBic = null;
    protected ?string $assignerPartyName = null;
    protected ?string $assigneeAgentBic = null;
    protected ?string $assigneePartyName = null;
    protected ?string $caseId = null;
    protected ?string $caseCreator = null;

    // Underlying Transaction Reference
    protected ?string $originalMessageId = null;
    protected ?string $originalMessageNameId = null;
    protected ?DateTimeImmutable $originalCreationDateTime = null;
    protected ?string $originalEndToEndId = null;
    protected ?string $originalTransactionId = null;
    protected ?float $originalInterbankSettlementAmount = null;
    protected ?CurrencyCode $originalCurrency = null;
    protected ?DateTimeImmutable $originalInterbankSettlementDate = null;

    // Claim Non Receipt specific
    protected ?bool $missingCoverIndicator = null;
    protected ?DateTimeImmutable $coverDate = null;
    protected ?string $debtorName = null;
    protected ?string $creditorName = null;

    public function __construct(
        string $assignmentId,
        DateTimeImmutable|string $creationDateTime,
        ?string $assignerAgentBic = null,
        ?string $assignerPartyName = null,
        ?string $assigneeAgentBic = null,
        ?string $assigneePartyName = null,
        ?string $caseId = null,
        ?string $caseCreator = null,
        ?string $originalMessageId = null,
        ?string $originalMessageNameId = null,
        DateTimeImmutable|string|null $originalCreationDateTime = null,
        ?string $originalEndToEndId = null,
        ?string $originalTransactionId = null,
        float|string|null $originalInterbankSettlementAmount = null,
        CurrencyCode|string|null $originalCurrency = null,
        DateTimeImmutable|string|null $originalInterbankSettlementDate = null,
        ?bool $missingCoverIndicator = null,
        DateTimeImmutable|string|null $coverDate = null,
        ?string $debtorName = null,
        ?string $creditorName = null
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
        $this->originalMessageId = $originalMessageId;
        $this->originalMessageNameId = $originalMessageNameId;
        $this->originalCreationDateTime = $originalCreationDateTime instanceof DateTimeImmutable
            ? $originalCreationDateTime
            : ($originalCreationDateTime !== null ? new DateTimeImmutable($originalCreationDateTime) : null);
        $this->originalEndToEndId = $originalEndToEndId;
        $this->originalTransactionId = $originalTransactionId;
        $this->originalInterbankSettlementAmount = is_string($originalInterbankSettlementAmount) ? (float) $originalInterbankSettlementAmount : $originalInterbankSettlementAmount;
        $this->originalCurrency = $originalCurrency instanceof CurrencyCode
            ? $originalCurrency
            : ($originalCurrency !== null ? CurrencyCode::from($originalCurrency) : null);
        $this->originalInterbankSettlementDate = $originalInterbankSettlementDate instanceof DateTimeImmutable
            ? $originalInterbankSettlementDate
            : ($originalInterbankSettlementDate !== null ? new DateTimeImmutable($originalInterbankSettlementDate) : null);
        $this->missingCoverIndicator = $missingCoverIndicator;
        $this->coverDate = $coverDate instanceof DateTimeImmutable
            ? $coverDate
            : ($coverDate !== null ? new DateTimeImmutable($coverDate) : null);
        $this->debtorName = $debtorName;
        $this->creditorName = $creditorName;
    }

    public function getCamtType(): CamtType {
        return CamtType::CAMT027;
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

    public function getOriginalMessageId(): ?string {
        return $this->originalMessageId;
    }

    public function getOriginalMessageNameId(): ?string {
        return $this->originalMessageNameId;
    }

    public function getOriginalCreationDateTime(): ?DateTimeImmutable {
        return $this->originalCreationDateTime;
    }

    public function getOriginalEndToEndId(): ?string {
        return $this->originalEndToEndId;
    }

    public function getOriginalTransactionId(): ?string {
        return $this->originalTransactionId;
    }

    public function getOriginalInterbankSettlementAmount(): ?float {
        return $this->originalInterbankSettlementAmount;
    }

    public function getOriginalCurrency(): ?CurrencyCode {
        return $this->originalCurrency;
    }

    public function getOriginalInterbankSettlementDate(): ?DateTimeImmutable {
        return $this->originalInterbankSettlementDate;
    }

    public function getMissingCoverIndicator(): ?bool {
        return $this->missingCoverIndicator;
    }

    /**
     * Checks if the payment is not covered.
     */
    public function isMissingCover(): bool {
        return $this->missingCoverIndicator === true;
    }

    public function getCoverDate(): ?DateTimeImmutable {
        return $this->coverDate;
    }

    public function getDebtorName(): ?string {
        return $this->debtorName;
    }

    public function getCreditorName(): ?string {
        return $this->creditorName;
    }

    protected function getDefaultXml(): string {
        return $this->toXml();
    }

    public function toXml(CamtVersion $version = CamtVersion::V10): string {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $namespace = $version->getNamespace($this->getCamtType());
        $root = $dom->createElementNS($namespace, 'Document');
        $dom->appendChild($root);

        $clmNonRcpt = $dom->createElement('ClmNonRcpt');
        $root->appendChild($clmNonRcpt);

        // Assgnmt (Assignment)
        $assgnmt = $dom->createElement('Assgnmt');
        $clmNonRcpt->appendChild($assgnmt);

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

        // Case
        if ($this->caseId !== null) {
            $case = $dom->createElement('Case');
            $clmNonRcpt->appendChild($case);
            $case->appendChild($dom->createElement('Id', htmlspecialchars($this->caseId)));

            if ($this->caseCreator !== null) {
                $cretr = $dom->createElement('Cretr');
                $case->appendChild($cretr);
                $pty = $dom->createElement('Pty');
                $cretr->appendChild($pty);
                $pty->appendChild($dom->createElement('Nm', htmlspecialchars($this->caseCreator)));
            }
        }

        // Undrlyg (Underlying)
        if ($this->originalTransactionId !== null || $this->originalEndToEndId !== null) {
            $undrlyg = $dom->createElement('Undrlyg');
            $clmNonRcpt->appendChild($undrlyg);

            $initn = $dom->createElement('Initn');
            $undrlyg->appendChild($initn);

            if ($this->originalMessageId !== null) {
                $orgnlGrpInf = $dom->createElement('OrgnlGrpInf');
                $initn->appendChild($orgnlGrpInf);
                $orgnlGrpInf->appendChild($dom->createElement('OrgnlMsgId', htmlspecialchars($this->originalMessageId)));
                if ($this->originalMessageNameId !== null) {
                    $orgnlGrpInf->appendChild($dom->createElement('OrgnlMsgNmId', htmlspecialchars($this->originalMessageNameId)));
                }
            }

            if ($this->originalEndToEndId !== null) {
                $initn->appendChild($dom->createElement('OrgnlEndToEndId', htmlspecialchars($this->originalEndToEndId)));
            }

            if ($this->originalTransactionId !== null) {
                $initn->appendChild($dom->createElement('OrgnlTxId', htmlspecialchars($this->originalTransactionId)));
            }

            if ($this->originalInterbankSettlementAmount !== null && $this->originalCurrency !== null) {
                $amtElement = $dom->createElement('OrgnlIntrBkSttlmAmt', number_format($this->originalInterbankSettlementAmount, 2, '.', ''));
                $amtElement->setAttribute('Ccy', $this->originalCurrency->value);
                $initn->appendChild($amtElement);
            }

            if ($this->originalInterbankSettlementDate !== null) {
                $initn->appendChild($dom->createElement('OrgnlIntrBkSttlmDt', $this->originalInterbankSettlementDate->format('Y-m-d')));
            }
        }

        // CoverDtls (Cover Details)
        if ($this->missingCoverIndicator !== null || $this->coverDate !== null) {
            $coverDtls = $dom->createElement('CoverDtls');
            $clmNonRcpt->appendChild($coverDtls);

            if ($this->missingCoverIndicator !== null) {
                $coverDtls->appendChild($dom->createElement('MssngCoverInd', $this->missingCoverIndicator ? 'true' : 'false'));
            }

            if ($this->coverDate !== null) {
                $coverDtls->appendChild($dom->createElement('CoverDt', $this->coverDate->format('Y-m-d')));
            }
        }

        return $dom->saveXML() ?: '';
    }
}
