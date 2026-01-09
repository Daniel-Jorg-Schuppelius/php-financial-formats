<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt104DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Entities\Mt1\TransferDetails;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type104\Document;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type104\Transaction;
use CommonToolkit\FinancialFormats\Enums\Mt\ChargesCode;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder for MT104 Direct Debit Message.
 * 
 * Creates direct debit messages for collecting payments from multiple debtors.
 * 
 * Usage:
 * ```php
 * $document = Mt104DocumentBuilder::create('REF-001')
 *     ->creditor('DE89370400440532013000', 'Firma GmbH', 'COBADEFFXXX')
 *     ->requestedExecutionDate(new DateTimeImmutable('2024-03-15'))
 *     ->currency(CurrencyCode::Euro)
 *     ->beginTransaction('TXN-001')
 *         ->amount(1000.00, CurrencyCode::Euro, new DateTimeImmutable('2024-03-15'))
 *         ->debtor('DE91100000000123456789', 'Max Mustermann', 'DEUTDEFF')
 *         ->remittanceInfo('Lastschrift 2024-001')
 *         ->done()
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\Builders\Mt
 */
final class Mt104DocumentBuilder {
    private string $sendersReference;
    private ?string $mandateReference = null;
    private ?string $instructionCode = null;
    private ?string $transactionTypeCode = null;
    private ?DateTimeImmutable $requestedExecutionDate = null;
    private ?Party $creditor = null;
    private ?Party $sendingInstitution = null;
    private ?Party $creditorsBank = null;
    private ?Party $sendersCorrespondent = null;
    private ?string $detailsOfCharges = null;
    private ?string $senderToReceiverInfo = null;
    private ?string $regulatoryReporting = null;
    private CurrencyCode $currency = CurrencyCode::Euro;
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
     * Sets the Mandate Reference (Field :21E:).
     */
    public function mandateReference(string $reference): self {
        $clone = clone $this;
        $clone->mandateReference = $reference;
        return $clone;
    }

    /**
     * Sets the Instruction Code (Field :23E:).
     */
    public function instructionCode(string $code): self {
        $clone = clone $this;
        $clone->instructionCode = $code;
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
     * Sets the requested execution date (Field :30:).
     */
    public function requestedExecutionDate(DateTimeImmutable $date): self {
        $clone = clone $this;
        $clone->requestedExecutionDate = $date;
        return $clone;
    }

    /**
     * Sets the creditor (Field :50:).
     */
    public function creditor(string $account, string $name, ?string $bic = null, ?string $address = null): self {
        $clone = clone $this;
        $clone->creditor = new Party(
            account: $account,
            bic: $bic,
            name: $name,
            addressLine1: $address
        );
        return $clone;
    }

    /**
     * Sets the creditor with complete Party.
     */
    public function creditorParty(Party $party): self {
        $clone = clone $this;
        $clone->creditor = $party;
        return $clone;
    }

    /**
     * Sets the Sending Institution (Field :51A:).
     */
    public function sendingInstitution(string $bic, ?string $name = null): self {
        $clone = clone $this;
        $clone->sendingInstitution = new Party(bic: $bic, name: $name);
        return $clone;
    }

    /**
     * Sets the Creditor's Bank (Field :52a:).
     */
    public function creditorsBank(string $bic, ?string $name = null): self {
        $clone = clone $this;
        $clone->creditorsBank = new Party(bic: $bic, name: $name);
        return $clone;
    }

    /**
     * Sets the Sender's Correspondent (Field :53a:).
     */
    public function sendersCorrespondent(string $bic, ?string $name = null): self {
        $clone = clone $this;
        $clone->sendersCorrespondent = new Party(bic: $bic, name: $name);
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
     * Sets the Sender to Receiver Information (Field :72:).
     */
    public function senderToReceiverInfo(string $info): self {
        $clone = clone $this;
        $clone->senderToReceiverInfo = $info;
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
     * Sets the currency for the batch.
     */
    public function currency(CurrencyCode $currency): self {
        $clone = clone $this;
        $clone->currency = $currency;
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
    public function beginTransaction(string $transactionReference): Mt104TransactionBuilder {
        return new Mt104TransactionBuilder($this, $transactionReference);
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
     * Builds the MT104 document.
     */
    public function build(): Document {
        if ($this->creditor === null) {
            throw new InvalidArgumentException('Creditor is required');
        }

        if ($this->requestedExecutionDate === null) {
            throw new InvalidArgumentException('Requested Execution Date is required');
        }

        if (empty($this->transactions)) {
            throw new InvalidArgumentException('At least one transaction is required');
        }

        return new Document(
            sendersReference: $this->sendersReference,
            creditor: $this->creditor,
            requestedExecutionDate: $this->requestedExecutionDate,
            currency: $this->currency,
            transactions: $this->transactions,
            mandateReference: $this->mandateReference,
            instructionCode: $this->instructionCode,
            transactionTypeCode: $this->transactionTypeCode,
            sendingInstitution: $this->sendingInstitution,
            creditorsBank: $this->creditorsBank,
            sendersCorrespondent: $this->sendersCorrespondent,
            detailsOfCharges: $this->detailsOfCharges,
            senderToReceiverInfo: $this->senderToReceiverInfo,
            regulatoryReporting: $this->regulatoryReporting,
            creationDateTime: $this->creationDateTime
        );
    }
}

/**
 * Transaction Builder for MT104.
 */
final class Mt104TransactionBuilder {
    private Mt104DocumentBuilder $parent;
    private string $transactionReference;
    private ?TransferDetails $transferDetails = null;
    private ?Party $debtor = null;
    private ?string $endToEndReference = null;
    private ?string $instructionCode = null;
    private ?Party $creditor = null;
    private ?Party $creditorsBank = null;
    private ?Party $debtorsBank = null;
    private ?string $remittanceInfo = null;
    private ?ChargesCode $chargesCode = null;
    private ?string $transactionTypeCode = null;

    public function __construct(Mt104DocumentBuilder $parent, string $transactionReference) {
        if (strlen($transactionReference) > 16) {
            throw new InvalidArgumentException('Transaction Reference darf maximal 16 Zeichen lang sein');
        }
        $this->parent = $parent;
        $this->transactionReference = $transactionReference;
    }

    /**
     * Sets the End-to-End Reference (Field :21C:).
     */
    public function endToEndReference(string $reference): self {
        $this->endToEndReference = $reference;
        return $this;
    }

    /**
     * Sets the Instruction Code (Field :23E:).
     */
    public function instructionCode(string $code): self {
        $this->instructionCode = $code;
        return $this;
    }

    /**
     * Sets the amount and currency (Field :32B:).
     */
    public function amount(float $amount, CurrencyCode $currency, DateTimeImmutable $valueDate): self {
        $this->transferDetails = new TransferDetails($valueDate, $currency, $amount);
        return $this;
    }

    /**
     * Sets the debtor (Field :59:) - account to be debited.
     */
    public function debtor(string $account, string $name, ?string $bic = null, ?string $address = null): self {
        $this->debtor = new Party(
            account: $account,
            bic: $bic,
            name: $name,
            addressLine1: $address
        );
        return $this;
    }

    /**
     * Sets the debtor with complete Party.
     */
    public function debtorParty(Party $party): self {
        $this->debtor = $party;
        return $this;
    }

    /**
     * Sets the creditor for this transaction if different from header.
     */
    public function creditor(string $account, string $name, ?string $bic = null, ?string $address = null): self {
        $this->creditor = new Party(
            account: $account,
            bic: $bic,
            name: $name,
            addressLine1: $address
        );
        return $this;
    }

    /**
     * Sets the Creditor's Bank (Field :52a:).
     */
    public function creditorsBank(string $bic, ?string $name = null): self {
        $this->creditorsBank = new Party(bic: $bic, name: $name);
        return $this;
    }

    /**
     * Sets the Debtor's Bank (Field :57a:).
     */
    public function debtorsBank(string $bic, ?string $name = null): self {
        $this->debtorsBank = new Party(bic: $bic, name: $name);
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
     * Sets the Transaction Type Code (Field :26T:).
     */
    public function transactionTypeCode(string $code): self {
        $this->transactionTypeCode = $code;
        return $this;
    }

    /**
     * Completes the transaction and returns to the document builder.
     */
    public function done(): Mt104DocumentBuilder {
        if ($this->transferDetails === null) {
            throw new InvalidArgumentException('Amount is required for transaction');
        }

        if ($this->debtor === null) {
            throw new InvalidArgumentException('Debtor is required for transaction');
        }

        $transaction = new Transaction(
            transactionReference: $this->transactionReference,
            transferDetails: $this->transferDetails,
            debtor: $this->debtor,
            endToEndReference: $this->endToEndReference,
            instructionCode: $this->instructionCode,
            creditor: $this->creditor,
            creditorsBank: $this->creditorsBank,
            debtorsBank: $this->debtorsBank,
            remittanceInfo: $this->remittanceInfo,
            chargesCode: $this->chargesCode,
            transactionTypeCode: $this->transactionTypeCode
        );

        return $this->parent->addTransaction($transaction);
    }
}
