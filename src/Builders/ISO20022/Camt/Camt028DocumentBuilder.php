<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt028DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type28\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type28\AdditionalPaymentInformation;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder for CAMT.028 Documents (Additional Payment Information).
 * 
 * Creates messages with additional payment information.
 * Used to submit additional information for an already
 * sent payment.
 * 
 * Verwendung:
 * ```php
 * $document = Camt028DocumentBuilder::create('ASGN-001')
 *     ->withAssignerAgent('COBADEFFXXX')
 *     ->withAssigneeAgent('DEUTDEFFXXX')
 *     ->forCase('CASE-001', 'COBADEFFXXX')
 *     ->withOriginalTransaction('MSG-001', 'ENDTOEND-001')
 *     ->addAdditionalInformation($info)
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Camt
 */
final class Camt028DocumentBuilder {
    private string $assignmentId;
    private DateTimeImmutable $creationDateTime;
    private ?string $assignerAgentBic = null;
    private ?string $assignerPartyName = null;
    private ?string $assigneeAgentBic = null;
    private ?string $assigneePartyName = null;
    private ?string $caseId = null;
    private ?string $caseCreator = null;

    // Underlying Transaction Reference
    private ?string $originalMessageId = null;
    private ?string $originalMessageNameId = null;
    private ?DateTimeImmutable $originalCreationDateTime = null;
    private ?string $originalEndToEndId = null;
    private ?string $originalTransactionId = null;
    private ?float $originalInterbankSettlementAmount = null;
    private ?CurrencyCode $originalCurrency = null;
    private ?DateTimeImmutable $originalInterbankSettlementDate = null;

    /** @var AdditionalPaymentInformation[] */
    private array $additionalInformation = [];

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
     * Sets the original transaction references.
     */
    public function withOriginalTransaction(
        ?string $messageId = null,
        ?string $endToEndId = null,
        ?string $transactionId = null,
        ?string $messageNameId = null
    ): self {
        $clone = clone $this;
        $clone->originalMessageId = $messageId;
        $clone->originalEndToEndId = $endToEndId;
        $clone->originalTransactionId = $transactionId;
        $clone->originalMessageNameId = $messageNameId;
        return $clone;
    }

    /**
     * Sets the original transaction amounts.
     */
    public function withOriginalAmount(
        float $amount,
        CurrencyCode $currency,
        ?DateTimeImmutable $settlementDate = null
    ): self {
        $clone = clone $this;
        $clone->originalInterbankSettlementAmount = $amount;
        $clone->originalCurrency = $currency;
        $clone->originalInterbankSettlementDate = $settlementDate;
        return $clone;
    }

    /**
     * Sets the original creation timestamp.
     */
    public function withOriginalCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->originalCreationDateTime = $dateTime;
        return $clone;
    }

    /**
     * Adds additional payment information.
     */
    public function addAdditionalInformation(AdditionalPaymentInformation $info): self {
        $clone = clone $this;
        $clone->additionalInformation[] = $info;
        return $clone;
    }

    /**
     * Creates the CAMT.028 document.
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
            originalMessageId: $this->originalMessageId,
            originalMessageNameId: $this->originalMessageNameId,
            originalCreationDateTime: $this->originalCreationDateTime,
            originalEndToEndId: $this->originalEndToEndId,
            originalTransactionId: $this->originalTransactionId,
            originalInterbankSettlementAmount: $this->originalInterbankSettlementAmount,
            originalCurrency: $this->originalCurrency,
            originalInterbankSettlementDate: $this->originalInterbankSettlementDate
        );

        foreach ($this->additionalInformation as $info) {
            $document->addAdditionalInformation($info);
        }

        return $document;
    }
}
