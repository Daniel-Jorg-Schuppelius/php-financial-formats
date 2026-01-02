<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt087DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type87\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type87\ModificationRequest;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder für CAMT.087 Documents (Request to Modify Payment).
 * 
 * Erstellt Anfragen zur Änderung einer bereits eingereichten Zahlung.
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Camt
 */
final class Camt087DocumentBuilder {
    private string $assignmentId;
    private DateTimeImmutable $creationDateTime;
    private ?string $assignerAgentBic = null;
    private ?string $assignerPartyName = null;
    private ?string $assigneeAgentBic = null;
    private ?string $assigneePartyName = null;
    private ?string $caseId = null;
    private ?string $caseCreator = null;
    private ?string $originalMessageId = null;
    private ?string $originalMessageNameId = null;
    private ?DateTimeImmutable $originalCreationDateTime = null;
    private ?string $originalEndToEndId = null;
    private ?string $originalTransactionId = null;
    private ?float $originalInterbankSettlementAmount = null;
    private ?CurrencyCode $originalCurrency = null;
    private ?DateTimeImmutable $originalInterbankSettlementDate = null;
    /** @var ModificationRequest[] */
    private array $modificationRequests = [];

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

    public function withOriginalMessage(string $messageId, ?string $messageNameId = null, ?DateTimeImmutable $creationDateTime = null): self {
        $clone = clone $this;
        $clone->originalMessageId = $messageId;
        $clone->originalMessageNameId = $messageNameId;
        $clone->originalCreationDateTime = $creationDateTime;
        return $clone;
    }

    public function withOriginalTransaction(string $transactionId, ?string $endToEndId = null): self {
        $clone = clone $this;
        $clone->originalTransactionId = $transactionId;
        $clone->originalEndToEndId = $endToEndId;
        return $clone;
    }

    public function withOriginalAmount(float $amount, CurrencyCode $currency, ?DateTimeImmutable $settlementDate = null): self {
        $clone = clone $this;
        $clone->originalInterbankSettlementAmount = $amount;
        $clone->originalCurrency = $currency;
        $clone->originalInterbankSettlementDate = $settlementDate;
        return $clone;
    }

    public function addModificationRequest(ModificationRequest $request): self {
        $clone = clone $this;
        $clone->modificationRequests = [...$this->modificationRequests, $request];
        return $clone;
    }

    /**
     * Fügt eine Betragsänderung hinzu.
     */
    public function requestAmountChange(float $newAmount, CurrencyCode $currency): self {
        return $this->addModificationRequest(new ModificationRequest(
            requestedSettlementAmount: $newAmount,
            requestedCurrency: $currency
        ));
    }

    /**
     * Fügt eine Gläubigeränderung hinzu.
     */
    public function requestCreditorChange(string $name, ?string $accountIban = null): self {
        return $this->addModificationRequest(new ModificationRequest(
            creditorName: $name,
            creditorAccount: $accountIban
        ));
    }

    /**
     * Fügt eine Verwendungszweck-Änderung hinzu.
     */
    public function requestRemittanceChange(string $remittanceInformation): self {
        return $this->addModificationRequest(new ModificationRequest(
            remittanceInformation: $remittanceInformation
        ));
    }

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

        foreach ($this->modificationRequests as $request) {
            $document->addModificationRequest($request);
        }

        return $document;
    }
}
