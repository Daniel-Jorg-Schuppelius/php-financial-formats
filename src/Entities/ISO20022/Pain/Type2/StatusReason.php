<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : StatusReason.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2;

use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\StatusReasonCode;

/**
 * Status reason information for pain.002 (StsRsnInf).
 * 
 * Contains details about the status of a transaction.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type2
 */
final readonly class StatusReason {
    private ?StatusReasonCode $code;

    /**
     * @param StatusReasonCode|string|null $code Reason Code (Rsn/Cd)
     * @param string|null $proprietary Proprietary Reason (Rsn/Prtry)
     * @param string[] $additionalInfo Additional information (AddtlInf)
     */
    public function __construct(
        StatusReasonCode|string|null $code = null,
        private ?string $proprietary = null,
        private array $additionalInfo = []
    ) {
        if (is_string($code)) {
            $this->code = StatusReasonCode::tryFrom($code);
        } else {
            $this->code = $code;
        }
    }

    /**
     * Creates from an ISO reason code.
     */
    public static function fromCode(StatusReasonCode|string $code, array $additionalInfo = []): self {
        return new self($code, null, $additionalInfo);
    }

    /**
     * Creates from proprietary code.
     */
    public static function fromProprietary(string $proprietary, array $additionalInfo = []): self {
        return new self(null, $proprietary, $additionalInfo);
    }

    public function getCode(): ?StatusReasonCode {
        return $this->code;
    }

    public function getCodeString(): ?string {
        return $this->code?->value;
    }

    public function getProprietary(): ?string {
        return $this->proprietary;
    }

    /**
     * @return string[]
     */
    public function getAdditionalInfo(): array {
        return $this->additionalInfo;
    }

    /**
     * Returns the reason (code or proprietary).
     */
    public function getReason(): ?string {
        return $this->code?->value ?? $this->proprietary;
    }
}
