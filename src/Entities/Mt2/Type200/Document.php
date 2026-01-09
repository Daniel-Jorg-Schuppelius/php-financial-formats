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

namespace CommonToolkit\FinancialFormats\Entities\Mt2\Type200;

use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * MT200 Document - Financial Institution Transfer for its Own Account.
 * 
 * Used by a financial institution to transfer funds from one of its accounts
 * to another of its own accounts at a different institution.
 * 
 * Structure:
 * - :20:   Transaction Reference
 * - :32A:  Value Date/Currency/Amount
 * - :53a:  Sender's Correspondent (optional)
 * - :56a:  Intermediary (optional)
 * - :57a:  Account With Institution
 * - :72:   Sender to Receiver Information (optional)
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt2\Type200
 */
class Document {
    private string $transactionReference;
    private DateTimeImmutable $valueDate;
    private CurrencyCode $currency;
    private float $amount;
    private Party $accountWithInstitution;
    private ?Party $sendersCorrespondent;
    private ?Party $intermediary;
    private ?string $senderToReceiverInfo;
    private ?DateTimeImmutable $creationDateTime;

    public function __construct(
        string $transactionReference,
        DateTimeImmutable $valueDate,
        CurrencyCode $currency,
        float $amount,
        Party $accountWithInstitution,
        ?Party $sendersCorrespondent = null,
        ?Party $intermediary = null,
        ?string $senderToReceiverInfo = null,
        ?DateTimeImmutable $creationDateTime = null
    ) {
        $this->transactionReference = $transactionReference;
        $this->valueDate = $valueDate;
        $this->currency = $currency;
        $this->amount = $amount;
        $this->accountWithInstitution = $accountWithInstitution;
        $this->sendersCorrespondent = $sendersCorrespondent;
        $this->intermediary = $intermediary;
        $this->senderToReceiverInfo = $senderToReceiverInfo;
        $this->creationDateTime = $creationDateTime ?? new DateTimeImmutable();
    }

    public function getMtType(): MtType {
        return MtType::MT200;
    }

    /**
     * Returns the Transaction Reference (Field :20:).
     */
    public function getTransactionReference(): string {
        return $this->transactionReference;
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
     * Returns the Account With Institution (Field :57a:).
     */
    public function getAccountWithInstitution(): Party {
        return $this->accountWithInstitution;
    }

    /**
     * Returns the Sender's Correspondent (Field :53a:).
     */
    public function getSendersCorrespondent(): ?Party {
        return $this->sendersCorrespondent;
    }

    /**
     * Returns the Intermediary (Field :56a:).
     */
    public function getIntermediary(): ?Party {
        return $this->intermediary;
    }

    /**
     * Returns the Sender to Receiver Information (Field :72:).
     */
    public function getSenderToReceiverInfo(): ?string {
        return $this->senderToReceiverInfo;
    }

    /**
     * Returns the creation datetime.
     */
    public function getCreationDateTime(): ?DateTimeImmutable {
        return $this->creationDateTime;
    }

    /**
     * Returns the Field :32A: formatted value.
     */
    public function toField32A(): string {
        $amount = number_format($this->amount, 2, ',', '');
        return $this->valueDate->format('ymd') . $this->currency->value . $amount;
    }
}
