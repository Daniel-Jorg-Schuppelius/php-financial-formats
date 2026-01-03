<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt034DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type34\Document;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder for CAMT.034 Documents (Duplicate).
 * 
 * Creates responses to duplicate requests (CAMT.033).
 * Contains the requested duplicate of a message.
 * 
 * Verwendung:
 * ```php
 * $document = Camt034DocumentBuilder::create('ASGN-001')
 *     ->withAssignerAgent('COBADEFFXXX')
 *     ->withAssigneeAgent('DEUTDEFFXXX')
 *     ->forCase('CASE-001', 'COBADEFFXXX')
 *     ->withDuplicateContent('<original-message>...</original-message>')
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Camt
 */
final class Camt034DocumentBuilder {
    private string $assignmentId;
    private DateTimeImmutable $creationDateTime;
    private ?string $assignerAgentBic = null;
    private ?string $assignerPartyName = null;
    private ?string $assigneeAgentBic = null;
    private ?string $assigneePartyName = null;
    private ?string $caseId = null;
    private ?string $caseCreator = null;
    private ?string $duplicateContent = null;
    private ?string $duplicateContentType = null;

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
     * Sets the duplicate content.
     */
    public function withDuplicateContent(string $content, ?string $contentType = null): self {
        $clone = clone $this;
        $clone->duplicateContent = $content;
        $clone->duplicateContentType = $contentType;
        return $clone;
    }

    /**
     * Creates the CAMT.034 document.
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
            duplicateContent: $this->duplicateContent,
            duplicateContentType: $this->duplicateContentType
        );
    }
}
