<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : OriginalPaymentInformation.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2;

/**
 * Original payment information and status for pain.002 (OrgnlPmtInfAndSts).
 * 
 * Status einer Payment Instruction aus der Original-Nachricht.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type2
 */
final class OriginalPaymentInformation {
    /** @var TransactionInformationAndStatus[] */
    private array $transactionStatuses = [];

    /**
     * @param string $originalPaymentInformationId Original Payment Information ID (OrgnlPmtInfId)
     * @param TransactionStatus|null $status Payment-Status (PmtInfSts)
     * @param StatusReason[] $statusReasons Status reasons (StsRsnInf)
     * @param int|null $numberOfTransactionsPerStatus Anzahl Transaktionen je Status
     * @param TransactionInformationAndStatus[] $transactionStatuses Einzelne Transaktions-Status
     */
    public function __construct(
        private readonly string $originalPaymentInformationId,
        private readonly ?TransactionStatus $status = null,
        private readonly array $statusReasons = [],
        private readonly ?int $numberOfTransactionsPerStatus = null,
        array $transactionStatuses = []
    ) {
        $this->transactionStatuses = $transactionStatuses;
    }

    public function getOriginalPaymentInformationId(): string {
        return $this->originalPaymentInformationId;
    }

    public function getStatus(): ?TransactionStatus {
        return $this->status;
    }

    /**
     * @return StatusReason[]
     */
    public function getStatusReasons(): array {
        return $this->statusReasons;
    }

    public function getNumberOfTransactionsPerStatus(): ?int {
        return $this->numberOfTransactionsPerStatus;
    }

    /**
     * @return TransactionInformationAndStatus[]
     */
    public function getTransactionStatuses(): array {
        return $this->transactionStatuses;
    }

    /**
     * Adds a transaction status.
     */
    public function addTransactionStatus(TransactionInformationAndStatus $status): self {
        $clone = clone $this;
        $clone->transactionStatuses[] = $status;
        return $clone;
    }

    /**
     * Checks if all transactions were successful.
     */
    public function isFullyAccepted(): bool {
        if ($this->status?->isSuccessful()) {
            return true;
        }

        foreach ($this->transactionStatuses as $txStatus) {
            if (!$txStatus->isSuccessful()) {
                return false;
            }
        }

        return count($this->transactionStatuses) > 0;
    }

    /**
     * Checks if there are rejected transactions.
     */
    public function hasRejections(): bool {
        if ($this->status?->isRejected()) {
            return true;
        }

        foreach ($this->transactionStatuses as $txStatus) {
            if ($txStatus->isRejected()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns all rejected transactions.
     */
    public function getRejectedTransactions(): array {
        return array_filter(
            $this->transactionStatuses,
            fn($tx) => $tx->isRejected()
        );
    }

    /**
     * Counts the transactions.
     */
    public function countTransactionStatuses(): int {
        return count($this->transactionStatuses);
    }
}
