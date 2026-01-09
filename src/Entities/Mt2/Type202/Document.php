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

namespace CommonToolkit\FinancialFormats\Entities\Mt2\Type202;

use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * MT202 Document - General Financial Institution Transfer.
 * 
 * Used by a financial institution to order movement of funds between
 * financial institutions. Can also be used for cover payments (MT202COV).
 * 
 * Structure:
 * - :20:   Transaction Reference
 * - :21:   Related Reference
 * - :13C:  Time Indication (optional)
 * - :32A:  Value Date/Currency/Amount
 * - :52a:  Ordering Institution (optional)
 * - :53a:  Sender's Correspondent (optional)
 * - :54a:  Receiver's Correspondent (optional)
 * - :56a:  Intermediary (optional)
 * - :57a:  Account With Institution (optional)
 * - :58a:  Beneficiary Institution
 * - :72:   Sender to Receiver Information (optional)
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt2\Type202
 */
class Document {
    private string $transactionReference;
    private string $relatedReference;
    private ?string $timeIndication;
    private DateTimeImmutable $valueDate;
    private CurrencyCode $currency;
    private float $amount;
    private Party $beneficiaryInstitution;
    private ?Party $orderingInstitution;
    private ?Party $sendersCorrespondent;
    private ?Party $receiversCorrespondent;
    private ?Party $intermediary;
    private ?Party $accountWithInstitution;
    private ?string $senderToReceiverInfo;
    private bool $isCoverPayment;
    private ?DateTimeImmutable $creationDateTime;

    public function __construct(
        string $transactionReference,
        string $relatedReference,
        DateTimeImmutable $valueDate,
        CurrencyCode $currency,
        float $amount,
        Party $beneficiaryInstitution,
        ?Party $orderingInstitution = null,
        ?Party $sendersCorrespondent = null,
        ?Party $receiversCorrespondent = null,
        ?Party $intermediary = null,
        ?Party $accountWithInstitution = null,
        ?string $senderToReceiverInfo = null,
        ?string $timeIndication = null,
        bool $isCoverPayment = false,
        ?DateTimeImmutable $creationDateTime = null
    ) {
        $this->transactionReference = $transactionReference;
        $this->relatedReference = $relatedReference;
        $this->valueDate = $valueDate;
        $this->currency = $currency;
        $this->amount = $amount;
        $this->beneficiaryInstitution = $beneficiaryInstitution;
        $this->orderingInstitution = $orderingInstitution;
        $this->sendersCorrespondent = $sendersCorrespondent;
        $this->receiversCorrespondent = $receiversCorrespondent;
        $this->intermediary = $intermediary;
        $this->accountWithInstitution = $accountWithInstitution;
        $this->senderToReceiverInfo = $senderToReceiverInfo;
        $this->timeIndication = $timeIndication;
        $this->isCoverPayment = $isCoverPayment;
        $this->creationDateTime = $creationDateTime ?? new DateTimeImmutable();
    }

    public function getMtType(): MtType {
        return $this->isCoverPayment ? MtType::MT202COV : MtType::MT202;
    }

    /**
     * Returns the Transaction Reference (Field :20:).
     */
    public function getTransactionReference(): string {
        return $this->transactionReference;
    }

    /**
     * Returns the Related Reference (Field :21:).
     */
    public function getRelatedReference(): string {
        return $this->relatedReference;
    }

    /**
     * Returns the Time Indication (Field :13C:).
     */
    public function getTimeIndication(): ?string {
        return $this->timeIndication;
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
     * Returns the Beneficiary Institution (Field :58a:).
     */
    public function getBeneficiaryInstitution(): Party {
        return $this->beneficiaryInstitution;
    }

    /**
     * Returns the Ordering Institution (Field :52a:).
     */
    public function getOrderingInstitution(): ?Party {
        return $this->orderingInstitution;
    }

    /**
     * Returns the Sender's Correspondent (Field :53a:).
     */
    public function getSendersCorrespondent(): ?Party {
        return $this->sendersCorrespondent;
    }

    /**
     * Returns the Receiver's Correspondent (Field :54a:).
     */
    public function getReceiversCorrespondent(): ?Party {
        return $this->receiversCorrespondent;
    }

    /**
     * Returns the Intermediary (Field :56a:).
     */
    public function getIntermediary(): ?Party {
        return $this->intermediary;
    }

    /**
     * Returns the Account With Institution (Field :57a:).
     */
    public function getAccountWithInstitution(): ?Party {
        return $this->accountWithInstitution;
    }

    /**
     * Returns the Sender to Receiver Information (Field :72:).
     */
    public function getSenderToReceiverInfo(): ?string {
        return $this->senderToReceiverInfo;
    }

    /**
     * Returns true if this is a cover payment (MT202COV).
     */
    public function isCoverPayment(): bool {
        return $this->isCoverPayment;
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
