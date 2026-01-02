<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt037DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type37\Document;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder für CAMT.037 Documents (Debit Authorisation Request).
 * 
 * Erstellt Anfragen zur Belastungsautorisierung.
 * Wird verwendet um eine Genehmigung für eine Belastung anzufordern.
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Camt
 */
final class Camt037DocumentBuilder {
    private string $assignmentId;
    private DateTimeImmutable $creationDateTime;
    private ?string $assignerAgentBic = null;
    private ?string $assignerPartyName = null;
    private ?string $assigneeAgentBic = null;
    private ?string $assigneePartyName = null;
    private ?string $caseId = null;
    private ?string $caseCreator = null;
    private ?string $originalTransactionId = null;
    private ?string $originalEndToEndId = null;
    private ?float $originalInterbankSettlementAmount = null;
    private ?CurrencyCode $originalCurrency = null;
    private ?string $debtorName = null;
    private ?string $debtorAccountIban = null;
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

    public function withUnderlyingTransaction(string $transactionId, ?string $endToEndId = null): self {
        $clone = clone $this;
        $clone->originalTransactionId = $transactionId;
        $clone->originalEndToEndId = $endToEndId;
        return $clone;
    }

    public function withInstructedAmount(float $amount, CurrencyCode $currency): self {
        $clone = clone $this;
        $clone->originalInterbankSettlementAmount = $amount;
        $clone->originalCurrency = $currency;
        return $clone;
    }

    public function withDebtor(string $name, ?string $accountIban = null): self {
        $clone = clone $this;
        $clone->debtorName = $name;
        $clone->debtorAccountIban = $accountIban;
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
            assignerAgentBic: $this->assignerAgentBic,
            assignerPartyName: $this->assignerPartyName,
            assigneeAgentBic: $this->assigneeAgentBic,
            assigneePartyName: $this->assigneePartyName,
            caseId: $this->caseId,
            caseCreator: $this->caseCreator,
            originalTransactionId: $this->originalTransactionId,
            originalEndToEndId: $this->originalEndToEndId,
            originalInterbankSettlementAmount: $this->originalInterbankSettlementAmount,
            originalCurrency: $this->originalCurrency,
            debtorName: $this->debtorName,
            debtorAccountIban: $this->debtorAccountIban,
            reason: $this->reason
        );
    }
}
