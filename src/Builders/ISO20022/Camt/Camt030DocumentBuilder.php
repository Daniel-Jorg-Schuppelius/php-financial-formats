<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt030DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type30\Document;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder für CAMT.030 Documents (Notification of Case Assignment).
 * 
 * Erstellt Benachrichtigungen über die Zuweisung eines Falls.
 * Wird verwendet, um einen Teilnehmer darüber zu informieren, dass
 * ihm ein Untersuchungsfall zugewiesen wurde.
 * 
 * Verwendung:
 * ```php
 * $document = Camt030DocumentBuilder::create('MSG-001')
 *     ->withAssignerAgent('COBADEFFXXX')
 *     ->withAssigneeAgent('DEUTDEFFXXX')
 *     ->forCase('CASE-001', 'COBADEFFXXX')
 *     ->withNotificationJustification('Case reassigned')
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Camt
 */
final class Camt030DocumentBuilder {
    private string $headerMessageId;
    private DateTimeImmutable $creationDateTime;
    private ?string $assignerAgentBic = null;
    private ?string $assignerPartyName = null;
    private ?string $assigneeAgentBic = null;
    private ?string $assigneePartyName = null;
    private ?string $caseId = null;
    private ?string $caseCreator = null;
    private ?string $notificationJustification = null;

    private function __construct(string $headerMessageId) {
        if (strlen($headerMessageId) > 35) {
            throw new InvalidArgumentException('HeaderMessageId darf maximal 35 Zeichen lang sein');
        }
        $this->headerMessageId = $headerMessageId;
        $this->creationDateTime = new DateTimeImmutable();
    }

    /**
     * Erzeugt neuen Builder mit Message-ID.
     */
    public static function create(string $headerMessageId): self {
        return new self($headerMessageId);
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
     * Setzt die Begründung für die Benachrichtigung.
     */
    public function withNotificationJustification(string $justification): self {
        $clone = clone $this;
        $clone->notificationJustification = $justification;
        return $clone;
    }

    /**
     * Erstellt das CAMT.030 Document.
     */
    public function build(): Document {
        return new Document(
            headerMessageId: $this->headerMessageId,
            creationDateTime: $this->creationDateTime,
            assignerAgentBic: $this->assignerAgentBic,
            assignerPartyName: $this->assignerPartyName,
            assigneeAgentBic: $this->assigneeAgentBic,
            assigneePartyName: $this->assigneePartyName,
            caseId: $this->caseId,
            caseCreator: $this->caseCreator,
            notificationJustification: $this->notificationJustification
        );
    }
}
