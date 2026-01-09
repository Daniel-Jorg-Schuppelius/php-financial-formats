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

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type1;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Enums\Mt\ChargesCode;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\PaymentMethod;
use DateTimeImmutable;

/**
 * Payment instruction for pain.001 messages (PmtInf).
 * 
 * Groups transfers with common debtor data:
 * - PmtInfId: Payment Instruction ID
 * - PmtMtd: Payment method (TRF=transfer, CHK=cheque)
 * - Dbtr: Auftraggeber (Debtor)
 * - DbtrAcct: Konto des Auftraggebers
 * - DbtrAgt: Bank des Auftraggebers
 * - CdtTrfTxInf: Individual transfers
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type1
 */
final class PaymentInstruction {
    /** @var CreditTransferTransaction[] */
    private array $transactions = [];

    public function __construct(
        private readonly string $paymentInstructionId,
        private readonly PaymentMethod $paymentMethod,
        private readonly DateTimeImmutable $requestedExecutionDate,
        private readonly PartyIdentification $debtor,
        private readonly AccountIdentification $debtorAccount,
        private readonly FinancialInstitution $debtorAgent,
        array $transactions = [],
        private readonly ?bool $batchBooking = null,
        private readonly ?ChargesCode $chargeBearer = null,
        private readonly ?PartyIdentification $ultimateDebtor = null,
        private readonly ?string $serviceLevel = null,
        private readonly ?string $localInstrument = null,
        private readonly ?string $categoryPurpose = null
    ) {
        $this->transactions = $transactions;
    }

    /**
     * Returns the payment instruction ID (PmtInfId).
     */
    public function getPaymentInstructionId(): string {
        return $this->paymentInstructionId;
    }

    /**
     * Returns the payment method (PmtMtd).
     */
    public function getPaymentMethod(): PaymentMethod {
        return $this->paymentMethod;
    }

    /**
     * Returns the requested execution date (ReqdExctnDt).
     */
    public function getRequestedExecutionDate(): DateTimeImmutable {
        return $this->requestedExecutionDate;
    }

    /**
     * Returns the debtor (Dbtr).
     */
    public function getDebtor(): PartyIdentification {
        return $this->debtor;
    }

    /**
     * Returns the debtor account (DbtrAcct).
     */
    public function getDebtorAccount(): AccountIdentification {
        return $this->debtorAccount;
    }

    /**
     * Returns the debtor bank (DbtrAgt).
     */
    public function getDebtorAgent(): FinancialInstitution {
        return $this->debtorAgent;
    }

    /**
     * Returns the transactions (CdtTrfTxInf).
     * @return CreditTransferTransaction[]
     */
    public function getTransactions(): array {
        return $this->transactions;
    }

    /**
     * Returns the batch booking option (BtchBookg).
     * true = Sammelbuchung, false = Einzelbuchung pro Transaktion
     */
    public function getBatchBooking(): ?bool {
        return $this->batchBooking;
    }

    /**
     * Returns the charge bearer (ChrgBr).
     */
    public function getChargeBearer(): ?ChargesCode {
        return $this->chargeBearer;
    }

    /**
     * Returns the ultimate debtor (UltmtDbtr).
     */
    public function getUltimateDebtor(): ?PartyIdentification {
        return $this->ultimateDebtor;
    }

    /**
     * Returns the service level (SvcLvl/Cd).
     * E.g. "SEPA" for SEPA transfers.
     */
    public function getServiceLevel(): ?string {
        return $this->serviceLevel;
    }

    /**
     * Returns the local instrument (LclInstrm/Cd).
     */
    public function getLocalInstrument(): ?string {
        return $this->localInstrument;
    }

    /**
     * Returns the category purpose (CtgyPurp/Cd).
     * E.g. "SALA" for salary payment.
     */
    public function getCategoryPurpose(): ?string {
        return $this->categoryPurpose;
    }

    /**
     * Adds a transaction.
     */
    public function addTransaction(CreditTransferTransaction $transaction): void {
        $this->transactions[] = $transaction;
    }

    /**
     * Returns the number of transactions.
     */
    public function countTransactions(): int {
        return count($this->transactions);
    }

    /**
     * Berechnet die Kontrollsumme aller Transaktionen.
     */
    public function calculateControlSum(): float {
        return array_reduce(
            $this->transactions,
            fn(float $sum, CreditTransferTransaction $txn) => $sum + $txn->getAmount(),
            0.0
        );
    }

    /**
     * Erstellt eine SEPA-Payment-Instruction mit String-Parametern.
     */
    public static function sepa(
        string $paymentInstructionId,
        DateTimeImmutable $executionDate,
        string $debtorName,
        string $debtorIban,
        string $debtorBic,
        array $transactions = []
    ): self {
        return new self(
            paymentInstructionId: $paymentInstructionId,
            paymentMethod: PaymentMethod::TRANSFER,
            requestedExecutionDate: $executionDate,
            debtor: PartyIdentification::fromName($debtorName),
            debtorAccount: AccountIdentification::fromIban($debtorIban),
            debtorAgent: FinancialInstitution::fromBic($debtorBic),
            transactions: $transactions,
            batchBooking: true,
            chargeBearer: ChargesCode::SLEV,
            serviceLevel: 'SEPA'
        );
    }

    /**
     * Erstellt eine SEPA-Payment-Instruction mit Entity-Objekten.
     */
    public static function sepaFromEntities(
        string $paymentInstructionId,
        PartyIdentification $debtor,
        AccountIdentification $debtorAccount,
        FinancialInstitution $debtorAgent,
        array $transactions = [],
        ?DateTimeImmutable $executionDate = null
    ): self {
        return new self(
            paymentInstructionId: $paymentInstructionId,
            paymentMethod: PaymentMethod::TRANSFER,
            requestedExecutionDate: $executionDate ?? new DateTimeImmutable(),
            debtor: $debtor,
            debtorAccount: $debtorAccount,
            debtorAgent: $debtorAgent,
            transactions: $transactions,
            batchBooking: true,
            chargeBearer: ChargesCode::SLEV,
            serviceLevel: 'SEPA'
        );
    }
}
