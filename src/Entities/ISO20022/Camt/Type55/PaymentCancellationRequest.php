<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PaymentCancellationRequest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55;

use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * CAMT.055 Payment Transaction Information.
 * 
 * Represents a single cancellation request for a customer payment 
 * according to ISO 20022 camt.055.001.xx standard.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type55
 */
class PaymentCancellationRequest {
    private ?string $cancellationId;
    private ?string $originalInstructionId;
    private ?string $originalEndToEndId;
    private ?string $originalTransactionId;
    private ?float $originalAmount;
    private ?CurrencyCode $originalCurrency;
    private ?DateTimeImmutable $requestedExecutionDate;
    private ?string $cancellationReasonCode;
    private ?string $cancellationReasonProprietary;
    private ?string $cancellationReasonAdditionalInfo;
    private ?string $debtorName;
    private ?string $debtorIban;
    private ?string $debtorBic;
    private ?string $creditorName;
    private ?string $creditorIban;
    private ?string $creditorBic;
    private ?string $remittanceInformation;

    public function __construct(
        ?string $cancellationId = null,
        ?string $originalInstructionId = null,
        ?string $originalEndToEndId = null,
        ?string $originalTransactionId = null,
        float|string|null $originalAmount = null,
        CurrencyCode|string|null $originalCurrency = null,
        DateTimeImmutable|string|null $requestedExecutionDate = null,
        ?string $cancellationReasonCode = null,
        ?string $cancellationReasonProprietary = null,
        ?string $cancellationReasonAdditionalInfo = null,
        ?string $debtorName = null,
        ?string $debtorIban = null,
        ?string $debtorBic = null,
        ?string $creditorName = null,
        ?string $creditorIban = null,
        ?string $creditorBic = null,
        ?string $remittanceInformation = null
    ) {
        $this->cancellationId = $cancellationId;
        $this->originalInstructionId = $originalInstructionId;
        $this->originalEndToEndId = $originalEndToEndId;
        $this->originalTransactionId = $originalTransactionId;
        $this->originalAmount = is_string($originalAmount) ? (float) $originalAmount : $originalAmount;
        $this->originalCurrency = $originalCurrency instanceof CurrencyCode
            ? $originalCurrency
            : ($originalCurrency !== null ? CurrencyCode::from($originalCurrency) : null);
        $this->requestedExecutionDate = $requestedExecutionDate instanceof DateTimeImmutable
            ? $requestedExecutionDate
            : ($requestedExecutionDate !== null ? new DateTimeImmutable($requestedExecutionDate) : null);
        $this->cancellationReasonCode = $cancellationReasonCode;
        $this->cancellationReasonProprietary = $cancellationReasonProprietary;
        $this->cancellationReasonAdditionalInfo = $cancellationReasonAdditionalInfo;
        $this->debtorName = $debtorName;
        $this->debtorIban = $debtorIban;
        $this->debtorBic = $debtorBic;
        $this->creditorName = $creditorName;
        $this->creditorIban = $creditorIban;
        $this->creditorBic = $creditorBic;
        $this->remittanceInformation = $remittanceInformation;
    }

    public function getCancellationId(): ?string {
        return $this->cancellationId;
    }

    public function getOriginalInstructionId(): ?string {
        return $this->originalInstructionId;
    }

    public function getOriginalEndToEndId(): ?string {
        return $this->originalEndToEndId;
    }

    public function getOriginalTransactionId(): ?string {
        return $this->originalTransactionId;
    }

    public function getOriginalAmount(): ?float {
        return $this->originalAmount;
    }

    public function getOriginalCurrency(): ?CurrencyCode {
        return $this->originalCurrency;
    }

    public function getRequestedExecutionDate(): ?DateTimeImmutable {
        return $this->requestedExecutionDate;
    }

    public function getCancellationReasonCode(): ?string {
        return $this->cancellationReasonCode;
    }

    public function getCancellationReasonProprietary(): ?string {
        return $this->cancellationReasonProprietary;
    }

    public function getCancellationReasonAdditionalInfo(): ?string {
        return $this->cancellationReasonAdditionalInfo;
    }

    public function getCancellationReason(): ?string {
        return $this->cancellationReasonCode ?? $this->cancellationReasonProprietary;
    }

    public function getDebtorName(): ?string {
        return $this->debtorName;
    }

    public function getDebtorIban(): ?string {
        return $this->debtorIban;
    }

    public function getDebtorBic(): ?string {
        return $this->debtorBic;
    }

    public function getCreditorName(): ?string {
        return $this->creditorName;
    }

    public function getCreditorIban(): ?string {
        return $this->creditorIban;
    }

    public function getCreditorBic(): ?string {
        return $this->creditorBic;
    }

    public function getRemittanceInformation(): ?string {
        return $this->remittanceInformation;
    }
}
