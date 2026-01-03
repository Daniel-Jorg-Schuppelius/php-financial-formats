<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : UnderlyingTransaction.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type56;

use DateTimeImmutable;

/**
 * CAMT.056 Underlying Transaction.
 * 
 * Represents a group of cancellation requests for an original message.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type56
 */
class UnderlyingTransaction {
    private ?string $originalGroupInformationMessageId;
    private ?string $originalGroupInformationMessageNameId;
    private ?DateTimeImmutable $originalGroupInformationCreationDateTime;
    private ?int $originalNumberOfTransactions;
    private ?float $originalControlSum;
    private ?string $groupCancellationStatusCode;
    private ?string $groupCancellationStatusProprietary;

    /** @var PaymentCancellationRequest[] */
    private array $transactionInformation = [];

    public function __construct(
        ?string $originalGroupInformationMessageId = null,
        ?string $originalGroupInformationMessageNameId = null,
        DateTimeImmutable|string|null $originalGroupInformationCreationDateTime = null,
        ?int $originalNumberOfTransactions = null,
        float|string|null $originalControlSum = null,
        ?string $groupCancellationStatusCode = null,
        ?string $groupCancellationStatusProprietary = null
    ) {
        $this->originalGroupInformationMessageId = $originalGroupInformationMessageId;
        $this->originalGroupInformationMessageNameId = $originalGroupInformationMessageNameId;
        $this->originalGroupInformationCreationDateTime = $originalGroupInformationCreationDateTime instanceof DateTimeImmutable
            ? $originalGroupInformationCreationDateTime
            : ($originalGroupInformationCreationDateTime !== null ? new DateTimeImmutable($originalGroupInformationCreationDateTime) : null);
        $this->originalNumberOfTransactions = $originalNumberOfTransactions;
        $this->originalControlSum = is_string($originalControlSum) ? (float) $originalControlSum : $originalControlSum;
        $this->groupCancellationStatusCode = $groupCancellationStatusCode;
        $this->groupCancellationStatusProprietary = $groupCancellationStatusProprietary;
    }

    public function getOriginalGroupInformationMessageId(): ?string {
        return $this->originalGroupInformationMessageId;
    }

    public function getOriginalGroupInformationMessageNameId(): ?string {
        return $this->originalGroupInformationMessageNameId;
    }

    public function getOriginalGroupInformationCreationDateTime(): ?DateTimeImmutable {
        return $this->originalGroupInformationCreationDateTime;
    }

    public function getOriginalNumberOfTransactions(): ?int {
        return $this->originalNumberOfTransactions;
    }

    public function getOriginalControlSum(): ?float {
        return $this->originalControlSum;
    }

    public function getGroupCancellationStatusCode(): ?string {
        return $this->groupCancellationStatusCode;
    }

    public function getGroupCancellationStatusProprietary(): ?string {
        return $this->groupCancellationStatusProprietary;
    }

    public function addTransactionInformation(PaymentCancellationRequest $txInfo): void {
        $this->transactionInformation[] = $txInfo;
    }

    /**
     * @return PaymentCancellationRequest[]
     */
    public function getTransactionInformation(): array {
        return $this->transactionInformation;
    }

    public function getTransactionCount(): int {
        return count($this->transactionInformation);
    }
}
