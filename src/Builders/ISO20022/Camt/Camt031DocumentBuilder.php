<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt031DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type31\Document;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder for CAMT.031 Documents (Reject Investigation).
 * 
 * Creates rejections of investigation requests.
 * Used to reject an investigation request
 * (e.g. because it is invalid or cannot be processed).
 * 
 * Verwendung:
 * ```php
 * $document = Camt031DocumentBuilder::create('ASGN-001')
 *     ->withAssignerAgent('COBADEFFXXX')
 *     ->withAssigneeAgent('DEUTDEFFXXX')
 *     ->forCase('CASE-001', 'COBADEFFXXX')
 *     ->withRejectionReason('NOOR')
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Camt
 */
final class Camt031DocumentBuilder {
    private string $assignmentId;
    private DateTimeImmutable $creationDateTime;
    private ?string $assignerAgentBic = null;
    private ?string $assignerPartyName = null;
    private ?string $assigneeAgentBic = null;
    private ?string $assigneePartyName = null;
    private ?string $caseId = null;
    private ?string $caseCreator = null;
    private ?string $rejectionReasonCode = null;
    private ?string $rejectionReasonProprietary = null;
    private ?string $additionalInformation = null;

    private function __construct(string $assignmentId) {
        if (strlen($assignmentId) > 35) {
            throw new InvalidArgumentException('AssignmentId must not exceed 35 characters');
        }
        $this->assignmentId = $assignmentId;
        $this->creationDateTime = new DateTimeImmutable();
    }

    /**
     * Creates a new builder with assignment ID.
     */
    public static function create(string $assignmentId): self {
        return new self($assignmentId);
    }

    /**
     * Sets the creation timestamp (default: now).
     */
    public function withCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    /**
     * Sets the assigner agent (sending bank).
     */
    public function withAssignerAgent(string $bic): self {
        $clone = clone $this;
        $clone->assignerAgentBic = $bic;
        return $clone;
    }

    /**
     * Sets the assigner party name.
     */
    public function withAssignerPartyName(string $name): self {
        $clone = clone $this;
        $clone->assignerPartyName = $name;
        return $clone;
    }

    /**
     * Sets the assignee agent (receiving bank).
     */
    public function withAssigneeAgent(string $bic): self {
        $clone = clone $this;
        $clone->assigneeAgentBic = $bic;
        return $clone;
    }

    /**
     * Sets the assignee party name.
     */
    public function withAssigneePartyName(string $name): self {
        $clone = clone $this;
        $clone->assigneePartyName = $name;
        return $clone;
    }

    /**
     * Sets the case reference.
     */
    public function forCase(string $caseId, ?string $caseCreator = null): self {
        $clone = clone $this;
        $clone->caseId = $caseId;
        $clone->caseCreator = $caseCreator;
        return $clone;
    }

    /**
     * Sets the rejection reason (code).
     * 
     * Common codes:
     * - NOOR: No Original Transaction Received
     * - ARDT: Already Returned
     * - NOAS: No Answer From Customer
     * - LEGL: Legal Reasons
     */
    public function withRejectionReason(string $reasonCode): self {
        $clone = clone $this;
        $clone->rejectionReasonCode = $reasonCode;
        return $clone;
    }

    /**
     * Sets the proprietary rejection reason.
     */
    public function withProprietaryRejectionReason(string $reason): self {
        $clone = clone $this;
        $clone->rejectionReasonProprietary = $reason;
        return $clone;
    }

    /**
     * Sets additional information.
     */
    public function withAdditionalInformation(string $info): self {
        $clone = clone $this;
        $clone->additionalInformation = $info;
        return $clone;
    }

    /**
     * Creates the CAMT.031 document.
     */
    public function build(): Document {
        return new Document(
            assignmentId: $this->assignmentId,
            creationDateTime: $this->creationDateTime,
            assignerAgentBic: $this->assignerAgentBic,
            assignerPartyName: $this->assignerPartyName,
            assigneeAgentBic: $this->assigneeAgentBic,
            assigneePartyName: $this->assigneePartyName,
            caseId: $this->caseId,
            caseCreator: $this->caseCreator,
            rejectionReasonCode: $this->rejectionReasonCode,
            rejectionReasonProprietary: $this->rejectionReasonProprietary,
            additionalInformation: $this->additionalInformation
        );
    }
}
