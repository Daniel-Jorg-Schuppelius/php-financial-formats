<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt102DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Entities\Mt1\TransferDetails;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type102\Document;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type102\Transaction;
use CommonToolkit\FinancialFormats\Enums\Mt\ChargesCode;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder for MT102 Multiple Customer Credit Transfer.
 * 
 * Creates batch payments for multiple beneficiaries in one message.
 * 
 * Usage:
 * ```php
 * $document = Mt102DocumentBuilder::create('REF-001')
 *     ->orderingCustomer('DE89370400440532013000', 'Firma GmbH', 'COBADEFFXXX')
 *     ->valueDate(new DateTimeImmutable('2024-03-15'))
 *     ->currency(CurrencyCode::Euro)
 *     ->beginTransaction('TXN-001')
 *         ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
 *         ->beneficiary('DE91100000000123456789', 'Max Mustermann', 'DEUTDEFF')
 *         ->remittanceInfo('Rechnung 2024-001')
 *         ->done()
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\Builders\Mt
 */
final class Mt102DocumentBuilder {
    private string $sendersReference;
    private string $bankOperationCode = 'CRED';
    private ?Party $orderingCustomer = null;
    private ?Party $orderingInstitution = null;
    private ?DateTimeImmutable $valueDate = null;
    private CurrencyCode $currency = CurrencyCode::Euro;
    private ?string $transactionTypeCode = null;
    private ?string $regulatoryReporting = null;
    private ?string $detailsOfCharges = null;
    private ?float $exchangeRate = null;
    private ?DateTimeImmutable $creationDateTime = null;
    /** @var Transaction[] */
    private array $transactions = [];

    private function __construct(string $sendersReference) {
        if (strlen($sendersReference) > 16) {
            throw new InvalidArgumentException('Sender\'s Reference darf maximal 16 Zeichen lang sein');
        }
        $this->sendersReference = $sendersReference;
        $this->creationDateTime = new DateTimeImmutable();
    }

    /**
     * Creates new builder with Sender's Reference.
     */
    public static function create(string $sendersReference): self {
        return new self($sendersReference);
    }

    /**
     * Sets the Bank Operation Code (Field :23:).
     * Default: CRED (Credit Transfer)
     */
    public function bankOperationCode(string $code): self {
        $clone = clone $this;
        $clone->bankOperationCode = $code;
        return $clone;
    }

    /**
     * Sets the ordering customer (Field :50:).
     */
    public function orderingCustomer(string $account, string $name, ?string $bic = null, ?string $address = null): self {
        $clone = clone $this;
        $clone->orderingCustomer = new Party(
            account: $account,
            bic: $bic,
            name: $name,
            addressLine1: $address
        );
        return $clone;
    }

    /**
     * Sets the ordering party with complete Party.
     */
    public function orderingCustomerParty(Party $party): self {
        $clone = clone $this;
        $clone->orderingCustomer = $party;
        return $clone;
    }

    /**
     * Sets the Ordering Institution (Field :52a:).
     */
    public function orderingInstitution(string $bic, ?string $name = null): self {
        $clone = clone $this;
        $clone->orderingInstitution = new Party(bic: $bic, name: $name);
        return $clone;
    }

    /**
     * Sets the Ordering Institution with complete Party.
     */
    public function orderingInstitutionParty(Party $party): self {
        $clone = clone $this;
        $clone->orderingInstitution = $party;
        return $clone;
    }

    /**
     * Sets the value date for the batch (Field :32A:).
     */
    public function valueDate(DateTimeImmutable $date): self {
        $clone = clone $this;
        $clone->valueDate = $date;
        return $clone;
    }

    /**
     * Sets the currency for the batch.
     */
    public function currency(CurrencyCode $currency): self {
        $clone = clone $this;
        $clone->currency = $currency;
        return $clone;
    }

    /**
     * Sets the Transaction Type Code (Field :26T:).
     */
    public function transactionTypeCode(string $code): self {
        $clone = clone $this;
        $clone->transactionTypeCode = $code;
        return $clone;
    }

    /**
     * Sets the Regulatory Reporting (Field :77B:).
     */
    public function regulatoryReporting(string $reporting): self {
        $clone = clone $this;
        $clone->regulatoryReporting = $reporting;
        return $clone;
    }

    /**
     * Sets the Details of Charges (Field :71A:).
     */
    public function detailsOfCharges(string $charges): self {
        $clone = clone $this;
        $clone->detailsOfCharges = $charges;
        return $clone;
    }

    /**
     * Sets the Exchange Rate (Field :36:).
     */
    public function exchangeRate(float $rate): self {
        $clone = clone $this;
        $clone->exchangeRate = $rate;
        return $clone;
    }

    /**
     * Sets the creation datetime.
     */
    public function withCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    /**
     * Begins a new transaction (Sequence B).
     */
    public function beginTransaction(string $transactionReference): Mt102TransactionBuilder {
        return new Mt102TransactionBuilder($this, $transactionReference);
    }

    /**
     * Adds a completed transaction (internal).
     * 
     * @internal
     */
    public function addTransaction(Transaction $transaction): self {
        $clone = clone $this;
        $clone->transactions = [...$this->transactions, $transaction];
        return $clone;
    }

    /**
     * Builds the MT102 document.
     */
    public function build(): Document {
        if ($this->orderingCustomer === null) {
            throw new InvalidArgumentException('Ordering Customer is required');
        }

        if ($this->valueDate === null) {
            throw new InvalidArgumentException('Value Date is required');
        }

        if (empty($this->transactions)) {
            throw new InvalidArgumentException('At least one transaction is required');
        }

        return new Document(
            sendersReference: $this->sendersReference,
            orderingCustomer: $this->orderingCustomer,
            valueDate: $this->valueDate,
            currency: $this->currency,
            transactions: $this->transactions,
            bankOperationCode: $this->bankOperationCode,
            orderingInstitution: $this->orderingInstitution,
            transactionTypeCode: $this->transactionTypeCode,
            regulatoryReporting: $this->regulatoryReporting,
            detailsOfCharges: $this->detailsOfCharges,
            exchangeRate: $this->exchangeRate,
            creationDateTime: $this->creationDateTime
        );
    }
}

/**
 * Transaction Builder for MT102.
 */
final class Mt102TransactionBuilder {
    private Mt102DocumentBuilder $parent;
    private string $transactionReference;
    private ?TransferDetails $transferDetails = null;
    private ?Party $beneficiary = null;
    private ?Party $orderingCustomer = null;
    private ?Party $accountWithInstitution = null;
    private ?string $remittanceInfo = null;
    private ?ChargesCode $chargesCode = null;
    private ?string $regulatoryReporting = null;

    public function __construct(Mt102DocumentBuilder $parent, string $transactionReference) {
        if (strlen($transactionReference) > 16) {
            throw new InvalidArgumentException('Transaction Reference darf maximal 16 Zeichen lang sein');
        }
        $this->parent = $parent;
        $this->transactionReference = $transactionReference;
    }

    /**
     * Sets the amount and currency (Field :32B:).
     */
    public function amount(float $amount, CurrencyCode $currency, DateTimeImmutable $valueDate): self {
        $this->transferDetails = new TransferDetails($valueDate, $currency, $amount);
        return $this;
    }

    /**
     * Sets the beneficiary (Field :59:).
     */
    public function beneficiary(string $account, string $name, ?string $bic = null, ?string $address = null): self {
        $this->beneficiary = new Party(
            account: $account,
            bic: $bic,
            name: $name,
            addressLine1: $address
        );
        return $this;
    }

    /**
     * Sets the beneficiary with complete Party.
     */
    public function beneficiaryParty(Party $party): self {
        $this->beneficiary = $party;
        return $this;
    }

    /**
     * Sets the ordering customer for this transaction if different from header.
     */
    public function orderingCustomer(string $account, string $name, ?string $bic = null, ?string $address = null): self {
        $this->orderingCustomer = new Party(
            account: $account,
            bic: $bic,
            name: $name,
            addressLine1: $address
        );
        return $this;
    }

    /**
     * Sets the Account With Institution (Field :57a:).
     */
    public function accountWithInstitution(string $bic, ?string $name = null): self {
        $this->accountWithInstitution = new Party(bic: $bic, name: $name);
        return $this;
    }

    /**
     * Sets the remittance information (Field :70:).
     */
    public function remittanceInfo(string $info): self {
        $this->remittanceInfo = $info;
        return $this;
    }

    /**
     * Sets the charges code (Field :71A:).
     */
    public function charges(ChargesCode $code): self {
        $this->chargesCode = $code;
        return $this;
    }

    /**
     * Sets the regulatory reporting (Field :77B:).
     */
    public function regulatoryReporting(string $reporting): self {
        $this->regulatoryReporting = $reporting;
        return $this;
    }

    /**
     * Completes the transaction and returns to the document builder.
     */
    public function done(): Mt102DocumentBuilder {
        if ($this->transferDetails === null) {
            throw new InvalidArgumentException('Amount is required for transaction');
        }

        if ($this->beneficiary === null) {
            throw new InvalidArgumentException('Beneficiary is required for transaction');
        }

        $transaction = new Transaction(
            transactionReference: $this->transactionReference,
            transferDetails: $this->transferDetails,
            beneficiary: $this->beneficiary,
            orderingCustomer: $this->orderingCustomer,
            accountWithInstitution: $this->accountWithInstitution,
            remittanceInfo: $this->remittanceInfo,
            chargesCode: $this->chargesCode,
            regulatoryReporting: $this->regulatoryReporting
        );

        return $this->parent->addTransaction($transaction);
    }
}
