<?php
/*
 * Created on   : Wed Jul 09 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt101DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Entities\Mt1\TransferDetails;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type101\Document;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type101\Transaction;
use CommonToolkit\FinancialFormats\Enums\Mt\ChargesCode;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder for MT101 Request for Transfer.
 * 
 * Creates batch transfers according to SWIFT standard. Enables sending
 * multiple payment orders in one message.
 * 
 * Verwendung:
 * ```php
 * $document = Mt101DocumentBuilder::create('REF-001')
 *     ->orderingCustomer('DE89370400440532013000', 'Firma GmbH', 'COBADEFFXXX')
 *     ->requestedExecutionDate(new DateTimeImmutable('2024-03-15'))
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
final class Mt101DocumentBuilder {
    private string $sendersReference;
    private ?string $customerReference = null;
    private string $messageIndex = '1/1';
    private ?Party $orderingCustomer = null;
    private ?Party $orderingInstitution = null;
    private ?DateTimeImmutable $requestedExecutionDate = null;
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
     * Erzeugt neuen Builder mit Sender's Reference.
     */
    public static function create(string $sendersReference): self {
        return new self($sendersReference);
    }

    /**
     * Setzt die Customer Specified Reference (Feld :21R:).
     */
    public function customerReference(string $reference): self {
        if (strlen($reference) > 16) {
            throw new InvalidArgumentException('Customer reference must not exceed 16 characters');
        }
        $clone = clone $this;
        $clone->customerReference = $reference;
        return $clone;
    }

    /**
     * Setzt den Message Index (Feld :28D:).
     * Format: n/m (z.B. "1/1" oder "2/3")
     */
    public function messageIndex(int $current, int $total): self {
        $clone = clone $this;
        $clone->messageIndex = $current . '/' . $total;
        return $clone;
    }

    /**
     * Setzt den Auftraggeber (Feld :50:).
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
     * Setzt die Ordering Institution (Feld :52a:).
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
     * Sets the requested execution date (Field :30:).
     */
    public function requestedExecutionDate(DateTimeImmutable $date): self {
        $clone = clone $this;
        $clone->requestedExecutionDate = $date;
        return $clone;
    }

    /**
     * Setzt den Erstellungszeitpunkt.
     */
    public function withCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    /**
     * Beginnt eine neue Transaktion.
     */
    public function beginTransaction(string $transactionReference): Mt101TransactionBuilder {
        return new Mt101TransactionBuilder($this, $transactionReference);
    }

    /**
     * Adds a pre-built transaction.
     */
    public function addTransaction(Transaction $transaction): self {
        $clone = clone $this;
        $clone->transactions[] = $transaction;
        return $clone;
    }

    /**
     * Adds multiple transactions.
     * 
     * @param Transaction[] $transactions
     */
    public function addTransactions(array $transactions): self {
        $clone = clone $this;
        $clone->transactions = array_merge($clone->transactions, $transactions);
        return $clone;
    }

    /**
     * Called by Mt101TransactionBuilder to add the transaction.
     * @internal
     */
    public function pushTransaction(Transaction $transaction): self {
        return $this->addTransaction($transaction);
    }

    /**
     * Erstellt das MT101 Dokument.
     * 
     * @throws InvalidArgumentException wenn Pflichtfelder fehlen
     */
    public function build(): Document {
        if ($this->orderingCustomer === null) {
            throw new InvalidArgumentException('Ordering Customer (Auftraggeber) ist erforderlich');
        }
        if ($this->requestedExecutionDate === null) {
            throw new InvalidArgumentException('Requested Execution Date ist erforderlich');
        }
        if (empty($this->transactions)) {
            throw new InvalidArgumentException('Mindestens eine Transaktion erforderlich');
        }

        return new Document(
            sendersReference: $this->sendersReference,
            orderingCustomer: $this->orderingCustomer,
            requestedExecutionDate: $this->requestedExecutionDate,
            transactions: $this->transactions,
            orderingInstitution: $this->orderingInstitution,
            customerReference: $this->customerReference,
            messageIndex: $this->messageIndex,
            creationDateTime: $this->creationDateTime
        );
    }

    // === Static Factory Methods ===

    /**
     * Creates a simple batch transfer.
     * 
     * @param array<array{reference: string, amount: float, currency: CurrencyCode, beneficiaryAccount: string, beneficiaryName: string, beneficiaryBic?: string, remittanceInfo?: string}> $payments
     */
    public static function createBatch(
        string $sendersReference,
        string $orderingAccount,
        string $orderingName,
        DateTimeImmutable $executionDate,
        array $payments
    ): Document {
        $builder = self::create($sendersReference)
            ->orderingCustomer($orderingAccount, $orderingName)
            ->requestedExecutionDate($executionDate);

        foreach ($payments as $payment) {
            $builder = $builder
                ->beginTransaction($payment['reference'])
                ->amount($payment['amount'], $payment['currency'], $executionDate)
                ->beneficiary(
                    $payment['beneficiaryAccount'],
                    $payment['beneficiaryName'],
                    $payment['beneficiaryBic'] ?? null
                )
                ->remittanceInfo($payment['remittanceInfo'] ?? null)
                ->done();
        }

        return $builder->build();
    }
}

