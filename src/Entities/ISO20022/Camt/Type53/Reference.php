<?php
/*
 * Created on   : Thu May 08 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Reference.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53;

/**
 * Represents all references of a CAMT.053 transaction.
 * 
 * Basierend auf den SEPA-Referenzfeldern:
 * - EndToEndId: Ende-zu-Ende-Identifikation (EREF+)
 * - MandateId: Mandatsreferenz (MREF+)
 * - CreditorId: Creditor ID (CRED+)
 * - EntryReference: Umsatzreferenz der Bank
 * - AccountServicerReference: Reference of the account-holding institution
 * - PaymentInformationId: Zahlungsinformations-ID
 * - InstructionId: Auftrags-ID (KREF+)
 * 
 * @package CommonToolkit\Entities\Common\Banking\Camt053
 */
final class Reference {
    private ?string $endToEndId;
    private ?string $mandateId;
    private ?string $creditorId;
    private ?string $entryReference;
    private ?string $accountServicerReference;
    private ?string $paymentInformationId;
    private ?string $instructionId;
    private ?string $additional;

    public function __construct(
        ?string $endToEndId = null,
        ?string $mandateId = null,
        ?string $creditorId = null,
        ?string $entryReference = null,
        ?string $accountServicerReference = null,
        ?string $paymentInformationId = null,
        ?string $instructionId = null,
        ?string $additional = null
    ) {
        $this->endToEndId = $endToEndId;
        $this->mandateId = $mandateId;
        $this->creditorId = $creditorId;
        $this->entryReference = $entryReference;
        $this->accountServicerReference = $accountServicerReference;
        $this->paymentInformationId = $paymentInformationId;
        $this->instructionId = $instructionId;
        $this->additional = $additional;
    }

    /**
     * Ende-zu-Ende-Identifikation (EREF+).
     * Vom Auftraggeber vergebene eindeutige Referenz.
     */
    public function getEndToEndId(): ?string {
        return $this->endToEndId;
    }

    /**
     * Mandatsreferenz (MREF+).
     * Nur bei SEPA-Lastschriften.
     */
    public function getMandateId(): ?string {
        return $this->mandateId;
    }

    /**
     * Creditor identification (CRED+).
     * Nur bei SEPA-Lastschriften.
     */
    public function getCreditorId(): ?string {
        return $this->creditorId;
    }

    /**
     * Transaction reference of the account-holding institution.
     */
    public function getEntryReference(): ?string {
        return $this->entryReference;
    }

    /**
     * Reference of the account-holding institution.
     * Entspricht Subfeld 10 im MT940 :61:-Feld.
     */
    public function getAccountServicerReference(): ?string {
        return $this->accountServicerReference;
    }

    /**
     * Zahlungsinformations-ID.
     */
    public function getPaymentInformationId(): ?string {
        return $this->paymentInformationId;
    }

    /**
     * Auftrags-ID (KREF+).
     */
    public function getInstructionId(): ?string {
        return $this->instructionId;
    }

    /**
     * Additional reference.
     */
    public function getAdditional(): ?string {
        return $this->additional;
    }

    /**
     * Checks if at least one reference is set.
     */
    public function hasAnyReference(): bool {
        return $this->endToEndId !== null
            || $this->mandateId !== null
            || $this->creditorId !== null
            || $this->entryReference !== null
            || $this->accountServicerReference !== null
            || $this->paymentInformationId !== null
            || $this->instructionId !== null
            || $this->additional !== null;
    }

    /**
     * Returns the primary reference (first found).
     */
    public function getPrimaryReference(): ?string {
        return $this->endToEndId
            ?? $this->accountServicerReference
            ?? $this->entryReference
            ?? $this->mandateId
            ?? $this->instructionId
            ?? $this->additional;
    }

    public function __toString(): string {
        $parts = array_filter([
            $this->endToEndId ? "EREF+{$this->endToEndId}" : null,
            $this->mandateId ? "MREF+{$this->mandateId}" : null,
            $this->creditorId ? "CRED+{$this->creditorId}" : null,
            $this->instructionId ? "KREF+{$this->instructionId}" : null,
            $this->additional,
        ]);

        return implode(' ', $parts);
    }
}
