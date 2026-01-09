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

namespace CommonToolkit\FinancialFormats\Entities\Mt9\Type950;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\MtDocumentAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt950Generator;
use DateTimeImmutable;

/**
 * MT950 Document - Statement Message.
 * 
 * Statement message for netting systems and financial institution statements.
 * Similar to MT940 but used in different contexts (e.g., CHIPS, TARGET2).
 * 
 * Fields:
 * - :20: Transaction Reference Number (M)
 * - :25: Account Identification (M)
 * - :28C: Statement Number/Sequence Number (M)
 * - :60a: Opening Balance (M)
 * - :61: Statement Line (O, repetitive)
 * - :62a: Closing Balance (M)
 * - :64: Closing Available Balance (O)
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt9\Type950
 */
class Document extends MtDocumentAbstract {
    private Balance $openingBalance;
    private Balance $closingBalance;
    private ?Balance $closingAvailableBalance;

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
        ?DateTimeImmutable $creationDateTime = null,
        ?string $relatedReference = null
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
        $this->transactions = $transactions;
    }

    public function getMtType(): MtType {
        return MtType::MT950;
    }

    /**
     * Returns the Opening Balance.
     * Field :60F: in SWIFT notation.
     */
    public function getOpeningBalance(): Balance {
        return $this->openingBalance;
    }

    /**
     * Returns the Closing Balance.
     * Field :62F: in SWIFT notation.
     */
    public function getClosingBalance(): Balance {
        return $this->closingBalance;
    }

    /**
     * Returns the Closing Available Balance.
     * Field :64: in SWIFT notation (optional).
     */
    public function getClosingAvailableBalance(): ?Balance {
        return $this->closingAvailableBalance;
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
     * Calculates the total debit amount.
     */
    public function getTotalDebit(): float {
        return array_reduce(
            $this->transactions,
            fn(float $sum, Transaction $txn) => $sum + ($txn->isDebit() ? $txn->getAmount() : 0),
            0.0
        );
    }

    /**
     * Calculates the total credit amount.
     */
    public function getTotalCredit(): float {
        return array_reduce(
            $this->transactions,
            fn(float $sum, Transaction $txn) => $sum + ($txn->isCredit() ? $txn->getAmount() : 0),
            0.0
        );
    }

    public function __toString(): string {
        return (new Mt950Generator())->generate($this);
    }
}
