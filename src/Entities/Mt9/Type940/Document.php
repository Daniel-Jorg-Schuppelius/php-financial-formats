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

namespace CommonToolkit\FinancialFormats\Entities\Mt9\Type940;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\MtDocumentAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;
use CommonToolkit\FinancialFormats\Enums\MtType;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt940Generator;
use DateTimeImmutable;

/**
 * MT940 Document - Customer Statement Message.
 * 
 * End-of-day statement according to SWIFT standard.
 * Contains complete transactions of a day with Opening and Closing Balance.
 * 
 * Equivalent to CAMT.053 (Bank to Customer Statement).
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt9\Type940
 */
class Document extends MtDocumentAbstract {
    private Balance $openingBalance;
    private Balance $closingBalance;
    private ?Balance $closingAvailableBalance;
    private ?Balance $forwardAvailableBalance;

    /** @var Transaction[] */
    private array $transactions = [];

    public function __construct(
        string $accountId,
        string $referenceId,
        string $statementNumber,
        Balance $openingBalance,
        Balance $closingBalance,
        array $transactions = [],
        ?Balance $closingAvailableBalance = null,
        ?Balance $forwardAvailableBalance = null,
        ?DateTimeImmutable $creationDateTime = null
    ) {
        parent::__construct(
            $accountId,
            $referenceId,
            $statementNumber,
            $openingBalance->getCurrency(),
            $creationDateTime
        );

        $this->openingBalance = $openingBalance;
        $this->closingBalance = $closingBalance;
        $this->closingAvailableBalance = $closingAvailableBalance;
        $this->forwardAvailableBalance = $forwardAvailableBalance;
        $this->transactions = $transactions;
    }

    public function getMtType(): MtType {
        return MtType::MT940;
    }

    /**
     * Returns the Opening Balance.
     * Feld :60F: in SWIFT-Notation.
     */
    public function getOpeningBalance(): Balance {
        return $this->openingBalance;
    }

    /**
     * Returns the Closing Balance.
     * Feld :62F: in SWIFT-Notation.
     */
    public function getClosingBalance(): Balance {
        return $this->closingBalance;
    }

    /**
     * Returns the Closing Available Balance.
     * Feld :64: in SWIFT-Notation (optional).
     */
    public function getClosingAvailableBalance(): ?Balance {
        return $this->closingAvailableBalance;
    }

    /**
     * Returns the Forward Available Balance.
     * Feld :65: in SWIFT-Notation (optional).
     */
    public function getForwardAvailableBalance(): ?Balance {
        return $this->forwardAvailableBalance;
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

    public function countEntries(): int {
        return count($this->transactions);
    }

    /**
     * Berechnet die Summe aller Soll-Buchungen.
     */
    public function getTotalDebit(): float {
        return array_reduce(
            $this->transactions,
            fn(float $sum, Transaction $txn) => $sum + ($txn->isDebit() ? $txn->getAmount() : 0),
            0.0
        );
    }

    /**
     * Berechnet die Summe aller Haben-Buchungen.
     */
    public function getTotalCredit(): float {
        return array_reduce(
            $this->transactions,
            fn(float $sum, Transaction $txn) => $sum + ($txn->isCredit() ? $txn->getAmount() : 0),
            0.0
        );
    }

    public function __toString(): string {
        return (new Mt940Generator())->generate($this);
    }
}
