<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : FinancialInstitutionIdentification.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\ClearingSystemIdentification;

/**
 * Financial Institution Identification for ISO 20022.
 * 
 * Represents a financial institution with various identification methods.
 * Supports BIC, Clearing System Member ID, and generic identification.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Camt
 */
final readonly class FinancialInstitutionIdentification {
    private ?string $bic;
    private ?string $clearingSystemCode;
    private ?string $clearingSystemProprietary;
    private ?string $clearingMemberId;
    private ?string $name;
    private ?GenericIdentification $otherId;

    public function __construct(
        ?string $bic = null,
        ?string $clearingSystemCode = null,
        ?string $clearingSystemProprietary = null,
        ?string $clearingMemberId = null,
        ?string $name = null,
        ?GenericIdentification $otherId = null
    ) {
        $this->bic = $bic;
        $this->clearingSystemCode = $clearingSystemCode;
        $this->clearingSystemProprietary = $clearingSystemProprietary;
        $this->clearingMemberId = $clearingMemberId;
        $this->name = $name;
        $this->otherId = $otherId;
    }

    public function getBic(): ?string {
        return $this->bic;
    }

    public function getClearingSystemCode(): ?string {
        return $this->clearingSystemCode;
    }

    /**
     * Get clearing system code as enum.
     */
    public function getClearingSystemIdentification(): ?ClearingSystemIdentification {
        return $this->clearingSystemCode !== null
            ? ClearingSystemIdentification::tryFrom($this->clearingSystemCode)
            : null;
    }

    public function getClearingSystemProprietary(): ?string {
        return $this->clearingSystemProprietary;
    }

    public function getClearingMemberId(): ?string {
        return $this->clearingMemberId;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function getOtherId(): ?GenericIdentification {
        return $this->otherId;
    }

    /**
     * Check if BIC is available.
     */
    public function hasBic(): bool {
        return $this->bic !== null;
    }

    /**
     * Check if clearing system identification is available.
     */
    public function hasClearingSystem(): bool {
        return $this->clearingSystemCode !== null || $this->clearingSystemProprietary !== null;
    }

    /**
     * Get the primary identifier (BIC preferred, then clearing member, then other).
     */
    public function getPrimaryId(): ?string {
        if ($this->bic !== null) {
            return $this->bic;
        }
        if ($this->clearingMemberId !== null) {
            return $this->clearingMemberId;
        }
        return $this->otherId?->getId();
    }

    public function __toString(): string {
        $parts = [];
        if ($this->name !== null) {
            $parts[] = $this->name;
        }
        $id = $this->getPrimaryId();
        if ($id !== null) {
            $parts[] = "({$id})";
        }
        return implode(' ', $parts) ?: '';
    }
}
