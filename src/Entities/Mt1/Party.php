<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Party.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Mt1;

use CommonToolkit\Helper\Data\BankHelper;

/**
 * Partei in einer MT10x-Nachricht.
 * 
 * Represents ordering party (:50:), beneficiary (:59:) or
 * beteiligte Banken (:52:, :53:, :56:, :57:).
 * 
 * Formate:
 * - Option A: BIC-Code (11 Zeichen)
 * - Option K: Name und Adresse (max. 4 Zeilen à 35 Zeichen)
 * - Option H: /Kontonummer + Name und Adresse
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt1
 */
final readonly class Party {
    public function __construct(
        private ?string $account = null,
        private ?string $bic = null,
        private ?string $name = null,
        private ?string $addressLine1 = null,
        private ?string $addressLine2 = null,
        private ?string $addressLine3 = null
    ) {
    }

    /**
     * Returns the account number/IBAN.
     */
    public function getAccount(): ?string {
        return $this->account;
    }

    /**
     * Returns the BIC.
     */
    public function getBic(): ?string {
        return $this->bic;
    }

    /**
     * Returns the name.
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * Returns the first address line.
     */
    public function getAddressLine1(): ?string {
        return $this->addressLine1;
    }

    /**
     * Returns the second address line.
     */
    public function getAddressLine2(): ?string {
        return $this->addressLine2;
    }

    /**
     * Returns the third address line.
     */
    public function getAddressLine3(): ?string {
        return $this->addressLine3;
    }

    /**
     * Returns the complete address as array.
     * 
     * @return string[]
     */
    public function getAddressLines(): array {
        return array_filter([
            $this->addressLine1,
            $this->addressLine2,
            $this->addressLine3,
        ], fn($line) => $line !== null);
    }

    /**
     * Returns the complete address as string.
     */
    public function getFullAddress(): string {
        $lines = $this->getAddressLines();
        if ($this->name !== null) {
            array_unshift($lines, $this->name);
        }
        return implode("\n", $lines);
    }

    /**
     * Checks if the party only has a BIC (Option A).
     */
    public function isBicOnly(): bool {
        return $this->bic !== null && $this->name === null;
    }

    /**
     * Checks if the party has name and address (Option K).
     */
    public function hasNameAddress(): bool {
        return $this->name !== null;
    }

    /**
     * Checks if the party has an account (Option H, F).
     */
    public function hasAccount(): bool {
        return $this->account !== null;
    }

    /**
     * Serialisiert im SWIFT-Format (Option A - nur BIC).
     */
    public function toOptionA(): string {
        return $this->bic ?? '';
    }

    /**
     * Serialisiert im SWIFT-Format (Option K - Name/Adresse).
     */
    public function toOptionK(): string {
        $lines = [];
        if ($this->account !== null) {
            $lines[] = '/' . $this->account;
        }
        if ($this->name !== null) {
            $lines[] = $this->name;
        }
        $lines = array_merge($lines, $this->getAddressLines());
        return implode("\n", $lines);
    }

    /**
     * Serialisiert im SWIFT-Format (Option H - /Kontonummer + Name/Adresse).
     * 
     * Format:
     * /Kontonummer
     * Name
     * Adresszeile 1
     * Adresszeile 2
     */
    public function toOptionH(): string {
        $lines = [];
        if ($this->account !== null) {
            $lines[] = '/' . $this->account;
        }
        if ($this->name !== null) {
            $lines[] = $this->name;
        }
        $lines = array_merge($lines, $this->getAddressLines());
        return implode("\n", $lines);
    }

    /**
     * Parst eine Partei aus einem SWIFT-Feldinhalt.
     */
    public static function fromSwiftField(string $content): self {
        $lines = explode("\n", trim($content));

        $account = null;
        $bic = null;
        $name = null;
        $addressLines = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // Kontonummer beginnt mit /
            if (str_starts_with($line, '/')) {
                $account = substr($line, 1);
                continue;
            }

            // BIC-Erkennung via BankHelper
            if (BankHelper::isBIC($line)) {
                $bic = $line;
                continue;
            }

            // Erste Textzeile ist der Name
            if ($name === null) {
                $name = $line;
            } else {
                $addressLines[] = $line;
            }
        }

        return new self(
            account: $account,
            bic: $bic,
            name: $name,
            addressLine1: $addressLines[0] ?? null,
            addressLine2: $addressLines[1] ?? null,
            addressLine3: $addressLines[2] ?? null
        );
    }

    public function __toString(): string {
        if ($this->isBicOnly()) {
            return $this->toOptionA();
        }
        return $this->toOptionK();
    }
}
