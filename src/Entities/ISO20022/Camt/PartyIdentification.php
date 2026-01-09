<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PartyIdentification.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\OrganisationIdentification;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\PersonIdentification;
use DateTimeImmutable;

/**
 * Party Identification for ISO 20022.
 * 
 * Represents a party (debtor/creditor) with full identification details.
 * Supports both organisation and person identification.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Camt
 */
final readonly class PartyIdentification {
    private ?string $name;
    private ?string $postalAddressCountry;
    private ?string $postalAddressLine;

    // Organisation identification
    private ?string $bicOrBei;
    private ?GenericIdentification $organisationId;

    // Person identification
    private ?DateTimeImmutable $birthDate;
    private ?string $birthPlace;
    private ?string $birthCountry;
    private ?GenericIdentification $personId;

    public function __construct(
        ?string $name = null,
        ?string $postalAddressCountry = null,
        ?string $postalAddressLine = null,
        ?string $bicOrBei = null,
        ?GenericIdentification $organisationId = null,
        ?DateTimeImmutable $birthDate = null,
        ?string $birthPlace = null,
        ?string $birthCountry = null,
        ?GenericIdentification $personId = null
    ) {
        $this->name = $name;
        $this->postalAddressCountry = $postalAddressCountry;
        $this->postalAddressLine = $postalAddressLine;
        $this->bicOrBei = $bicOrBei;
        $this->organisationId = $organisationId;
        $this->birthDate = $birthDate;
        $this->birthPlace = $birthPlace;
        $this->birthCountry = $birthCountry;
        $this->personId = $personId;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function getPostalAddressCountry(): ?string {
        return $this->postalAddressCountry;
    }

    public function getPostalAddressLine(): ?string {
        return $this->postalAddressLine;
    }

    public function getBicOrBei(): ?string {
        return $this->bicOrBei;
    }

    public function getOrganisationId(): ?GenericIdentification {
        return $this->organisationId;
    }

    /**
     * Get the organisation identification scheme as enum.
     */
    public function getOrganisationIdScheme(): ?OrganisationIdentification {
        return $this->organisationId?->getOrganisationIdentification();
    }

    public function getBirthDate(): ?DateTimeImmutable {
        return $this->birthDate;
    }

    public function getBirthPlace(): ?string {
        return $this->birthPlace;
    }

    public function getBirthCountry(): ?string {
        return $this->birthCountry;
    }

    public function getPersonId(): ?GenericIdentification {
        return $this->personId;
    }

    /**
     * Get the person identification scheme as enum.
     */
    public function getPersonIdScheme(): ?PersonIdentification {
        return $this->personId?->getPersonIdentification();
    }

    /**
     * Check if this is an organisation identification.
     */
    public function isOrganisation(): bool {
        return $this->bicOrBei !== null || $this->organisationId !== null;
    }

    /**
     * Check if this is a person identification.
     */
    public function isPerson(): bool {
        return $this->birthDate !== null || $this->personId !== null;
    }

    /**
     * Get any available identification value.
     */
    public function getAnyId(): ?string {
        if ($this->bicOrBei !== null) {
            return $this->bicOrBei;
        }
        if ($this->organisationId !== null) {
            return $this->organisationId->getId();
        }
        if ($this->personId !== null) {
            return $this->personId->getId();
        }
        return null;
    }

    public function __toString(): string {
        $parts = [];
        if ($this->name !== null) {
            $parts[] = $this->name;
        }
        $id = $this->getAnyId();
        if ($id !== null) {
            $parts[] = "({$id})";
        }
        return implode(' ', $parts) ?: '';
    }
}
