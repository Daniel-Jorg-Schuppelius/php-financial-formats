<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : TransactionInformationAndStatus.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Camt\Type29;

use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * CAMT.029 Transaction Information and Status.
 * 
 * Enthält Informationen über den Status einer einzelnen Transaktion im Rahmen
 * einer Stornierungsantwort.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type29
 */
class TransactionInformationAndStatus {
    private ?string $cancellationStatusId;
    private ?string $originalInstructionId;
    private ?string $originalEndToEndId;
    private ?string $originalTransactionId;
    private ?string $transactionCancellationStatus;
    private ?string $originalAmount;
    private ?CurrencyCode $originalCurrency;
    private ?DateTimeImmutable $originalInterbankSettlementDate;
    private ?string $debtorName;
    private ?string $debtorIban;
    private ?string $creditorName;
    private ?string $creditorIban;

    /** @var CancellationStatus[] */
    private array $cancellationStatusReasonInformation = [];

    public function __construct(
        ?string $cancellationStatusId = null,
        ?string $originalInstructionId = null,
        ?string $originalEndToEndId = null,
        ?string $originalTransactionId = null,
        ?string $transactionCancellationStatus = null,
        ?string $originalAmount = null,
        CurrencyCode|string|null $originalCurrency = null,
        DateTimeImmutable|string|null $originalInterbankSettlementDate = null,
        ?string $debtorName = null,
        ?string $debtorIban = null,
        ?string $creditorName = null,
        ?string $creditorIban = null
    ) {
        $this->cancellationStatusId = $cancellationStatusId;
        $this->originalInstructionId = $originalInstructionId;
        $this->originalEndToEndId = $originalEndToEndId;
        $this->originalTransactionId = $originalTransactionId;
        $this->transactionCancellationStatus = $transactionCancellationStatus;
        $this->originalAmount = $originalAmount;
        $this->originalCurrency = $originalCurrency instanceof CurrencyCode
            ? $originalCurrency
            : ($originalCurrency !== null ? CurrencyCode::from($originalCurrency) : null);
        $this->originalInterbankSettlementDate = $originalInterbankSettlementDate instanceof DateTimeImmutable
            ? $originalInterbankSettlementDate
            : ($originalInterbankSettlementDate !== null ? new DateTimeImmutable($originalInterbankSettlementDate) : null);
        $this->debtorName = $debtorName;
        $this->debtorIban = $debtorIban;
        $this->creditorName = $creditorName;
        $this->creditorIban = $creditorIban;
    }

    public function getCancellationStatusId(): ?string {
        return $this->cancellationStatusId;
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

    public function getTransactionCancellationStatus(): ?string {
        return $this->transactionCancellationStatus;
    }

    public function getOriginalAmount(): ?string {
        return $this->originalAmount;
    }

    public function getOriginalCurrency(): ?CurrencyCode {
        return $this->originalCurrency;
    }

    public function getOriginalInterbankSettlementDate(): ?DateTimeImmutable {
        return $this->originalInterbankSettlementDate;
    }

    public function getDebtorName(): ?string {
        return $this->debtorName;
    }

    public function getDebtorIban(): ?string {
        return $this->debtorIban;
    }

    public function getCreditorName(): ?string {
        return $this->creditorName;
    }

    public function getCreditorIban(): ?string {
        return $this->creditorIban;
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

    /**
     * Prüft ob die Stornierung akzeptiert wurde.
     */
    public function isAccepted(): bool {
        return $this->transactionCancellationStatus === 'CNCL' || $this->transactionCancellationStatus === 'ACCP';
    }

    /**
     * Prüft ob die Stornierung abgelehnt wurde.
     */
    public function isRejected(): bool {
        return $this->transactionCancellationStatus === 'RJCR';
    }

    /**
     * Prüft ob die Stornierung noch aussteht.
     */
    public function isPending(): bool {
        return $this->transactionCancellationStatus === 'PDNG';
    }
}
