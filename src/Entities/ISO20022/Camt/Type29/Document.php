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

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt029Generator;
use CommonToolkit\FinancialFormats\Traits\XmlDocumentExportTrait;
use DateTimeImmutable;

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
    use XmlDocumentExportTrait;

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

    protected function getDefaultXml(): string {
        return $this->toXml();
    }

    public function toXml(CamtVersion $version = CamtVersion::V13): string {
        return (new Camt029Generator())->generate($this, $version);
    }
}
