<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : FinancialInstitution.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain;

/**
 * Financial institution identification for pain messages.
 * 
 * Represents a bank/financial institution according to ISO 20022.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain
 */
final readonly class FinancialInstitution {
    public function __construct(
        private ?string $bic = null,
        private ?string $name = null,
        private ?PostalAddress $postalAddress = null,
        private ?string $clearingSystemId = null,
        private ?string $memberId = null,
        private ?string $lei = null
    ) {
    }

    /**
     * Returns the BIC (BICFI).
     */
    public function getBic(): ?string {
        return $this->bic;
    }

    /**
     * Returns the name (Nm).
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * Returns the postal address (PstlAdr).
     */
    public function getPostalAddress(): ?PostalAddress {
        return $this->postalAddress;
    }

    /**
     * Returns the clearing system ID (ClrSysId).
     */
    public function getClearingSystemId(): ?string {
        return $this->clearingSystemId;
    }

    /**
     * Returns the member ID (MmbId).
     */
    public function getMemberId(): ?string {
        return $this->memberId;
    }

    /**
     * Returns the LEI.
     */
    public function getLei(): ?string {
        return $this->lei;
    }

    /**
     * Checks if the institution is valid (at least BIC or name).
     */
    public function isValid(): bool {
        return $this->bic !== null || $this->name !== null;
    }

    /**
     * Erstellt eine Institution aus BIC.
     */
    public static function fromBic(string $bic): self {
        return new self(bic: $bic);
    }

    /**
     * Erstellt eine Institution aus Name und BIC.
     */
    public static function fromNameAndBic(string $name, string $bic): self {
        return new self(bic: $bic, name: $name);
    }
}
