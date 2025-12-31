<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mandate.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Pain\Mandate;

use CommonToolkit\FinancialFormats\Entities\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Enums\LocalInstrument;
use CommonToolkit\FinancialFormats\Enums\SequenceType;
use DateTimeImmutable;

/**
 * Mandat für SEPA-Lastschriften.
 * 
 * Verwendet in pain.009 (Initiation), pain.010 (Amendment), 
 * pain.011 (Cancellation), pain.012 (Acceptance).
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Mandate
 */
final readonly class Mandate {
    public function __construct(
        private string $mandateId,
        private DateTimeImmutable $dateOfSignature,
        private PartyIdentification $creditor,
        private AccountIdentification $creditorAccount,
        private FinancialInstitution $creditorAgent,
        private PartyIdentification $debtor,
        private AccountIdentification $debtorAccount,
        private FinancialInstitution $debtorAgent,
        private ?string $creditorSchemeId = null,
        private ?LocalInstrument $localInstrument = null,
        private ?SequenceType $sequenceType = null,
        private ?DateTimeImmutable $finalCollectionDate = null,
        private ?DateTimeImmutable $firstCollectionDate = null,
        private ?float $maxAmount = null,
        private ?string $electronicSignature = null,
        private ?string $mandateReason = null
    ) {
    }

    /**
     * Factory für SEPA Core Mandat.
     */
    public static function sepaCore(
        string $mandateId,
        DateTimeImmutable $dateOfSignature,
        string $creditorName,
        string $creditorIban,
        string $creditorBic,
        string $creditorSchemeId,
        string $debtorName,
        string $debtorIban,
        string $debtorBic
    ): self {
        return new self(
            mandateId: $mandateId,
            dateOfSignature: $dateOfSignature,
            creditor: new PartyIdentification(name: $creditorName),
            creditorAccount: new AccountIdentification(iban: $creditorIban),
            creditorAgent: new FinancialInstitution(bic: $creditorBic),
            debtor: new PartyIdentification(name: $debtorName),
            debtorAccount: new AccountIdentification(iban: $debtorIban),
            debtorAgent: new FinancialInstitution(bic: $debtorBic),
            creditorSchemeId: $creditorSchemeId,
            localInstrument: LocalInstrument::SEPA_CORE
        );
    }

    /**
     * Factory für SEPA B2B Mandat.
     */
    public static function sepaB2B(
        string $mandateId,
        DateTimeImmutable $dateOfSignature,
        string $creditorName,
        string $creditorIban,
        string $creditorBic,
        string $creditorSchemeId,
        string $debtorName,
        string $debtorIban,
        string $debtorBic
    ): self {
        return new self(
            mandateId: $mandateId,
            dateOfSignature: $dateOfSignature,
            creditor: new PartyIdentification(name: $creditorName),
            creditorAccount: new AccountIdentification(iban: $creditorIban),
            creditorAgent: new FinancialInstitution(bic: $creditorBic),
            debtor: new PartyIdentification(name: $debtorName),
            debtorAccount: new AccountIdentification(iban: $debtorIban),
            debtorAgent: new FinancialInstitution(bic: $debtorBic),
            creditorSchemeId: $creditorSchemeId,
            localInstrument: LocalInstrument::SEPA_B2B
        );
    }

    public function getMandateId(): string {
        return $this->mandateId;
    }

    public function getDateOfSignature(): DateTimeImmutable {
        return $this->dateOfSignature;
    }

    public function getCreditor(): PartyIdentification {
        return $this->creditor;
    }

    public function getCreditorAccount(): AccountIdentification {
        return $this->creditorAccount;
    }

    public function getCreditorAgent(): FinancialInstitution {
        return $this->creditorAgent;
    }

    public function getDebtor(): PartyIdentification {
        return $this->debtor;
    }

    public function getDebtorAccount(): AccountIdentification {
        return $this->debtorAccount;
    }

    public function getDebtorAgent(): FinancialInstitution {
        return $this->debtorAgent;
    }

    public function getCreditorSchemeId(): ?string {
        return $this->creditorSchemeId;
    }

    public function getLocalInstrument(): ?LocalInstrument {
        return $this->localInstrument;
    }

    public function getSequenceType(): ?SequenceType {
        return $this->sequenceType;
    }

    public function getFinalCollectionDate(): ?DateTimeImmutable {
        return $this->finalCollectionDate;
    }

    public function getFirstCollectionDate(): ?DateTimeImmutable {
        return $this->firstCollectionDate;
    }

    public function getMaxAmount(): ?float {
        return $this->maxAmount;
    }

    public function getElectronicSignature(): ?string {
        return $this->electronicSignature;
    }

    public function getMandateReason(): ?string {
        return $this->mandateReason;
    }

    public function isB2B(): bool {
        return $this->localInstrument === LocalInstrument::SEPA_B2B;
    }

    public function isCore(): bool {
        return $this->localInstrument?->isCore() ?? false;
    }
}
