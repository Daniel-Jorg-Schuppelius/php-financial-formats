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

namespace CommonToolkit\FinancialFormats\Entities\Mt9\Type942;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\DocumentAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt942Generator;
use DateTimeImmutable;

/**
 * MT942 Document - Interim Transaction Report.
 * 
 * Intraday transaction information according to SWIFT standard.
 * Contains transactions since the last report with interim balances.
 * 
 * Equivalent to CAMT.052 (Bank to Customer Account Report).
 * 
 * Unterschiede zu MT940:
 * - Uses :60M: (Interim) instead of :60F: (Final) for Opening Balance
 * - Uses :62M: (Interim) instead of :62F: (Final) for Closing Balance
 * - Can be created multiple times per day
 * - Contains Field :34F: for Floor Limit Indicator
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt9\Type942
 */
class Document extends DocumentAbstract {
    private ?Balance $openingBalance;
    private Balance $closingBalance;
    private ?float $floorLimitIndicator;
    private ?DateTimeImmutable $dateTimeIndicator;

    /** @var Transaction[] */
    private array $transactions = [];

    public function __construct(
        string $accountId,
        string $referenceId,
        string $statementNumber,
        Balance $closingBalance,
        array $transactions = [],
        ?Balance $openingBalance = null,
        ?float $floorLimitIndicator = null,
        ?DateTimeImmutable $dateTimeIndicator = null,
        ?DateTimeImmutable $creationDateTime = null,
        ?string $relatedReference = null
    ) {
        parent::__construct(
            $accountId,
            $referenceId,
            $statementNumber,
            $closingBalance->getCurrency(),
            $creationDateTime,
            $relatedReference
        );

        $this->openingBalance = $openingBalance;
        $this->closingBalance = $closingBalance;
        $this->floorLimitIndicator = $floorLimitIndicator;
        $this->dateTimeIndicator = $dateTimeIndicator;
        $this->transactions = $transactions;
    }

    public function getMtType(): MtType {
        return MtType::MT942;
    }

    /**
     * Returns the Opening Balance (optional for MT942).
     * Feld :60M: in SWIFT-Notation.
     */
    public function getOpeningBalance(): ?Balance {
        return $this->openingBalance;
    }

    /**
     * Returns the Closing Balance.
     * Feld :62M: in SWIFT-Notation.
     */
    public function getClosingBalance(): Balance {
        return $this->closingBalance;
    }

    /**
     * Returns the Floor Limit Indicator.
     * Feld :34F: - Transaktionen unter diesem Betrag werden nicht einzeln gemeldet.
     */
    public function getFloorLimitIndicator(): ?float {
        return $this->floorLimitIndicator;
    }

    /**
     * Returns the DateTime Indicator.
     * Feld :13D: - Zeitpunkt der Erstellung des Reports.
     */
    public function getDateTimeIndicator(): ?DateTimeImmutable {
        return $this->dateTimeIndicator;
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
     * Feld :90D: Anzahl und Summe der Soll-Buchungen.
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
     * Feld :90C: Anzahl und Summe der Haben-Buchungen.
     */
    public function getTotalCredit(): float {
        return array_reduce(
            $this->transactions,
            fn(float $sum, Transaction $txn) => $sum + ($txn->isCredit() ? $txn->getAmount() : 0),
            0.0
        );
    }

    /**
     * Counts the debit entries.
     */
    public function countDebitEntries(): int {
        return count(array_filter($this->transactions, fn(Transaction $txn) => $txn->isDebit()));
    }

    /**
     * Counts the credit entries.
     */
    public function countCreditEntries(): int {
        return count(array_filter($this->transactions, fn(Transaction $txn) => $txn->isCredit()));
    }

    public function __toString(): string {
        return (new Mt942Generator())->generate($this);
    }
}
