<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PartyIdentification.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain;

use CommonToolkit\Enums\CountryCode;

/**
 * Party identification for pain messages.
 * 
 * Represents a party (Debtor, Creditor, Initiating Party) 
 * according to ISO 20022 PartyIdentification schema.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain
 */
final readonly class PartyIdentification {
    public function __construct(
        private ?string $name = null,
        private ?PostalAddress $postalAddress = null,
        private ?string $organisationId = null,
        private ?string $privateId = null,
        private ?string $bic = null,
        private ?string $lei = null,
        private ?CountryCode $countryOfResidence = null
    ) {
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
     * Returns the organization ID (OrgId).
     */
    public function getOrganisationId(): ?string {
        return $this->organisationId;
    }

    /**
     * Returns the private ID (PrvtId).
     */
    public function getPrivateId(): ?string {
        return $this->privateId;
    }

    /**
     * Returns the BIC (AnyBIC).
     */
    public function getBic(): ?string {
        return $this->bic;
    }

    /**
     * Returns the LEI (Legal Entity Identifier).
     */
    public function getLei(): ?string {
        return $this->lei;
    }

    /**
     * Returns the country of residence (CtryOfRes).
     */
    public function getCountryOfResidence(): ?CountryCode {
        return $this->countryOfResidence;
    }

    /**
     * Checks if the party is valid (at least name or ID).
     */
    public function isValid(): bool {
        return $this->name !== null
            || $this->organisationId !== null
            || $this->privateId !== null;
    }

    /**
     * Creates a simple party with name only.
     */
    public static function fromName(string $name): self {
        return new self(name: $name);
    }

    /**
     * Creates a party with name and BIC.
     */
    public static function fromNameAndBic(string $name, string $bic): self {
        return new self(name: $name, bic: $bic);
    }
}
