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
 * Financial Institution Identification für pain-Nachrichten.
 * 
 * Repräsentiert eine Bank/Finanzinstitution gemäß ISO 20022.
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
     * Gibt den BIC zurück (BICFI).
     */
    public function getBic(): ?string {
        return $this->bic;
    }

    /**
     * Gibt den Namen zurück (Nm).
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * Gibt die Postadresse zurück (PstlAdr).
     */
    public function getPostalAddress(): ?PostalAddress {
        return $this->postalAddress;
    }

    /**
     * Gibt die Clearing-System-ID zurück (ClrSysId).
     */
    public function getClearingSystemId(): ?string {
        return $this->clearingSystemId;
    }

    /**
     * Gibt die Member-ID zurück (MmbId).
     */
    public function getMemberId(): ?string {
        return $this->memberId;
    }

    /**
     * Gibt die LEI zurück.
     */
    public function getLei(): ?string {
        return $this->lei;
    }

    /**
     * Prüft ob die Institution gültig ist (mindestens BIC oder Name).
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
