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

/**
 * Status reason information for pain.002 (StsRsnInf).
 * 
 * Contains details about the status of a transaction.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type2
 */
final readonly class StatusReason {
    /**
     * @param string|null $code Reason Code (Rsn/Cd)
     * @param string|null $proprietary Proprietary Reason (Rsn/Prtry)
     * @param string[] $additionalInfo Additional information (AddtlInf)
     */
    public function __construct(
        private ?string $code = null,
        private ?string $proprietary = null,
        private array $additionalInfo = []
    ) {
    }

    /**
     * Creates from an ISO reason code.
     */
    public static function fromCode(string $code, array $additionalInfo = []): self {
        return new self($code, null, $additionalInfo);
    }

    /**
     * Creates from proprietary code.
     */
    public static function fromProprietary(string $proprietary, array $additionalInfo = []): self {
        return new self(null, $proprietary, $additionalInfo);
    }

    public function getCode(): ?string {
        return $this->code;
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
        return $this->code ?? $this->proprietary;
    }

    /**
     * Versucht den Code als StatusReasonCode zu interpretieren.
     */
    public function getReasonCode(): ?StatusReasonCode {
        if ($this->code === null) {
            return null;
        }
        return StatusReasonCode::tryFrom($this->code);
    }
}