/**
 * Helper builder for individual MT101 transactions.
 */
final class Mt101TransactionBuilder {
    private string $transactionReference;
    private ?TransferDetails $transferDetails = null;
    private ?Party $beneficiary = null;
    private ?Party $accountWithInstitution = null;
    private ?string $remittanceInfo = null;
    private ?ChargesCode $chargesCode = null;

    public function __construct(
        private readonly Mt101DocumentBuilder $parent,
        string $transactionReference
    ) {
        if (strlen($transactionReference) > 16) {
            throw new InvalidArgumentException('Transaction reference must not exceed 16 characters');
        }
        $this->transactionReference = $transactionReference;
    }

    /**
     * Sets amount, currency and value date (Field :32B:).
     */
    public function amount(float $amount, CurrencyCode $currency, DateTimeImmutable $valueDate): self {
        $clone = clone $this;
        $clone->transferDetails = new TransferDetails(
            valueDate: $valueDate,
            currency: $currency,
            amount: $amount
        );
        return $clone;
    }

    /**
     * Sets the TransferDetails with complete object.
     */
    public function transferDetails(TransferDetails $details): self {
        $clone = clone $this;
        $clone->transferDetails = $details;
        return $clone;
    }

    /**
     * Sets the beneficiary (Field :59:).
     */
    public function beneficiary(string $account, string $name, ?string $bic = null, ?string $address = null): self {
        $clone = clone $this;
        $clone->beneficiary = new Party(
            account: $account,
            bic: $bic,
            name: $name,
            addressLine1: $address
        );
        return $clone;
    }

    /**
     * Sets the beneficiary with complete Party.
     */
    public function beneficiaryParty(Party $party): self {
        $clone = clone $this;
        $clone->beneficiary = $party;
        return $clone;
    }

    /**
     * Setzt die Account With Institution (Feld :57a:).
     */
    public function accountWithInstitution(string $bic, ?string $name = null): self {
        $clone = clone $this;
        $clone->accountWithInstitution = new Party(bic: $bic, name: $name);
        return $clone;
    }

    /**
     * Setzt den Verwendungszweck (Feld :70:).
     */
    public function remittanceInfo(?string $info): self {
        $clone = clone $this;
        $clone->remittanceInfo = $info;
        return $clone;
    }

    /**
     * Sets the charges code (Field :71A:).
     */
    public function chargesCode(ChargesCode $code): self {
        $clone = clone $this;
        $clone->chargesCode = $code;
        return $clone;
    }

    /**
     * Ends the transaction and returns to the main builder.
     */
    public function done(): Mt101DocumentBuilder {
        if ($this->transferDetails === null) {
            throw new InvalidArgumentException('TransferDetails (Betrag/Währung/Datum) erforderlich');
        }
        if ($this->beneficiary === null) {
            throw new InvalidArgumentException('Beneficiary (Begünstigter) erforderlich');
        }

        $transaction = new Transaction(
            transactionReference: $this->transactionReference,
            transferDetails: $this->transferDetails,
            beneficiary: $this->beneficiary,
            accountWithInstitution: $this->accountWithInstitution,
            remittanceInfo: $this->remittanceInfo,
            chargesCode: $this->chargesCode
        );

        return $this->parent->pushTransaction($transaction);
    }
}
