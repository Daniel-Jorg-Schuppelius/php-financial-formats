<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : MandateInformation.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8;

use DateTimeImmutable;

/**
 * Mandate related information for pain.008 (DrctDbtTx/MndtRltdInf).
 * 
 * Contains information about the SEPA direct debit mandate.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type8
 */
final readonly class MandateInformation {
    public function __construct(
        private string $mandateId,
        private DateTimeImmutable $dateOfSignature,
        private ?bool $amendmentIndicator = null,
        private ?string $originalMandateId = null,
        private ?string $originalCreditorSchemeId = null,
        private ?string $originalCreditorName = null,
        private ?string $originalDebtorAccount = null,
        private ?string $originalDebtorAgent = null,
        private ?string $electronicSignature = null
    ) {
    }

    /**
     * Factory for simple mandate.
     */
    public static function create(
        string $mandateId,
        DateTimeImmutable $dateOfSignature
    ): self {
        return new self($mandateId, $dateOfSignature);
    }

    /**
     * Factory for amended mandate.
     */
    public static function amended(
        string $mandateId,
        DateTimeImmutable $dateOfSignature,
        ?string $originalMandateId = null,
        ?string $originalCreditorSchemeId = null
    ): self {
        return new self(
            mandateId: $mandateId,
            dateOfSignature: $dateOfSignature,
            amendmentIndicator: true,
            originalMandateId: $originalMandateId,
            originalCreditorSchemeId: $originalCreditorSchemeId
        );
    }

    public function getMandateId(): string {
        return $this->mandateId;
    }

    public function getDateOfSignature(): DateTimeImmutable {
        return $this->dateOfSignature;
    }

    public function getAmendmentIndicator(): ?bool {
        return $this->amendmentIndicator;
    }

    public function getOriginalMandateId(): ?string {
        return $this->originalMandateId;
    }

    public function getOriginalCreditorSchemeId(): ?string {
        return $this->originalCreditorSchemeId;
    }

    public function getOriginalCreditorName(): ?string {
        return $this->originalCreditorName;
    }

    public function getOriginalDebtorAccount(): ?string {
        return $this->originalDebtorAccount;
    }

    public function getOriginalDebtorAgent(): ?string {
        return $this->originalDebtorAgent;
    }

    public function getElectronicSignature(): ?string {
        return $this->electronicSignature;
    }

    /**
     * Checks if the mandate was amended.
     */
    public function isAmended(): bool {
        return $this->amendmentIndicator === true;
    }
}
