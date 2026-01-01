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

namespace CommonToolkit\FinancialFormats\Entities\Camt\Type56;

use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * CAMT.056 Payment Transaction Information.
 * 
 * Repräsentiert eine einzelne Stornierungsanfrage für eine Zahlung 
 * gemäß ISO 20022 camt.056.001.xx Standard.
 * 
 * Enthält Informationen zur ursprünglichen Transaktion und den Stornierungsgrund.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type56
 */
class PaymentCancellationRequest {
    private ?string $originalMessageId;
    private ?string $originalMessageNameId;
    private ?DateTimeImmutable $originalCreationDateTime;
    private ?string $originalEndToEndId;
    private ?string $originalInstructionId;
    private ?string $originalTransactionId;
    private ?string $originalInterbankSettlementAmount;
    private ?CurrencyCode $originalCurrency;
    private ?DateTimeImmutable $originalInterbankSettlementDate;
    private ?string $cancellationReasonCode;
    private ?string $cancellationReasonProprietary;
    private ?string $cancellationReasonAdditionalInfo;
    private ?string $debtorName;
    private ?string $debtorIban;
    private ?string $debtorBic;
    private ?string $creditorName;
    private ?string $creditorIban;
    private ?string $creditorBic;

    public function __construct(
        ?string $originalMessageId = null,
        ?string $originalMessageNameId = null,
        DateTimeImmutable|string|null $originalCreationDateTime = null,
        ?string $originalEndToEndId = null,
        ?string $originalInstructionId = null,
        ?string $originalTransactionId = null,
        ?string $originalInterbankSettlementAmount = null,
        CurrencyCode|string|null $originalCurrency = null,
        DateTimeImmutable|string|null $originalInterbankSettlementDate = null,
        ?string $cancellationReasonCode = null,
        ?string $cancellationReasonProprietary = null,
        ?string $cancellationReasonAdditionalInfo = null,
        ?string $debtorName = null,
        ?string $debtorIban = null,
        ?string $debtorBic = null,
        ?string $creditorName = null,
        ?string $creditorIban = null,
        ?string $creditorBic = null
    ) {
        $this->originalMessageId = $originalMessageId;
        $this->originalMessageNameId = $originalMessageNameId;
        $this->originalCreationDateTime = $originalCreationDateTime instanceof DateTimeImmutable
            ? $originalCreationDateTime
            : ($originalCreationDateTime !== null ? new DateTimeImmutable($originalCreationDateTime) : null);
        $this->originalEndToEndId = $originalEndToEndId;
        $this->originalInstructionId = $originalInstructionId;
        $this->originalTransactionId = $originalTransactionId;
        $this->originalInterbankSettlementAmount = $originalInterbankSettlementAmount;
        $this->originalCurrency = $originalCurrency instanceof CurrencyCode
            ? $originalCurrency
            : ($originalCurrency !== null ? CurrencyCode::from($originalCurrency) : null);
        $this->originalInterbankSettlementDate = $originalInterbankSettlementDate instanceof DateTimeImmutable
            ? $originalInterbankSettlementDate
            : ($originalInterbankSettlementDate !== null ? new DateTimeImmutable($originalInterbankSettlementDate) : null);
        $this->cancellationReasonCode = $cancellationReasonCode;
        $this->cancellationReasonProprietary = $cancellationReasonProprietary;
        $this->cancellationReasonAdditionalInfo = $cancellationReasonAdditionalInfo;
        $this->debtorName = $debtorName;
        $this->debtorIban = $debtorIban;
        $this->debtorBic = $debtorBic;
        $this->creditorName = $creditorName;
        $this->creditorIban = $creditorIban;
        $this->creditorBic = $creditorBic;
    }

    public function getOriginalMessageId(): ?string {
        return $this->originalMessageId;
    }

    public function getOriginalMessageNameId(): ?string {
        return $this->originalMessageNameId;
    }

    public function getOriginalCreationDateTime(): ?DateTimeImmutable {
        return $this->originalCreationDateTime;
    }

    public function getOriginalEndToEndId(): ?string {
        return $this->originalEndToEndId;
    }

    public function getOriginalInstructionId(): ?string {
        return $this->originalInstructionId;
    }

    public function getOriginalTransactionId(): ?string {
        return $this->originalTransactionId;
    }

    public function getOriginalInterbankSettlementAmount(): ?string {
        return $this->originalInterbankSettlementAmount;
    }

    public function getOriginalCurrency(): ?CurrencyCode {
        return $this->originalCurrency;
    }

    public function getOriginalInterbankSettlementDate(): ?DateTimeImmutable {
        return $this->originalInterbankSettlementDate;
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
}
