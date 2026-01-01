<?php
/*
 * Created on   : Tue Dec 31 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : UnableToApplyReason.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Camt\Type26;

/**
 * CAMT.026 Unable to Apply Reason.
 * 
 * Enthält die Gründe, warum eine Zahlung nicht verarbeitet werden konnte.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type26
 */
class UnableToApplyReason {
    private ?string $reasonCode;
    private ?string $reasonProprietary;
    private ?string $additionalInformation;
    private ?string $missingInformationType;
    private ?string $incorrectInformationType;

    public function __construct(
        ?string $reasonCode = null,
        ?string $reasonProprietary = null,
        ?string $additionalInformation = null,
        ?string $missingInformationType = null,
        ?string $incorrectInformationType = null
    ) {
        $this->reasonCode = $reasonCode;
        $this->reasonProprietary = $reasonProprietary;
        $this->additionalInformation = $additionalInformation;
        $this->missingInformationType = $missingInformationType;
        $this->incorrectInformationType = $incorrectInformationType;
    }

    public function getReasonCode(): ?string {
        return $this->reasonCode;
    }

    public function getReasonProprietary(): ?string {
        return $this->reasonProprietary;
    }

    public function getReason(): ?string {
        return $this->reasonCode ?? $this->reasonProprietary;
    }

    public function getAdditionalInformation(): ?string {
        return $this->additionalInformation;
    }

    public function getMissingInformationType(): ?string {
        return $this->missingInformationType;
    }

    public function getIncorrectInformationType(): ?string {
        return $this->incorrectInformationType;
    }

    /**
     * Prüft ob es sich um fehlende Informationen handelt.
     */
    public function isMissingInformation(): bool {
        return $this->missingInformationType !== null;
    }

    /**
     * Prüft ob es sich um fehlerhafte Informationen handelt.
     */
    public function isIncorrectInformation(): bool {
        return $this->incorrectInformationType !== null;
    }
}
