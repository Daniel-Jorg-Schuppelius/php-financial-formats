<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt202DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Entities\Mt2\Type202\Document;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder for MT202 General Financial Institution Transfer.
 * 
 * Creates FI transfers between financial institutions.
 * Can also create MT202COV cover payments.
 * 
 * Usage:
 * ```php
 * $document = Mt202DocumentBuilder::create('REF-001', 'RELATED-001')
 *     ->valueDate(new DateTimeImmutable('2024-03-15'))
 *     ->amount(100000.00, CurrencyCode::Euro)
 *     ->beneficiaryInstitution('DEUTDEFFXXX')
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\Builders\Mt
 */
final class Mt202DocumentBuilder {
    private string $transactionReference;
    private string $relatedReference;
    private ?string $timeIndication = null;
    private ?DateTimeImmutable $valueDate = null;
    private CurrencyCode $currency = CurrencyCode::Euro;
    private ?float $amount = null;
    private ?Party $beneficiaryInstitution = null;
    private ?Party $orderingInstitution = null;
    private ?Party $sendersCorrespondent = null;
    private ?Party $receiversCorrespondent = null;
    private ?Party $intermediary = null;
    private ?Party $accountWithInstitution = null;
    private ?string $senderToReceiverInfo = null;
    private bool $isCoverPayment = false;
    private ?DateTimeImmutable $creationDateTime = null;

    private function __construct(string $transactionReference, string $relatedReference) {
        if (strlen($transactionReference) > 16) {
            throw new InvalidArgumentException('Transaction Reference darf maximal 16 Zeichen lang sein');
        }
        if (strlen($relatedReference) > 16) {
            throw new InvalidArgumentException('Related Reference darf maximal 16 Zeichen lang sein');
        }
        $this->transactionReference = $transactionReference;
        $this->relatedReference = $relatedReference;
        $this->creationDateTime = new DateTimeImmutable();
    }

    /**
     * Creates new builder with Transaction Reference and Related Reference.
     */
    public static function create(string $transactionReference, string $relatedReference): self {
        return new self($transactionReference, $relatedReference);
    }

    /**
     * Sets the Time Indication (Field :13C:).
     */
    public function timeIndication(string $indication): self {
        $clone = clone $this;
        $clone->timeIndication = $indication;
        return $clone;
    }

    /**
     * Sets the value date (Field :32A:).
     */
    public function valueDate(DateTimeImmutable $date): self {
        $clone = clone $this;
        $clone->valueDate = $date;
        return $clone;
    }

    /**
     * Sets the amount and currency (Field :32A:).
     */
    public function amount(float $amount, CurrencyCode $currency): self {
        $clone = clone $this;
        $clone->amount = $amount;
        $clone->currency = $currency;
        return $clone;
    }

    /**
     * Sets the Beneficiary Institution (Field :58a:).
     */
    public function beneficiaryInstitution(string $bic, ?string $account = null): self {
        $clone = clone $this;
        $clone->beneficiaryInstitution = new Party(account: $account, bic: $bic);
        return $clone;
    }

    /**
     * Sets the Beneficiary Institution with complete Party.
     */
    public function beneficiaryInstitutionParty(Party $party): self {
        $clone = clone $this;
        $clone->beneficiaryInstitution = $party;
        return $clone;
    }

    /**
     * Sets the Ordering Institution (Field :52a:).
     */
    public function orderingInstitution(string $bic, ?string $account = null): self {
        $clone = clone $this;
        $clone->orderingInstitution = new Party(account: $account, bic: $bic);
        return $clone;
    }

    /**
     * Sets the Sender's Correspondent (Field :53a:).
     */
    public function sendersCorrespondent(string $bic, ?string $account = null): self {
        $clone = clone $this;
        $clone->sendersCorrespondent = new Party(account: $account, bic: $bic);
        return $clone;
    }

    /**
     * Sets the Receiver's Correspondent (Field :54a:).
     */
    public function receiversCorrespondent(string $bic, ?string $account = null): self {
        $clone = clone $this;
        $clone->receiversCorrespondent = new Party(account: $account, bic: $bic);
        return $clone;
    }

    /**
     * Sets the Intermediary (Field :56a:).
     */
    public function intermediary(string $bic, ?string $account = null): self {
        $clone = clone $this;
        $clone->intermediary = new Party(account: $account, bic: $bic);
        return $clone;
    }

    /**
     * Sets the Account With Institution (Field :57a:).
     */
    public function accountWithInstitution(string $bic, ?string $account = null): self {
        $clone = clone $this;
        $clone->accountWithInstitution = new Party(account: $account, bic: $bic);
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
     * Marks this as a cover payment (MT202COV).
     */
    public function asCoverPayment(): self {
        $clone = clone $this;
        $clone->isCoverPayment = true;
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
     * Builds the MT202/MT202COV document.
     */
    public function build(): Document {
        if ($this->valueDate === null) {
            throw new InvalidArgumentException('Value Date is required');
        }

        if ($this->amount === null) {
            throw new InvalidArgumentException('Amount is required');
        }

        if ($this->beneficiaryInstitution === null) {
            throw new InvalidArgumentException('Beneficiary Institution is required');
        }

        return new Document(
            transactionReference: $this->transactionReference,
            relatedReference: $this->relatedReference,
            valueDate: $this->valueDate,
            currency: $this->currency,
            amount: $this->amount,
            beneficiaryInstitution: $this->beneficiaryInstitution,
            orderingInstitution: $this->orderingInstitution,
            sendersCorrespondent: $this->sendersCorrespondent,
            receiversCorrespondent: $this->receiversCorrespondent,
            intermediary: $this->intermediary,
            accountWithInstitution: $this->accountWithInstitution,
            senderToReceiverInfo: $this->senderToReceiverInfo,
            timeIndication: $this->timeIndication,
            isCoverPayment: $this->isCoverPayment,
            creationDateTime: $this->creationDateTime
        );
    }
}
