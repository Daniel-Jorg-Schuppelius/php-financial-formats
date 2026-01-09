<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Document.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Mt1\Type104;

use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * MT104 Document - Direct Debit Message.
 * 
 * Direct debit message according to SWIFT standard for collecting
 * payments from multiple debtors in favor of a creditor.
 * 
 * Structure:
 * - Sequence A: General Information (once per message)
 *   - :20:   Sender's Reference
 *   - :21E:  Mandate Reference (optional)
 *   - :23E:  Instruction Code (optional)
 *   - :26T:  Transaction Type Code (optional)
 *   - :30:   Requested Execution Date
 *   - :50a:  Creditor
 *   - :51A:  Sending Institution (optional)
 *   - :52a:  Creditor's Bank (optional)
 *   - :53a:  Sender's Correspondent (optional)
 *   - :71A:  Details of Charges
 *   - :72:   Sender to Receiver Information (optional)
 *   - :77B:  Regulatory Reporting (optional)
 * 
 * - Sequence B: Transaction Details (repeatable)
 *   - :21:   Transaction Reference
 *   - :21C:  End-to-End Reference (optional)
 *   - :23E:  Instruction Code (optional)
 *   - :32B:  Currency/Amount
 *   - :50a:  Creditor (if different)
 *   - :57a:  Debtor's Bank
 *   - :59a:  Debtor
 *   - :70:   Remittance Information
 *   - :71A:  Details of Charges
 * 
 * - Sequence C: Summary (once per message)
 *   - :32B:  Currency/Amount (Total)
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt1\Type104
 */
class Document {
    private string $sendersReference;
    private ?string $mandateReference;
    private ?string $instructionCode;
    private ?string $transactionTypeCode;
    private DateTimeImmutable $requestedExecutionDate;
    private Party $creditor;
    private ?Party $sendingInstitution;
    private ?Party $creditorsBank;
    private ?Party $sendersCorrespondent;
    private ?string $detailsOfCharges;
    private ?string $senderToReceiverInfo;
    private ?string $regulatoryReporting;
    private CurrencyCode $currency;
    private ?DateTimeImmutable $creationDateTime;

    /** @var Transaction[] */
    private array $transactions = [];

    public function __construct(
        string $sendersReference,
        Party $creditor,
        DateTimeImmutable $requestedExecutionDate,
        CurrencyCode $currency,
        array $transactions = [],
        ?string $mandateReference = null,
        ?string $instructionCode = null,
        ?string $transactionTypeCode = null,
        ?Party $sendingInstitution = null,
        ?Party $creditorsBank = null,
        ?Party $sendersCorrespondent = null,
        ?string $detailsOfCharges = null,
        ?string $senderToReceiverInfo = null,
        ?string $regulatoryReporting = null,
        ?DateTimeImmutable $creationDateTime = null
    ) {
        $this->sendersReference = $sendersReference;
        $this->creditor = $creditor;
        $this->requestedExecutionDate = $requestedExecutionDate;
        $this->currency = $currency;
        $this->transactions = $transactions;
        $this->mandateReference = $mandateReference;
        $this->instructionCode = $instructionCode;
        $this->transactionTypeCode = $transactionTypeCode;
        $this->sendingInstitution = $sendingInstitution;
        $this->creditorsBank = $creditorsBank;
        $this->sendersCorrespondent = $sendersCorrespondent;
        $this->detailsOfCharges = $detailsOfCharges;
        $this->senderToReceiverInfo = $senderToReceiverInfo;
        $this->regulatoryReporting = $regulatoryReporting;
        $this->creationDateTime = $creationDateTime ?? new DateTimeImmutable();
    }

    public function getMtType(): MtType {
        return MtType::MT104;
    }

    /**
     * Returns the sender's reference (field :20:).
     */
    public function getSendersReference(): string {
        return $this->sendersReference;
    }

    /**
     * Returns the Mandate Reference (Field :21E:).
     */
    public function getMandateReference(): ?string {
        return $this->mandateReference;
    }

    /**
     * Returns the Instruction Code (Field :23E:).
     */
    public function getInstructionCode(): ?string {
        return $this->instructionCode;
    }

    /**
     * Returns the Transaction Type Code (Field :26T:).
     */
    public function getTransactionTypeCode(): ?string {
        return $this->transactionTypeCode;
    }

    /**
     * Returns the requested execution date (Field :30:).
     */
    public function getRequestedExecutionDate(): DateTimeImmutable {
        return $this->requestedExecutionDate;
    }

    /**
     * Returns the creditor (field :50:).
     */
    public function getCreditor(): Party {
        return $this->creditor;
    }

    /**
     * Returns the Sending Institution (Field :51A:).
     */
    public function getSendingInstitution(): ?Party {
        return $this->sendingInstitution;
    }

    /**
     * Returns the Creditor's Bank (Field :52a:).
     */
    public function getCreditorsBank(): ?Party {
        return $this->creditorsBank;
    }

    /**
     * Returns the Sender's Correspondent (Field :53a:).
     */
    public function getSendersCorrespondent(): ?Party {
        return $this->sendersCorrespondent;
    }

    /**
     * Returns the Details of Charges (Field :71A:).
     */
    public function getDetailsOfCharges(): ?string {
        return $this->detailsOfCharges;
    }

    /**
     * Returns the Sender to Receiver Information (Field :72:).
     */
    public function getSenderToReceiverInfo(): ?string {
        return $this->senderToReceiverInfo;
    }

    /**
     * Returns the Regulatory Reporting (Field :77B:).
     */
    public function getRegulatoryReporting(): ?string {
        return $this->regulatoryReporting;
    }

    /**
     * Returns the currency.
     */
    public function getCurrency(): CurrencyCode {
        return $this->currency;
    }

    /**
     * Returns the creation datetime.
     */
    public function getCreationDateTime(): ?DateTimeImmutable {
        return $this->creationDateTime;
    }

    /**
     * Returns all transactions (Sequence B).
     * 
     * @return Transaction[]
     */
    public function getTransactions(): array {
        return $this->transactions;
    }

    /**
     * Returns the total amount (sum of all transactions).
     */
    public function getTotalAmount(): float {
        return array_sum(array_map(fn(Transaction $t) => $t->getAmount(), $this->transactions));
    }

    /**
     * Returns the transaction count.
     */
    public function getTransactionCount(): int {
        return count($this->transactions);
    }

    /**
     * Adds a transaction.
     */
    public function addTransaction(Transaction $transaction): self {
        $clone = clone $this;
        $clone->transactions = [...$this->transactions, $transaction];
        return $clone;
    }

    /**
     * Returns the Field :32B: formatted value (Total).
     */
    public function toField32B(): string {
        $amount = number_format($this->getTotalAmount(), 2, ',', '');
        return $this->currency->value . $amount;
    }
}
