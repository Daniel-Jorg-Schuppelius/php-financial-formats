<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : OriginalGroupInformationAndStatus.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29;

use DateTimeImmutable;

/**
 * CAMT.029 Original Group Information and Status.
 * 
 * Enthält Informationen über die ursprüngliche Nachrichtengruppe und deren Status.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type29
 */
class OriginalGroupInformationAndStatus {
    private ?string $originalMessageId;
    private ?string $originalMessageNameId;
    private ?DateTimeImmutable $originalCreationDateTime;
    private ?int $originalNumberOfTransactions;
    private ?float $originalControlSum;
    private ?string $groupCancellationStatus;

    /** @var CancellationStatus[] */
    private array $cancellationStatusReasonInformation = [];

    public function __construct(
        ?string $originalMessageId = null,
        ?string $originalMessageNameId = null,
        DateTimeImmutable|string|null $originalCreationDateTime = null,
        ?int $originalNumberOfTransactions = null,
        float|string|null $originalControlSum = null,
        ?string $groupCancellationStatus = null
    ) {
        $this->originalMessageId = $originalMessageId;
        $this->originalMessageNameId = $originalMessageNameId;
        $this->originalCreationDateTime = $originalCreationDateTime instanceof DateTimeImmutable
            ? $originalCreationDateTime
            : ($originalCreationDateTime !== null ? new DateTimeImmutable($originalCreationDateTime) : null);
        $this->originalNumberOfTransactions = $originalNumberOfTransactions;
        $this->originalControlSum = is_string($originalControlSum) ? (float) $originalControlSum : $originalControlSum;
        $this->groupCancellationStatus = $groupCancellationStatus;
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

    public function getOriginalNumberOfTransactions(): ?int {
        return $this->originalNumberOfTransactions;
    }

    public function getOriginalControlSum(): ?float {
        return $this->originalControlSum;
    }

    public function getGroupCancellationStatus(): ?string {
        return $this->groupCancellationStatus;
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
     * Prüft ob alle Stornierungen akzeptiert wurden.
     */
    public function isFullyAccepted(): bool {
        return $this->groupCancellationStatus === 'ACCR';
    }

    /**
     * Prüft ob Stornierungen teilweise akzeptiert wurden.
     */
    public function isPartiallyAccepted(): bool {
        return $this->groupCancellationStatus === 'PACR';
    }

    /**
     * Prüft ob alle Stornierungen abgelehnt wurden.
     */
    public function isRejected(): bool {
        return $this->groupCancellationStatus === 'RJCR';
    }
}
