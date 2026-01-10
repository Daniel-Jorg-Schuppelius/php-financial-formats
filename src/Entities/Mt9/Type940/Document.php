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

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\DocumentAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
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
class Document extends DocumentAbstract {
    private Balance $openingBalance;
    private Balance $closingBalance;
    private ?Balance $closingAvailableBalance;
    /** @var Balance[] */
    private array $forwardAvailableBalances;
    private ?string $statementInfo;

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
        Balance|array|null $forwardAvailableBalances = null,
        ?DateTimeImmutable $creationDateTime = null,
        ?string $relatedReference = null,
        ?string $statementInfo = null
    ) {
        parent::__construct(
            $accountId,
            $referenceId,
            $statementNumber,
            $openingBalance->getCurrency(),
            $creationDateTime,
            $relatedReference
        );

        $this->openingBalance = $openingBalance;
        $this->closingBalance = $closingBalance;
        $this->closingAvailableBalance = $closingAvailableBalance;
        // Support both single Balance and array for backwards compatibility
        if ($forwardAvailableBalances instanceof Balance) {
            $this->forwardAvailableBalances = [$forwardAvailableBalances];
        } elseif (is_array($forwardAvailableBalances)) {
            $this->forwardAvailableBalances = $forwardAvailableBalances;
        } else {
            $this->forwardAvailableBalances = [];
        }
        $this->statementInfo = $statementInfo;
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
     * Returns the first Forward Available Balance for backwards compatibility.
     * Feld :65: in SWIFT-Notation (optional).
     * @deprecated Use getForwardAvailableBalances() for multiple balances
     */
    public function getForwardAvailableBalance(): ?Balance {
        return $this->forwardAvailableBalances[0] ?? null;
    }

    /**
     * Returns all Forward Available Balances.
     * Feld :65: in SWIFT-Notation (optional, can be repeated).
     * Indicates funds available at future dates.
     * @return Balance[]
     */
    public function getForwardAvailableBalances(): array {
        return $this->forwardAvailableBalances;
    }

    /**
     * Returns the statement-level information.
     * Feld :86: nach :62: in SWIFT-Notation (optional).
     * Contains additional information about the statement as a whole.
     */
    public function getStatementInfo(): ?string {
        return $this->statementInfo;
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
