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

namespace CommonToolkit\FinancialFormats\Entities\Mt1\Type102;

use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * MT102 Document - Multiple Customer Credit Transfer.
 * 
 * Batch payment message according to SWIFT standard for sending multiple
 * individual credit transfers to different beneficiaries.
 * 
 * Structure:
 * - Sequence A: General Information (once per message)
 *   - :20:   Sender's Reference
 *   - :23:   Bank Operation Code
 *   - :50a:  Ordering Customer
 *   - :52a:  Ordering Institution
 *   - :26T:  Transaction Type Code (optional)
 *   - :77B:  Regulatory Reporting (optional)
 *   - :71A:  Details of Charges
 *   - :36:   Exchange Rate (optional)
 * 
 * - Sequence B: Transaction Details (repeatable)
 *   - :21:   Transaction Reference
 *   - :32B:  Currency/Amount
 *   - :50a:  Ordering Customer (if different)
 *   - :57a:  Account With Institution
 *   - :59a:  Beneficiary
 *   - :70:   Remittance Information
 *   - :71A:  Details of Charges
 *   - :77B:  Regulatory Reporting
 * 
 * - Sequence C: Summary (once per message)
 *   - :32A:  Value Date/Currency/Amount (Total)
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt1\Type102
 */
class Document {
    private string $sendersReference;
    private string $bankOperationCode;
    private Party $orderingCustomer;
    private ?Party $orderingInstitution;
    private ?string $transactionTypeCode;
    private ?string $regulatoryReporting;
    private ?string $detailsOfCharges;
    private ?float $exchangeRate;
    private DateTimeImmutable $valueDate;
    private CurrencyCode $currency;
    private ?DateTimeImmutable $creationDateTime;

    /** @var Transaction[] */
    private array $transactions = [];

    public function __construct(
        string $sendersReference,
        Party $orderingCustomer,
        DateTimeImmutable $valueDate,
        CurrencyCode $currency,
        array $transactions = [],
        string $bankOperationCode = 'CRED',
        ?Party $orderingInstitution = null,
        ?string $transactionTypeCode = null,
        ?string $regulatoryReporting = null,
        ?string $detailsOfCharges = null,
        ?float $exchangeRate = null,
        ?DateTimeImmutable $creationDateTime = null
    ) {
        $this->sendersReference = $sendersReference;
        $this->orderingCustomer = $orderingCustomer;
        $this->valueDate = $valueDate;
        $this->currency = $currency;
        $this->transactions = $transactions;
        $this->bankOperationCode = $bankOperationCode;
        $this->orderingInstitution = $orderingInstitution;
        $this->transactionTypeCode = $transactionTypeCode;
        $this->regulatoryReporting = $regulatoryReporting;
        $this->detailsOfCharges = $detailsOfCharges;
        $this->exchangeRate = $exchangeRate;
        $this->creationDateTime = $creationDateTime ?? new DateTimeImmutable();
    }

    public function getMtType(): MtType {
        return MtType::MT102;
    }

    /**
     * Returns the sender's reference (field :20:).
     */
    public function getSendersReference(): string {
        return $this->sendersReference;
    }

    /**
     * Returns the Bank Operation Code (Field :23:).
     * Default: CRED (Credit Transfer)
     */
    public function getBankOperationCode(): string {
        return $this->bankOperationCode;
    }

    /**
     * Returns the ordering customer (field :50:).
     */
    public function getOrderingCustomer(): Party {
        return $this->orderingCustomer;
    }

    /**
     * Returns the Ordering Institution (Field :52a:).
     */
    public function getOrderingInstitution(): ?Party {
        return $this->orderingInstitution;
    }

    /**
     * Returns the Transaction Type Code (Field :26T:).
     */
    public function getTransactionTypeCode(): ?string {
        return $this->transactionTypeCode;
    }

    /**
     * Returns the Regulatory Reporting (Field :77B:).
     */
    public function getRegulatoryReporting(): ?string {
        return $this->regulatoryReporting;
    }

    /**
     * Returns the Details of Charges (Field :71A:).
     */
    public function getDetailsOfCharges(): ?string {
        return $this->detailsOfCharges;
    }

    /**
     * Returns the Exchange Rate (Field :36:).
     */
    public function getExchangeRate(): ?float {
        return $this->exchangeRate;
    }

    /**
     * Returns the value date.
     */
    public function getValueDate(): DateTimeImmutable {
        return $this->valueDate;
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
     * Returns the Field :32A: formatted value (Total).
     */
    public function toField32A(): string {
        $amount = number_format($this->getTotalAmount(), 2, ',', '');
        return $this->valueDate->format('ymd') . $this->currency->value . $amount;
    }
}
