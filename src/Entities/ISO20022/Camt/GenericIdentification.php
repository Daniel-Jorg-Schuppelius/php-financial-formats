<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : GenericIdentification.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\AccountIdentificationType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\ClearingSystemIdentification;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\OrganisationIdentification;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\PersonIdentification;

/**
 * Generic Identification for ISO 20022.
 * 
 * Represents a generic identification with scheme name.
 * Used for:
 * - GenericAccountIdentification1 (Account identification when not IBAN)
 * - GenericOrganisationIdentification1 (Organisation identification)
 * - GenericPersonIdentification1 (Person identification)
 * - ClearingSystemMemberIdentification2 (Clearing system member)
 * 
 * @package CommonToolkit\Entities\Common\Banking\Camt
 */
final readonly class GenericIdentification {
    private string $id;
    private ?string $schemeCode;
    private ?string $schemeProprietary;
    private ?string $issuer;

    /**
     * @param string $id The identification value
     * @param string|null $schemeCode ISO 20022 scheme code (from codelist)
     * @param string|null $schemeProprietary Proprietary scheme name
     * @param string|null $issuer Issuer of the identification
     */
    public function __construct(
        string $id,
        ?string $schemeCode = null,
        ?string $schemeProprietary = null,
        ?string $issuer = null
    ) {
        $this->id = $id;
        $this->schemeCode = $schemeCode;
        $this->schemeProprietary = $schemeProprietary;
        $this->issuer = $issuer;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getSchemeCode(): ?string {
        return $this->schemeCode;
    }

    public function getSchemeProprietary(): ?string {
        return $this->schemeProprietary;
    }

    public function getIssuer(): ?string {
        return $this->issuer;
    }

    /**
     * Get scheme code as AccountIdentificationType enum.
     */
    public function getAccountIdentificationType(): ?AccountIdentificationType {
        return $this->schemeCode !== null
            ? AccountIdentificationType::tryFrom($this->schemeCode)
            : null;
    }

    /**
     * Get scheme code as ClearingSystemIdentification enum.
     */
    public function getClearingSystemIdentification(): ?ClearingSystemIdentification {
        return $this->schemeCode !== null
            ? ClearingSystemIdentification::tryFrom($this->schemeCode)
            : null;
    }

    /**
     * Get scheme code as OrganisationIdentification enum.
     */
    public function getOrganisationIdentification(): ?OrganisationIdentification {
        return $this->schemeCode !== null
            ? OrganisationIdentification::tryFrom($this->schemeCode)
            : null;
    }

    /**
     * Get scheme code as PersonIdentification enum.
     */
    public function getPersonIdentification(): ?PersonIdentification {
        return $this->schemeCode !== null
            ? PersonIdentification::tryFrom($this->schemeCode)
            : null;
    }

    /**
     * Check if scheme is from codelist or proprietary.
     */
    public function hasCodelistScheme(): bool {
        return $this->schemeCode !== null;
    }

    public function hasProprietaryScheme(): bool {
        return $this->schemeProprietary !== null;
    }

    public function __toString(): string {
        $parts = [$this->id];
        if ($this->schemeCode !== null) {
            $parts[] = "({$this->schemeCode})";
        } elseif ($this->schemeProprietary !== null) {
            $parts[] = "({$this->schemeProprietary})";
        }
        return implode(' ', $parts);
    }
}
