<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : TransactionInformationAndStatus.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\TransactionStatus;
use DateTimeImmutable;

/**
 * Transaction information and status for pain.002 (TxInfAndSts).
 * 
 * Details zum Status einer einzelnen Transaktion.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type2
 */
final readonly class TransactionInformationAndStatus {
    /**
     * @param string|null $statusId Status-ID (StsId)
     * @param string|null $originalInstructionId Original Instruction ID (OrgnlInstrId)
     * @param string|null $originalEndToEndId Original End-to-End ID (OrgnlEndToEndId)
     * @param string|null $originalUetr Original UETR (OrgnlUETR)
     * @param TransactionStatus|null $status Transaktionsstatus (TxSts)
     * @param StatusReason[] $statusReasons Status reasons (StsRsnInf)
     * @param float|null $originalAmount Original-Betrag (OrgnlTxRef/Amt)
     * @param CurrencyCode|null $originalCurrency Original currency
     * @param DateTimeImmutable|null $acceptanceDateTime Akzeptanz-Zeitpunkt (AccptncDtTm)
     */
    public function __construct(
        private ?string $statusId = null,
        private ?string $originalInstructionId = null,
        private ?string $originalEndToEndId = null,
        private ?string $originalUetr = null,
        private ?TransactionStatus $status = null,
        private array $statusReasons = [],
        private ?float $originalAmount = null,
        private ?CurrencyCode $originalCurrency = null,
        private ?DateTimeImmutable $acceptanceDateTime = null
    ) {
    }

    /**
     * Creates a successful status.
     */
    public static function accepted(
        string $originalEndToEndId,
        TransactionStatus $status = TransactionStatus::ACCEPTED_SETTLEMENT_COMPLETED
    ): self {
        return new self(
            originalEndToEndId: $originalEndToEndId,
            status: $status
        );
    }

    /**
     * Creates a rejection status.
     */
    public static function rejected(
        string $originalEndToEndId,
        StatusReason $reason
    ): self {
        return new self(
            originalEndToEndId: $originalEndToEndId,
            status: TransactionStatus::REJECTED,
            statusReasons: [$reason]
        );
    }

    public function getStatusId(): ?string {
        return $this->statusId;
    }

    public function getOriginalInstructionId(): ?string {
        return $this->originalInstructionId;
    }

    public function getOriginalEndToEndId(): ?string {
        return $this->originalEndToEndId;
    }

    public function getOriginalUetr(): ?string {
        return $this->originalUetr;
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

    public function getOriginalAmount(): ?float {
        return $this->originalAmount;
    }

    public function getOriginalCurrency(): ?CurrencyCode {
        return $this->originalCurrency;
    }

    public function getAcceptanceDateTime(): ?DateTimeImmutable {
        return $this->acceptanceDateTime;
    }

    /**
     * Checks if the transaction was successful.
     */
    public function isSuccessful(): bool {
        return $this->status?->isSuccessful() ?? false;
    }

    /**
     * Checks if the transaction was rejected.
     */
    public function isRejected(): bool {
        return $this->status?->isRejected() ?? false;
    }

    /**
     * Returns the first rejection reason.
     */
    public function getFirstReason(): ?StatusReason {
        return $this->statusReasons[0] ?? null;
    }
}