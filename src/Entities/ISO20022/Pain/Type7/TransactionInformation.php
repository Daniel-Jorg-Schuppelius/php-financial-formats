<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : TransactionInformation.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7;

use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * Transaction information for pain.007 (TxInf).
 * 
 * Details about the original transaction to be reversed.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type7
 */
final readonly class TransactionInformation {
    public function __construct(
        private ?string $reversalId = null,
        private ?string $originalInstructionId = null,
        private ?string $originalEndToEndId = null,
        private ?string $originalTransactionId = null,
        private ?float $reversedAmount = null,
        private ?CurrencyCode $currency = null,
        private ?ReversalReason $reversalReason = null,
        private ?DateTimeImmutable $originalInterbankSettlementDate = null
    ) {
    }

    public static function create(
        string $originalEndToEndId,
        float $reversedAmount,
        ReversalReason $reason,
        CurrencyCode $currency = CurrencyCode::Euro
    ): self {
        return new self(
            originalEndToEndId: $originalEndToEndId,
            reversedAmount: $reversedAmount,
            currency: $currency,
            reversalReason: $reason
        );
    }

    public function getReversalId(): ?string {
        return $this->reversalId;
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

    public function getReversedAmount(): ?float {
        return $this->reversedAmount;
    }

    public function getCurrency(): ?CurrencyCode {
        return $this->currency;
    }

    public function getReversalReason(): ?ReversalReason {
        return $this->reversalReason;
    }

    public function getOriginalInterbankSettlementDate(): ?DateTimeImmutable {
        return $this->originalInterbankSettlementDate;
    }
}
