<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Document.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Mt1\Type101;

use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt101Generator;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * MT101 Document - Request for Transfer.
 * 
 * Batch transfer according to SWIFT standard. Enables sending
 * multiple payment orders in one message.
 * 
 * Struktur:
 * - Sequence A: Allgemeine Informationen (einmal pro Nachricht)
 *   - :20:   Sender's Reference
 *   - :21R:  Customer Specified Reference (optional)
 *   - :28D:  Message Index/Total
 *   - :50:   Ordering Customer
 *   - :52a:  Ordering Institution
 *   - :30:   Requested Execution Date
 * 
 * - Sequence B: Transaktionsdetails (wiederholbar)
 *   - :21:   Transaction Reference
 *   - :32B:  Currency/Amount
 *   - :57a:  Account With Institution
 *   - :59a:  Beneficiary
 *   - :70:   Remittance Information
 *   - :71A:  Details of Charges
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt1\Type101
 */
class Document {
    private string $sendersReference;
    private ?string $customerReference;
    private string $messageIndex;
    private Party $orderingCustomer;
    private ?Party $orderingInstitution;
    private DateTimeImmutable $requestedExecutionDate;
    private ?DateTimeImmutable $creationDateTime;

    /** @var Transaction[] */
    private array $transactions = [];

    public function __construct(
        string $sendersReference,
        Party $orderingCustomer,
        DateTimeImmutable $requestedExecutionDate,
        array $transactions = [],
        ?Party $orderingInstitution = null,
        ?string $customerReference = null,
        string $messageIndex = '1/1',
        ?DateTimeImmutable $creationDateTime = null
    ) {
        $this->sendersReference = $sendersReference;
        $this->orderingCustomer = $orderingCustomer;
        $this->requestedExecutionDate = $requestedExecutionDate;
        $this->transactions = $transactions;
        $this->orderingInstitution = $orderingInstitution;
        $this->customerReference = $customerReference;
        $this->messageIndex = $messageIndex;
        $this->creationDateTime = $creationDateTime ?? new DateTimeImmutable();
    }

    public function getMtType(): MtType {
        return MtType::MT101;
    }

    /**
     * Returns the sender's reference (field :20:).
     */
    public function getSendersReference(): string {
        return $this->sendersReference;
    }

    /**
     * Returns the Customer Specified Reference (Field :21R:).
     */
    public function getCustomerReference(): ?string {
        return $this->customerReference;
    }

    /**
     * Returns the Message Index (Field :28D:).
     * Format: n/m (z.B. "1/1" oder "2/3")
     */
    public function getMessageIndex(): string {
        return $this->messageIndex;
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
     * Returns the requested execution date (Field :30:).
     */
    public function getRequestedExecutionDate(): DateTimeImmutable {
        return $this->requestedExecutionDate;
    }

    /**
     * Returns the creation date.
     */
    public function getCreationDateTime(): DateTimeImmutable {
        return $this->creationDateTime;
    }

    /**
     * Returns all transactions.
     * @return Transaction[]
     */
    public function getTransactions(): array {
        return $this->transactions;
    }

    /**
     * Adds a transaction.
     */
    public function addTransaction(Transaction $transaction): void {
        $this->transactions[] = $transaction;
    }

    /**
     * Returns the number of transactions.
     */
    public function countTransactions(): int {
        return count($this->transactions);
    }

    /**
     * Berechnet die Gesamtsumme aller Transaktionen.
     */
    public function getTotalAmount(): float {
        return array_reduce(
            $this->transactions,
            fn(float $sum, Transaction $txn) => $sum + $txn->getAmount(),
            0.0
        );
    }

    /**
     * Returns all used currencies.
     * @return CurrencyCode[]
     */
    public function getCurrencies(): array {
        $currencies = [];
        foreach ($this->transactions as $txn) {
            $currency = $txn->getTransferDetails()->getCurrency();
            if (!in_array($currency, $currencies, true)) {
                $currencies[] = $currency;
            }
        }
        return $currencies;
    }

    /**
     * Generiert die SWIFT MT101 Nachricht.
     */
    public function __toString(): string {
        return (new Mt101Generator())->generate($this);
    }
}
