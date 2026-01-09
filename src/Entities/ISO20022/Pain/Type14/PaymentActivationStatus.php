<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PaymentActivationStatus.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type14;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\StatusReason;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\TransactionStatus;

/**
 * Payment activation status for pain.014.
 * 
 * Status einer einzelnen Zahlungsaktivierung.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type14
 */
final readonly class PaymentActivationStatus {
    public function __construct(
        private string $originalInstructionId,
        private string $originalEndToEndId,
        private TransactionStatus $status,
        private ?StatusReason $statusReason = null,
        private ?float $originalAmount = null,
        private ?string $creditorReference = null
    ) {
    }

    public static function accepted(
        string $originalInstructionId,
        string $originalEndToEndId
    ): self {
        return new self(
            originalInstructionId: $originalInstructionId,
            originalEndToEndId: $originalEndToEndId,
            status: TransactionStatus::ACCEPTED_SETTLEMENT_COMPLETED
        );
    }

    public static function pending(
        string $originalInstructionId,
        string $originalEndToEndId
    ): self {
        return new self(
            originalInstructionId: $originalInstructionId,
            originalEndToEndId: $originalEndToEndId,
            status: TransactionStatus::PENDING
        );
    }

    public static function rejected(
        string $originalInstructionId,
        string $originalEndToEndId,
        StatusReason $reason
    ): self {
        return new self(
            originalInstructionId: $originalInstructionId,
            originalEndToEndId: $originalEndToEndId,
            status: TransactionStatus::REJECTED,
            statusReason: $reason
        );
    }

    public function getOriginalInstructionId(): string {
        return $this->originalInstructionId;
    }

    public function getOriginalEndToEndId(): string {
        return $this->originalEndToEndId;
    }

    public function getStatus(): TransactionStatus {
        return $this->status;
    }

    public function getStatusReason(): ?StatusReason {
        return $this->statusReason;
    }

    public function getOriginalAmount(): ?float {
        return $this->originalAmount;
    }

    public function getCreditorReference(): ?string {
        return $this->creditorReference;
    }

    public function isAccepted(): bool {
        return $this->status->isSuccessful();
    }

    public function isRejected(): bool {
        return $this->status === TransactionStatus::REJECTED;
    }

    public function isPending(): bool {
        return $this->status === TransactionStatus::PENDING;
    }
}