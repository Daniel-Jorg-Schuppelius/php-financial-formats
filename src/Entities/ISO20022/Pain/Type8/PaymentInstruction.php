<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PaymentInstruction.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Enums\Mt\ChargesCode;
use CommonToolkit\FinancialFormats\Enums\Pain\LocalInstrument;
use CommonToolkit\FinancialFormats\Enums\Pain\PaymentMethod;
use CommonToolkit\FinancialFormats\Enums\Pain\SequenceType;
use DateTimeImmutable;

/**
 * Payment instruction for pain.008 (PmtInf).
 * 
 * Groups direct debits with common creditor data:
 * - PmtInfId: Payment Instruction ID
 * - PmtMtd: Zahlungsmethode (DD=Lastschrift)
 * - Cdtr: Creditor
 * - CdtrAcct: Creditor account
 * - CdtrAgt: Creditor bank
 * - DrctDbtTxInf: Einzelne Lastschriften
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type8
 */
final class PaymentInstruction {
    /** @var DirectDebitTransaction[] */
    private array $transactions = [];

    public function __construct(
        private readonly string $paymentInstructionId,
        private readonly PaymentMethod $paymentMethod,
        private readonly DateTimeImmutable $requestedCollectionDate,
        private readonly PartyIdentification $creditor,
        private readonly AccountIdentification $creditorAccount,
        private readonly FinancialInstitution $creditorAgent,
        array $transactions = [],
        private readonly ?string $creditorSchemeId = null,
        private readonly ?bool $batchBooking = null,
        private readonly ?ChargesCode $chargeBearer = null,
        private readonly ?SequenceType $sequenceType = null,
        private readonly ?LocalInstrument $localInstrument = null,
        private readonly ?string $serviceLevel = null,
        private readonly ?string $categoryPurpose = null
    ) {
        $this->transactions = $transactions;
    }

    /**
     * Factory for SEPA Core direct debit.
     */
    public static function sepaCore(
        string $paymentInstructionId,
        DateTimeImmutable $collectionDate,
        string $creditorName,
        string $creditorIban,
        string $creditorBic,
        string $creditorSchemeId,
        SequenceType $sequenceType,
        array $transactions = []
    ): self {
        return new self(
            paymentInstructionId: $paymentInstructionId,
            paymentMethod: PaymentMethod::DIRECT_DEBIT,
            requestedCollectionDate: $collectionDate,
            creditor: new PartyIdentification(name: $creditorName),
            creditorAccount: new AccountIdentification(iban: $creditorIban),
            creditorAgent: new FinancialInstitution(bic: $creditorBic),
            transactions: $transactions,
            creditorSchemeId: $creditorSchemeId,
            chargeBearer: ChargesCode::SLEV,
            sequenceType: $sequenceType,
            localInstrument: LocalInstrument::SEPA_CORE,
            serviceLevel: 'SEPA'
        );
    }

    /**
     * Factory for SEPA B2B direct debit.
     */
    public static function sepaB2B(
        string $paymentInstructionId,
        DateTimeImmutable $collectionDate,
        string $creditorName,
        string $creditorIban,
        string $creditorBic,
        string $creditorSchemeId,
        SequenceType $sequenceType,
        array $transactions = []
    ): self {
        return new self(
            paymentInstructionId: $paymentInstructionId,
            paymentMethod: PaymentMethod::DIRECT_DEBIT,
            requestedCollectionDate: $collectionDate,
            creditor: new PartyIdentification(name: $creditorName),
            creditorAccount: new AccountIdentification(iban: $creditorIban),
            creditorAgent: new FinancialInstitution(bic: $creditorBic),
            transactions: $transactions,
            creditorSchemeId: $creditorSchemeId,
            chargeBearer: ChargesCode::SLEV,
            sequenceType: $sequenceType,
            localInstrument: LocalInstrument::SEPA_B2B,
            serviceLevel: 'SEPA'
        );
    }

    public function getPaymentInstructionId(): string {
        return $this->paymentInstructionId;
    }

    public function getPaymentMethod(): PaymentMethod {
        return $this->paymentMethod;
    }

    public function getRequestedCollectionDate(): DateTimeImmutable {
        return $this->requestedCollectionDate;
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

    public function getCreditorSchemeId(): ?string {
        return $this->creditorSchemeId;
    }

    public function getBatchBooking(): ?bool {
        return $this->batchBooking;
    }

    public function getChargeBearer(): ?ChargesCode {
        return $this->chargeBearer;
    }

    public function getSequenceType(): ?SequenceType {
        return $this->sequenceType;
    }

    public function getLocalInstrument(): ?LocalInstrument {
        return $this->localInstrument;
    }

    public function getServiceLevel(): ?string {
        return $this->serviceLevel;
    }

    public function getCategoryPurpose(): ?string {
        return $this->categoryPurpose;
    }

    /**
     * @return DirectDebitTransaction[]
     */
    public function getTransactions(): array {
        return $this->transactions;
    }

    /**
     * Adds a transaction.
     */
    public function addTransaction(DirectDebitTransaction $transaction): self {
        $clone = clone $this;
        $clone->transactions[] = $transaction;
        return $clone;
    }

    /**
     * Berechnet die Kontrollsumme.
     */
    public function calculateControlSum(): float {
        return array_sum(array_map(
            fn($t) => $t->getAmount(),
            $this->transactions
        ));
    }

    /**
     * Counts the transactions.
     */
    public function countTransactions(): int {
        return count($this->transactions);
    }
}
