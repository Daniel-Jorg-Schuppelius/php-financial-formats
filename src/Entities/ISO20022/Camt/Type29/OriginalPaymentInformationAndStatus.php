<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : OriginalPaymentInformationAndStatus.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29;

/**
 * CAMT.029 Original Payment Information and Status.
 * 
 * Enthält Informationen über den Status einer ursprünglichen Zahlungsinformation.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type29
 */
class OriginalPaymentInformationAndStatus {
    private ?string $originalPaymentInformationId;
    private ?int $originalNumberOfTransactions;
    private ?string $originalControlSum;
    private ?string $paymentInformationCancellationStatus;

    /** @var CancellationStatus[] */
    private array $cancellationStatusReasonInformation = [];

    /** @var TransactionInformationAndStatus[] */
    private array $transactionInformationAndStatus = [];

    public function __construct(
        ?string $originalPaymentInformationId = null,
        ?int $originalNumberOfTransactions = null,
        ?string $originalControlSum = null,
        ?string $paymentInformationCancellationStatus = null
    ) {
        $this->originalPaymentInformationId = $originalPaymentInformationId;
        $this->originalNumberOfTransactions = $originalNumberOfTransactions;
        $this->originalControlSum = $originalControlSum;
        $this->paymentInformationCancellationStatus = $paymentInformationCancellationStatus;
    }

    public function getOriginalPaymentInformationId(): ?string {
        return $this->originalPaymentInformationId;
    }

    public function getOriginalNumberOfTransactions(): ?int {
        return $this->originalNumberOfTransactions;
    }

    public function getOriginalControlSum(): ?string {
        return $this->originalControlSum;
    }

    public function getPaymentInformationCancellationStatus(): ?string {
        return $this->paymentInformationCancellationStatus;
    }

    public function addCancellationStatusReasonInformation(CancellationStatus $status): void {
        $this->cancellationStatusReasonInformation[] = $status;
    }

    /**
     * @return CancellationStatus[]
     */
    public function getCancellationStatusReasonInformation(): array {
        return $this->cancellationStatusReasonInformation;
    }

    public function addTransactionInformationAndStatus(TransactionInformationAndStatus $txInfo): void {
        $this->transactionInformationAndStatus[] = $txInfo;
    }

    /**
     * @return TransactionInformationAndStatus[]
     */
    public function getTransactionInformationAndStatus(): array {
        return $this->transactionInformationAndStatus;
    }

    public function getTransactionCount(): int {
        return count($this->transactionInformationAndStatus);
    }
}
