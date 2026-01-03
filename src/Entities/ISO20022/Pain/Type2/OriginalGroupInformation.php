<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : OriginalGroupInformation.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2;

use DateTimeImmutable;

/**
 * Original group information and status for pain.002 (OrgnlGrpInfAndSts).
 * 
 * Informationen zur Original-Nachricht und deren Gesamtstatus.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type2
 */
final readonly class OriginalGroupInformation {
    /**
     * @param string $originalMessageId Original Message ID (OrgnlMsgId)
     * @param string $originalMessageNameId Original Message Name ID (OrgnlMsgNmId)
     * @param DateTimeImmutable|null $originalCreationDateTime Original Creation DateTime (OrgnlCreDtTm)
     * @param int|null $originalNumberOfTransactions Anzahl der Original-Transaktionen (OrgnlNbOfTxs)
     * @param float|null $originalControlSum Original Control Sum (OrgnlCtrlSum)
     * @param TransactionStatus|null $groupStatus Gruppen-Status (GrpSts)
     * @param StatusReason[] $statusReasons Status reasons (StsRsnInf)
     */
    public function __construct(
        private string $originalMessageId,
        private string $originalMessageNameId,
        private ?DateTimeImmutable $originalCreationDateTime = null,
        private ?int $originalNumberOfTransactions = null,
        private ?float $originalControlSum = null,
        private ?TransactionStatus $groupStatus = null,
        private array $statusReasons = []
    ) {
    }

    /**
     * Factory for pain.001 response.
     */
    public static function forPain001(
        string $originalMessageId,
        ?TransactionStatus $groupStatus = null
    ): self {
        return new self(
            originalMessageId: $originalMessageId,
            originalMessageNameId: 'pain.001.001.12',
            groupStatus: $groupStatus
        );
    }

    /**
     * Factory for pain.008 response.
     */
    public static function forPain008(
        string $originalMessageId,
        ?TransactionStatus $groupStatus = null
    ): self {
        return new self(
            originalMessageId: $originalMessageId,
            originalMessageNameId: 'pain.008.001.11',
            groupStatus: $groupStatus
        );
    }

    public function getOriginalMessageId(): string {
        return $this->originalMessageId;
    }

    public function getOriginalMessageNameId(): string {
        return $this->originalMessageNameId;
    }

    public function getOriginalCreationDateTime(): ?DateTimeImmutable {
        return $this->originalCreationDateTime;
    }

    public function getOriginalNumberOfTransactions(): ?int {
        return $this->originalNumberOfTransactions;
    }

    public function getOriginalControlSum(): ?float {
        return $this->originalControlSum;
    }

    public function getGroupStatus(): ?TransactionStatus {
        return $this->groupStatus;
    }

    /**
     * @return StatusReason[]
     */
    public function getStatusReasons(): array {
        return $this->statusReasons;
    }

    /**
     * Checks if the entire group was accepted.
     */
    public function isGroupAccepted(): bool {
        return $this->groupStatus?->isSuccessful() ?? false;
    }

    /**
     * Checks if the entire group was rejected.
     */
    public function isGroupRejected(): bool {
        return $this->groupStatus?->isRejected() ?? false;
    }
}
