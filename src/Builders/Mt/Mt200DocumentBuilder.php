<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt200DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\Mt;

use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Entities\Mt2\Type200\Document;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder for MT200 Financial Institution Transfer for its Own Account.
 * 
 * Creates FI transfers for the sending institution's own account.
 * 
 * Usage:
 * ```php
 * $document = Mt200DocumentBuilder::create('REF-001')
 *     ->valueDate(new DateTimeImmutable('2024-03-15'))
 *     ->amount(100000.00, CurrencyCode::Euro)
 *     ->accountWithInstitution('DEUTDEFFXXX')
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\Builders\Mt
 */
final class Mt200DocumentBuilder {
    private string $transactionReference;
    private ?DateTimeImmutable $valueDate = null;
    private CurrencyCode $currency = CurrencyCode::Euro;
    private ?float $amount = null;
    private ?Party $accountWithInstitution = null;
    private ?Party $sendersCorrespondent = null;
    private ?Party $intermediary = null;
    private ?string $senderToReceiverInfo = null;
    private ?DateTimeImmutable $creationDateTime = null;

    private function __construct(string $transactionReference) {
        if (strlen($transactionReference) > 16) {
            throw new InvalidArgumentException('Transaction Reference darf maximal 16 Zeichen lang sein');
        }
        $this->transactionReference = $transactionReference;
        $this->creationDateTime = new DateTimeImmutable();
    }

    /**
     * Creates new builder with Transaction Reference.
     */
    public static function create(string $transactionReference): self {
        return new self($transactionReference);
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
     * Sets the Account With Institution (Field :57a:).
     */
    public function accountWithInstitution(string $bic, ?string $account = null): self {
        $clone = clone $this;
        $clone->accountWithInstitution = new Party(account: $account, bic: $bic);
        return $clone;
    }

    /**
     * Sets the Account With Institution with complete Party.
     */
    public function accountWithInstitutionParty(Party $party): self {
        $clone = clone $this;
        $clone->accountWithInstitution = $party;
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
     * Sets the Intermediary (Field :56a:).
     */
    public function intermediary(string $bic, ?string $account = null): self {
        $clone = clone $this;
        $clone->intermediary = new Party(account: $account, bic: $bic);
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
     * Sets the creation datetime.
     */
    public function withCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    /**
     * Builds the MT200 document.
     */
    public function build(): Document {
        if ($this->valueDate === null) {
            throw new InvalidArgumentException('Value Date is required');
        }

        if ($this->amount === null) {
            throw new InvalidArgumentException('Amount is required');
        }

        if ($this->accountWithInstitution === null) {
            throw new InvalidArgumentException('Account With Institution is required');
        }

        return new Document(
            transactionReference: $this->transactionReference,
            valueDate: $this->valueDate,
            currency: $this->currency,
            amount: $this->amount,
            accountWithInstitution: $this->accountWithInstitution,
            sendersCorrespondent: $this->sendersCorrespondent,
            intermediary: $this->intermediary,
            senderToReceiverInfo: $this->senderToReceiverInfo,
            creationDateTime: $this->creationDateTime
        );
    }
}
