<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt036DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type36\Document;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder für CAMT.036 Documents (Debit Authorisation Response).
 * 
 * Erstellt Antworten auf Belastungsautorisierungsanfragen (CAMT.037).
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Camt
 */
final class Camt036DocumentBuilder {
    private string $assignmentId;
    private DateTimeImmutable $creationDateTime;
    private bool $debitAuthorised = false;
    private ?string $assignerAgentBic = null;
    private ?string $assignerPartyName = null;
    private ?string $assigneeAgentBic = null;
    private ?string $assigneePartyName = null;
    private ?string $caseId = null;
    private ?string $caseCreator = null;
    private ?float $authorisedAmount = null;
    private ?CurrencyCode $authorisedCurrency = null;
    private ?DateTimeImmutable $valueDate = null;
    private ?string $reason = null;

    private function __construct(string $assignmentId) {
        if (strlen($assignmentId) > 35) {
            throw new InvalidArgumentException('AssignmentId darf maximal 35 Zeichen lang sein');
        }
        $this->assignmentId = $assignmentId;
        $this->creationDateTime = new DateTimeImmutable();
    }

    public static function create(string $assignmentId): self {
        return new self($assignmentId);
    }

    public function withCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    public function authorised(bool $authorised = true): self {
        $clone = clone $this;
        $clone->debitAuthorised = $authorised;
        return $clone;
    }

    public function withAssignerAgent(string $bic): self {
        $clone = clone $this;
        $clone->assignerAgentBic = $bic;
        return $clone;
    }

    public function withAssignerPartyName(string $name): self {
        $clone = clone $this;
        $clone->assignerPartyName = $name;
        return $clone;
    }

    public function withAssigneeAgent(string $bic): self {
        $clone = clone $this;
        $clone->assigneeAgentBic = $bic;
        return $clone;
    }

    public function withAssigneePartyName(string $name): self {
        $clone = clone $this;
        $clone->assigneePartyName = $name;
        return $clone;
    }

    public function forCase(string $caseId, ?string $caseCreator = null): self {
        $clone = clone $this;
        $clone->caseId = $caseId;
        $clone->caseCreator = $caseCreator;
        return $clone;
    }

    public function withAuthorisedAmount(float $amount, CurrencyCode $currency, ?DateTimeImmutable $valueDate = null): self {
        $clone = clone $this;
        $clone->authorisedAmount = $amount;
        $clone->authorisedCurrency = $currency;
        $clone->valueDate = $valueDate;
        return $clone;
    }

    public function withReason(string $reason): self {
        $clone = clone $this;
        $clone->reason = $reason;
        return $clone;
    }

    public function build(): Document {
        return new Document(
            assignmentId: $this->assignmentId,
            creationDateTime: $this->creationDateTime,
            debitAuthorised: $this->debitAuthorised,
            assignerAgentBic: $this->assignerAgentBic,
            assignerPartyName: $this->assignerPartyName,
            assigneeAgentBic: $this->assigneeAgentBic,
            assigneePartyName: $this->assigneePartyName,
            caseId: $this->caseId,
            caseCreator: $this->caseCreator,
            authorisedAmount: $this->authorisedAmount,
            authorisedCurrency: $this->authorisedCurrency,
            valueDate: $this->valueDate,
            reason: $this->reason
        );
    }
}
