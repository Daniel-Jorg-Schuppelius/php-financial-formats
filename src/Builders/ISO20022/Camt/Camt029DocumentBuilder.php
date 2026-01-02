<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt029DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\CancellationDetails;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\Document;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder für CAMT.029 Documents (Resolution of Investigation).
 * 
 * Erstellt Antworten auf Untersuchungsanfragen (z.B. Stornierungsantworten).
 * Wird typischerweise von Banken als Antwort auf CAMT.055/056 generiert.
 * 
 * Verwendung:
 * ```php
 * $document = Camt029DocumentBuilder::create('ASGN-001')
 *     ->withAssignerAgent('COBADEFFXXX')
 *     ->withAssigneeAgent('DEUTDEFFXXX')
 *     ->forCase('CASE-001', 'COBADEFFXXX')
 *     ->withStatus('ACCP')
 *     ->addCancellationDetails($details)
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Camt
 */
final class Camt029DocumentBuilder {
    private string $assignmentId;
    private DateTimeImmutable $creationDateTime;
    private ?string $assignerAgentBic = null;
    private ?string $assignerPartyName = null;
    private ?string $assigneeAgentBic = null;
    private ?string $assigneePartyName = null;
    private ?string $caseId = null;
    private ?string $caseCreator = null;
    private ?string $investigationStatus = null;
    private ?string $investigationStatusProprietary = null;

    /** @var CancellationDetails[] */
    private array $cancellationDetails = [];

    private function __construct(string $assignmentId) {
        if (strlen($assignmentId) > 35) {
            throw new InvalidArgumentException('AssignmentId darf maximal 35 Zeichen lang sein');
        }
        $this->assignmentId = $assignmentId;
        $this->creationDateTime = new DateTimeImmutable();
    }

    /**
     * Erzeugt neuen Builder mit Assignment-ID.
     */
    public static function create(string $assignmentId): self {
        return new self($assignmentId);
    }

    /**
     * Setzt den Erstellungszeitpunkt (Standard: jetzt).
     */
    public function withCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    /**
     * Setzt den Assigner Agent (sendende Bank).
     */
    public function withAssignerAgent(string $bic): self {
        $clone = clone $this;
        $clone->assignerAgentBic = $bic;
        return $clone;
    }

    /**
     * Setzt den Assigner Party Name.
     */
    public function withAssignerPartyName(string $name): self {
        $clone = clone $this;
        $clone->assignerPartyName = $name;
        return $clone;
    }

    /**
     * Setzt den Assignee Agent (empfangende Bank).
     */
    public function withAssigneeAgent(string $bic): self {
        $clone = clone $this;
        $clone->assigneeAgentBic = $bic;
        return $clone;
    }

    /**
     * Setzt den Assignee Party Name.
     */
    public function withAssigneePartyName(string $name): self {
        $clone = clone $this;
        $clone->assigneePartyName = $name;
        return $clone;
    }

    /**
     * Setzt die Case-Referenz.
     */
    public function forCase(string $caseId, ?string $caseCreator = null): self {
        $clone = clone $this;
        $clone->caseId = $caseId;
        $clone->caseCreator = $caseCreator;
        return $clone;
    }

    /**
     * Setzt den Investigation-Status (Code).
     * 
     * Gängige Status-Codes:
     * - ACCP: Accepted (Stornierung akzeptiert)
     * - RJCT: Rejected (Stornierung abgelehnt)
     * - PDNG: Pending (In Bearbeitung)
     * - CNCL: Cancelled
     */
    public function withStatus(string $statusCode): self {
        $clone = clone $this;
        $clone->investigationStatus = $statusCode;
        return $clone;
    }

    /**
     * Setzt den Investigation-Status (proprietär).
     */
    public function withProprietaryStatus(string $status): self {
        $clone = clone $this;
        $clone->investigationStatusProprietary = $status;
        return $clone;
    }

    /**
     * Fügt Stornierungsdetails hinzu.
     */
    public function addCancellationDetails(CancellationDetails $details): self {
        $clone = clone $this;
        $clone->cancellationDetails[] = $details;
        return $clone;
    }

    /**
     * Fügt mehrere Stornierungsdetails hinzu.
     * 
     * @param CancellationDetails[] $details
     */
    public function addCancellationDetailsBulk(array $details): self {
        $clone = clone $this;
        $clone->cancellationDetails = array_merge($clone->cancellationDetails, $details);
        return $clone;
    }

    /**
     * Erstellt das CAMT.029 Document.
     * 
     * @throws InvalidArgumentException wenn Pflichtfelder fehlen
     */
    public function build(): Document {
        $document = new Document(
            assignmentId: $this->assignmentId,
            creationDateTime: $this->creationDateTime,
            assignerAgentBic: $this->assignerAgentBic,
            assignerPartyName: $this->assignerPartyName,
            assigneeAgentBic: $this->assigneeAgentBic,
            assigneePartyName: $this->assigneePartyName,
            caseId: $this->caseId,
            caseCreator: $this->caseCreator,
            investigationStatus: $this->investigationStatus,
            investigationStatusProprietary: $this->investigationStatusProprietary
        );

        foreach ($this->cancellationDetails as $details) {
            $document->addCancellationDetails($details);
        }

        return $document;
    }

    // === Static Factory Methods ===

    /**
     * Erstellt eine einfache Akzeptanz-Antwort.
     */
    public static function createAccepted(
        string $assignmentId,
        string $caseId,
        string $assignerBic,
        string $assigneeBic
    ): Document {
        return self::create($assignmentId)
            ->forCase($caseId)
            ->withAssignerAgent($assignerBic)
            ->withAssigneeAgent($assigneeBic)
            ->withStatus('ACCP')
            ->build();
    }

    /**
     * Erstellt eine einfache Ablehnungs-Antwort.
     */
    public static function createRejected(
        string $assignmentId,
        string $caseId,
        string $assignerBic,
        string $assigneeBic
    ): Document {
        return self::create($assignmentId)
            ->forCase($caseId)
            ->withAssignerAgent($assignerBic)
            ->withAssigneeAgent($assigneeBic)
            ->withStatus('RJCT')
            ->build();
    }
}
