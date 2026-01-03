<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : OriginalPaymentInformation.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55;

use DateTimeImmutable;

/**
 * CAMT.055 Original Payment Information and Cancellation.
 * 
 * Represents the original payment information to be cancelled.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type55
 */
class OriginalPaymentInformation {
    private ?string $originalPaymentInformationId;
    private ?int $originalNumberOfTransactions;
    private ?float $originalControlSum;
    private ?string $paymentInformationCancellationStatus;
    private bool $cancelAllTransactions = false;

    /** @var PaymentCancellationRequest[] */
    private array $transactionInformation = [];

    public function __construct(
        ?string $originalPaymentInformationId = null,
        ?int $originalNumberOfTransactions = null,
        float|string|null $originalControlSum = null,
        ?string $paymentInformationCancellationStatus = null,
        bool $cancelAllTransactions = false
    ) {
        $this->originalPaymentInformationId = $originalPaymentInformationId;
        $this->originalNumberOfTransactions = $originalNumberOfTransactions;
        $this->originalControlSum = is_string($originalControlSum) ? (float) $originalControlSum : $originalControlSum;
        $this->paymentInformationCancellationStatus = $paymentInformationCancellationStatus;
        $this->cancelAllTransactions = $cancelAllTransactions;
    }

    public function getOriginalPaymentInformationId(): ?string {
        return $this->originalPaymentInformationId;
    }

    public function getOriginalNumberOfTransactions(): ?int {
        return $this->originalNumberOfTransactions;
    }

    public function getOriginalControlSum(): ?float {
        return $this->originalControlSum;
    }

    public function getPaymentInformationCancellationStatus(): ?string {
        return $this->paymentInformationCancellationStatus;
    }

    public function isCancelAllTransactions(): bool {
        return $this->cancelAllTransactions;
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
