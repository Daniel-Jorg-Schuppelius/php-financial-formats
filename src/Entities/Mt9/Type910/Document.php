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

namespace CommonToolkit\FinancialFormats\Entities\Mt9\Type910;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use DateTimeImmutable;

/**
 * MT910 Document - Confirmation of Credit.
 * 
 * Sent by an account servicing institution to an account owner to confirm
 * the credit entry made to the account owner's account.
 * 
 * This message is used to notify the account owner of a credit that has
 * been made to their account.
 * 
 * Fields:
 * - :20: Transaction Reference Number (M)
 * - :21: Related Reference (M)
 * - :25: Account Identification (M)
 * - :13D: Date/Time Indication (O)
 * - :32A: Value Date/Currency/Amount (M)
 * - :50a: Ordering Customer (O)
 * - :52a: Ordering Institution (O)
 * - :56a: Intermediary (O)
 * - :72: Sender to Receiver Information (O)
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt9\Type910
 */
final readonly class Document {
    public function __construct(
        private string $transactionReference,
        private string $relatedReference,
        private string $accountId,
        private DateTimeImmutable $valueDate,
        private CurrencyCode $currency,
        private float $amount,
        private ?DateTimeImmutable $dateTimeIndication = null,
        private ?Party $orderingCustomer = null,
        private ?Party $orderingInstitution = null,
        private ?Party $intermediary = null,
        private ?string $senderToReceiverInfo = null
    ) {
    }

    /**
     * Returns the MT message type.
     */
    public function getMtType(): MtType {
        return MtType::MT910;
    }

    /**
     * Returns the transaction reference.
     * Field :20:
     */
    public function getTransactionReference(): string {
        return $this->transactionReference;
    }

    /**
     * Returns the related reference.
     * Field :21:
     */
    public function getRelatedReference(): string {
        return $this->relatedReference;
    }

    /**
     * Returns the account identification.
     * Field :25:
     */
    public function getAccountId(): string {
        return $this->accountId;
    }

    /**
     * Returns the date/time indication.
     * Field :13D:
     */
    public function getDateTimeIndication(): ?DateTimeImmutable {
        return $this->dateTimeIndication;
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
     * Returns the amount.
     */
    public function getAmount(): float {
        return $this->amount;
    }

    /**
     * Returns the ordering customer.
     * Field :50a:
     */
    public function getOrderingCustomer(): ?Party {
        return $this->orderingCustomer;
    }

    /**
     * Returns the ordering institution.
     * Field :52a:
     */
    public function getOrderingInstitution(): ?Party {
        return $this->orderingInstitution;
    }

    /**
     * Returns the intermediary.
     * Field :56a:
     */
    public function getIntermediary(): ?Party {
        return $this->intermediary;
    }

    /**
     * Returns the sender to receiver information.
     * Field :72:
     */
    public function getSenderToReceiverInfo(): ?string {
        return $this->senderToReceiverInfo;
    }

    /**
     * Formats the value date, currency and amount as Field 32A.
     * Format: YYMMDDCCCNNN,NN (Date + Currency + Amount)
     */
    public function toField32A(): string {
        $date = $this->valueDate->format('ymd');
        $amount = number_format($this->amount, 2, ',', '');
        return $date . $this->currency->value . $amount;
    }

    /**
     * Formats the date/time indication as Field 13D.
     * Format: YYMMDDHHMM+/-HHMM
     */
    public function toField13D(): ?string {
        if ($this->dateTimeIndication === null) {
            return null;
        }
        return $this->dateTimeIndication->format('ymdHiO');
    }
}
