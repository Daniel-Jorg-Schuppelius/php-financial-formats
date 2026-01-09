<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt910DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type910\Document;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder for MT910 Confirmation of Credit.
 * 
 * Creates MT910 documents for notifying account owners of credit entries.
 * 
 * Usage:
 * ```php
 * $document = Mt910DocumentBuilder::create('CREDIT-001', 'REL-001')
 *     ->account('DE89370400440532013000')
 *     ->valueDate(new DateTimeImmutable('2024-03-15'))
 *     ->amount(2500.00, CurrencyCode::Euro)
 *     ->orderingCustomer('DE11520513735120710131', 'Max Mustermann')
 *     ->orderingInstitution('COBADEFFXXX')
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\Builders\Common\Banking\Mt
 */
final class Mt910DocumentBuilder {
    private string $transactionReference;
    private string $relatedReference;
    private ?string $accountId = null;
    private ?DateTimeImmutable $valueDate = null;
    private ?CurrencyCode $currency = null;
    private ?float $amount = null;
    private ?DateTimeImmutable $dateTimeIndication = null;
    private ?Party $orderingCustomer = null;
    private ?Party $orderingInstitution = null;
    private ?Party $intermediary = null;
    private ?string $senderToReceiverInfo = null;

    private function __construct(string $transactionReference, string $relatedReference) {
        $this->transactionReference = $transactionReference;
        $this->relatedReference = $relatedReference;
    }

    /**
     * Creates a new builder instance.
     * 
     * @param string $transactionReference Field :20: Transaction reference
     * @param string $relatedReference Field :21: Related reference
     */
    public static function create(string $transactionReference, string $relatedReference): self {
        return new self($transactionReference, $relatedReference);
    }

    /**
     * Sets the account identification (Field :25:).
     */
    public function account(string $accountId): self {
        $this->accountId = $accountId;
        return $this;
    }

    /**
     * Sets the value date (Field :32A:).
     */
    public function valueDate(DateTimeImmutable $date): self {
        $this->valueDate = $date;
        return $this;
    }

    /**
     * Sets the currency and amount (Field :32A:).
     */
    public function amount(float $amount, CurrencyCode $currency): self {
        $this->amount = $amount;
        $this->currency = $currency;
        return $this;
    }

    /**
     * Sets the date/time indication (Field :13D:).
     */
    public function dateTimeIndication(DateTimeImmutable $dateTime): self {
        $this->dateTimeIndication = $dateTime;
        return $this;
    }

    /**
     * Sets the ordering customer (Field :50a:).
     */
    public function orderingCustomer(string $account, string $name, ?string $addressLine1 = null, ?string $addressLine2 = null): self {
        $this->orderingCustomer = new Party(
            account: $account,
            name: $name,
            addressLine1: $addressLine1,
            addressLine2: $addressLine2
        );
        return $this;
    }

    /**
     * Sets the ordering customer as Party (Field :50a:).
     */
    public function orderingCustomerParty(Party $party): self {
        $this->orderingCustomer = $party;
        return $this;
    }

    /**
     * Sets the ordering institution (Field :52a:).
     */
    public function orderingInstitution(string $bic, ?string $name = null): self {
        $this->orderingInstitution = new Party(bic: $bic, name: $name);
        return $this;
    }

    /**
     * Sets the ordering institution as Party (Field :52a:).
     */
    public function orderingInstitutionParty(Party $party): self {
        $this->orderingInstitution = $party;
        return $this;
    }

    /**
     * Sets the intermediary (Field :56a:).
     */
    public function intermediary(string $bic, ?string $name = null): self {
        $this->intermediary = new Party(bic: $bic, name: $name);
        return $this;
    }

    /**
     * Sets the intermediary as Party (Field :56a:).
     */
    public function intermediaryParty(Party $party): self {
        $this->intermediary = $party;
        return $this;
    }

    /**
     * Sets the sender to receiver information (Field :72:).
     */
    public function senderToReceiverInfo(string $info): self {
        $this->senderToReceiverInfo = $info;
        return $this;
    }

    /**
     * Builds the MT910 document.
     * 
     * @throws InvalidArgumentException if required fields are missing
     */
    public function build(): Document {
        if ($this->accountId === null) {
            throw new InvalidArgumentException('Account ID is required');
        }
        if ($this->valueDate === null) {
            throw new InvalidArgumentException('Value date is required');
        }
        if ($this->currency === null || $this->amount === null) {
            throw new InvalidArgumentException('Currency and amount are required');
        }

        return new Document(
            transactionReference: $this->transactionReference,
            relatedReference: $this->relatedReference,
            accountId: $this->accountId,
            valueDate: $this->valueDate,
            currency: $this->currency,
            amount: $this->amount,
            dateTimeIndication: $this->dateTimeIndication,
            orderingCustomer: $this->orderingCustomer,
            orderingInstitution: $this->orderingInstitution,
            intermediary: $this->intermediary,
            senderToReceiverInfo: $this->senderToReceiverInfo
        );
    }
}
