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

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55;

use DateTimeImmutable;

/**
 * CAMT.055 Underlying Transaction.
 * 
 * Repräsentiert eine Gruppe von Stornierungsanfragen zu einer ursprünglichen Nachricht.
 * Enthält sowohl OrgnlGrpInfAndCxl als auch OrgnlPmtInfAndCxl Strukturen.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type55
 */
class UnderlyingTransaction {
    private ?string $originalGroupInformationMessageId;
    private ?string $originalGroupInformationMessageNameId;
    private ?DateTimeImmutable $originalGroupInformationCreationDateTime;
    private ?int $originalNumberOfTransactions;
    private ?float $originalControlSum;
    private ?string $groupCancellationStatus;

    /** @var OriginalPaymentInformation[] */
    private array $originalPaymentInformationAndCancellation = [];

    /** @var PaymentCancellationRequest[] */
    private array $transactionInformation = [];

    public function __construct(
        ?string $originalGroupInformationMessageId = null,
        ?string $originalGroupInformationMessageNameId = null,
        DateTimeImmutable|string|null $originalGroupInformationCreationDateTime = null,
        ?int $originalNumberOfTransactions = null,
        float|string|null $originalControlSum = null,
        ?string $groupCancellationStatus = null
    ) {
        $this->originalGroupInformationMessageId = $originalGroupInformationMessageId;
        $this->originalGroupInformationMessageNameId = $originalGroupInformationMessageNameId;
        $this->originalGroupInformationCreationDateTime = $originalGroupInformationCreationDateTime instanceof DateTimeImmutable
            ? $originalGroupInformationCreationDateTime
            : ($originalGroupInformationCreationDateTime !== null ? new DateTimeImmutable($originalGroupInformationCreationDateTime) : null);
        $this->originalNumberOfTransactions = $originalNumberOfTransactions;
        $this->originalControlSum = is_string($originalControlSum) ? (float) $originalControlSum : $originalControlSum;
        $this->groupCancellationStatus = $groupCancellationStatus;
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

    public function getGroupCancellationStatus(): ?string {
        return $this->groupCancellationStatus;
    }

    public function addOriginalPaymentInformationAndCancellation(OriginalPaymentInformation $pmtInfAndCxl): void {
        $this->originalPaymentInformationAndCancellation[] = $pmtInfAndCxl;
    }

    /**
     * @return OriginalPaymentInformation[]
     */
    public function getOriginalPaymentInformationAndCancellation(): array {
        return $this->originalPaymentInformationAndCancellation;
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

    public function getTotalTransactionCount(): int {
        $count = count($this->transactionInformation);
        foreach ($this->originalPaymentInformationAndCancellation as $pmtInf) {
            $count += $pmtInf->getTransactionCount();
        }
        return $count;
    }
}
