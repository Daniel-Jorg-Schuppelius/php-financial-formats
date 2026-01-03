<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : AccountIdentification.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain;

use CommonToolkit\Enums\CurrencyCode;

/**
 * Account identification for pain messages.
 * 
 * Represents an account identification according to ISO 20022.
 * Supports both IBAN and proprietary account numbers.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain
 */
final readonly class AccountIdentification {
    public function __construct(
        private ?string $iban = null,
        private ?string $other = null,
        private ?CurrencyCode $currency = null,
        private ?string $name = null
    ) {
    }

    /**
     * Returns the IBAN.
     */
    public function getIban(): ?string {
        return $this->iban;
    }

    /**
     * Returns the alternative account number.
     */
    public function getOther(): ?string {
        return $this->other;
    }

    /**
     * Returns the currency.
     */
    public function getCurrency(): ?CurrencyCode {
        return $this->currency;
    }

    /**
     * Returns the account name.
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * Returns the account identification (IBAN or Other).
     */
    public function getIdentification(): ?string {
        return $this->iban ?? $this->other;
    }

    /**
     * Checks if this is an IBAN.
     */
    public function isIban(): bool {
        return $this->iban !== null;
    }

    /**
     * Creates an account identification from IBAN.
     */
    public static function fromIban(string $iban, ?CurrencyCode $currency = null): self {
        return new self(iban: $iban, currency: $currency);
    }

    /**
     * Creates an account identification from proprietary account number.
     */
    public static function fromOther(string $other, ?CurrencyCode $currency = null): self {
        return new self(other: $other, currency: $currency);
    }
}
