<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : MandateAcceptance.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type12;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Mandate;
use CommonToolkit\FinancialFormats\Enums\MandateStatus;
use DateTimeImmutable;

/**
 * Mandate acceptance for pain.012.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type12
 */
final readonly class MandateAcceptance {
    public function __construct(
        private string $mandateId,
        private MandateStatus $status,
        private ?Mandate $mandate = null,
        private ?string $originalMessageId = null,
        private ?DateTimeImmutable $acceptanceDateTime = null,
        private ?string $rejectReason = null
    ) {
    }

    public static function accepted(
        string $mandateId,
        ?Mandate $mandate = null
    ): self {
        return new self(
            mandateId: $mandateId,
            status: MandateStatus::ACCEPTED,
            mandate: $mandate,
            acceptanceDateTime: new DateTimeImmutable()
        );
    }

    public static function rejected(
        string $mandateId,
        string $rejectReason
    ): self {
        return new self(
            mandateId: $mandateId,
            status: MandateStatus::REJECTED,
            rejectReason: $rejectReason,
            acceptanceDateTime: new DateTimeImmutable()
        );
    }

    public function getMandateId(): string {
        return $this->mandateId;
    }

    public function getStatus(): MandateStatus {
        return $this->status;
    }

    public function getMandate(): ?Mandate {
        return $this->mandate;
    }

    public function getOriginalMessageId(): ?string {
        return $this->originalMessageId;
    }

    public function getAcceptanceDateTime(): ?DateTimeImmutable {
        return $this->acceptanceDateTime;
    }

    public function getRejectReason(): ?string {
        return $this->rejectReason;
    }

    public function isAccepted(): bool {
        return $this->status === MandateStatus::ACCEPTED;
    }

    public function isRejected(): bool {
        return $this->status === MandateStatus::REJECTED;
    }
}
