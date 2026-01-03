<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : MandateCopyRequest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type17;

/**
 * Mandate copy request for pain.017.
 * 
 * Einzelne Anfrage zur Erstellung einer Mandatskopie.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type17
 */
final readonly class MandateCopyRequest {
    public function __construct(
        private string $mandateId,
        private ?string $creditorSchemeId = null,
        private ?string $creditorId = null,
        private ?string $debtorId = null,
        private ?bool $includeElectronicSignature = null
    ) {
    }

    public static function create(
        string $mandateId,
        ?string $creditorSchemeId = null
    ): self {
        return new self(
            mandateId: $mandateId,
            creditorSchemeId: $creditorSchemeId
        );
    }

    public function getMandateId(): string {
        return $this->mandateId;
    }

    public function getCreditorSchemeId(): ?string {
        return $this->creditorSchemeId;
    }

    public function getCreditorId(): ?string {
        return $this->creditorId;
    }

    public function getDebtorId(): ?string {
        return $this->debtorId;
    }

    public function includeElectronicSignature(): ?bool {
        return $this->includeElectronicSignature;
    }
}
