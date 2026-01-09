<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : AccountIdentification.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\AccountIdentificationType;

/**
 * Account Identification for ISO 20022.
 * 
 * Represents an account with either IBAN or other identification.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Camt
 */
final readonly class AccountIdentification {
    private ?string $iban;
    private ?GenericIdentification $otherId;

    public function __construct(
        ?string $iban = null,
        ?GenericIdentification $otherId = null
    ) {
        $this->iban = $iban;
        $this->otherId = $otherId;
    }

    public function getIban(): ?string {
        return $this->iban;
    }

    public function getOtherId(): ?GenericIdentification {
        return $this->otherId;
    }

    /**
     * Get the other identification scheme as enum.
     */
    public function getAccountIdentificationType(): ?AccountIdentificationType {
        return $this->otherId?->getAccountIdentificationType();
    }

    /**
     * Check if this is an IBAN account.
     */
    public function isIban(): bool {
        return $this->iban !== null;
    }

    /**
     * Get the primary account identifier (IBAN or other ID).
     */
    public function getId(): string {
        if ($this->iban !== null) {
            return $this->iban;
        }
        return $this->otherId?->getId() ?? '';
    }

    /**
     * Get scheme name if using other identification.
     */
    public function getSchemeName(): ?string {
        if ($this->iban !== null) {
            return 'IBAN';
        }
        return $this->otherId?->getSchemeCode()
            ?? $this->otherId?->getSchemeProprietary();
    }

    public function __toString(): string {
        return $this->getId();
    }
}
