<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CancellationReason.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type11;

use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\StatusReasonCode;

/**
 * Cancellation reason for pain.011.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type11
 */
final readonly class CancellationReason {
    private ?StatusReasonCode $code;

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

    public static function customerRequest(): self {
        return new self(StatusReasonCode::CUST, null, ['Kundenanfrage']);
    }

    public static function accountClosed(): self {
        return new self(StatusReasonCode::AC01, null, ['Konto geschlossen']);
    }

    public static function debtorDeceased(): self {
        return new self(StatusReasonCode::MD07, null, ['Schuldner verstorben']);
    }

    public static function fraudulent(): self {
        return new self(StatusReasonCode::FRAD, null, ['Betrugsverdacht']);
    }

    public static function fromCode(StatusReasonCode|string $code, ?string $additionalInfo = null): self {
        return new self($code, null, $additionalInfo !== null ? [$additionalInfo] : []);
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

    public function getAdditionalInfo(): array {
        return $this->additionalInfo;
    }

    public function getReason(): ?string {
        return $this->code?->value ?? $this->proprietary;
    }
}
