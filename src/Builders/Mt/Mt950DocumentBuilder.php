<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt950DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\Mt;

use CommonToolkit\FinancialFormats\Entities\Mt9\Type950\Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type950\Transaction;
use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;
use CommonToolkit\Enums\CreditDebit;
use InvalidArgumentException;
use RuntimeException;

/**
 * Builder for MT950 Statement Message.
 * 
 * Creates MT950 documents for netting systems and FI statements.
 * 
 * Usage:
 * ```php
 * $document = Mt950DocumentBuilder::create('STMT-001')
 *     ->account('DE89370400440532013000')
 *     ->statementNumber('00001')
 *     ->openingBalance($openingBalance)
 *     ->closingBalance($closingBalance)
 *     ->addTransaction($transaction)
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\Builders\Common\Banking\Mt
 */
final class Mt950DocumentBuilder {
    private string $referenceId;
    private ?string $accountId = null;
    private ?string $relatedReference = null;
    private string $statementNumber = '00000';
    private bool $skipBalanceValidation = false;

    /** @var Transaction[] */
    private array $transactions = [];

    private ?Balance $openingBalance = null;
    private ?Balance $closingBalance = null;
    private ?Balance $closingAvailableBalance = null;

    private function __construct(string $referenceId) {
        $this->referenceId = $referenceId;
    }

    /**
     * Creates a new builder instance.
     * 
     * @param string $referenceId Field :20: Transaction reference
     */
    public static function create(string $referenceId): self {
        return new self($referenceId);
    }

    /**
     * Sets the account identification (Field :25:).
     */
    public function account(string $accountId): self {
        $this->accountId = $accountId;
        return $this;
    }

    /**
     * Sets the related reference (Field :21:).
     */
    public function relatedReference(string $reference): self {
        $this->relatedReference = $reference;
        return $this;
    }

    /**
     * Sets the statement number (Field :28C:).
     */
    public function statementNumber(string $number): self {
        $this->statementNumber = $number;
        return $this;
    }

    /**
     * Skip balance validation when building from parsed data.
     */
    public function skipBalanceValidation(bool $skip = true): self {
        $this->skipBalanceValidation = $skip;
        return $this;
    }

    /**
     * Sets the opening balance (Field :60F:).
     */
    public function openingBalance(Balance $balance): self {
        $this->openingBalance = $balance;
        return $this;
    }

    /**
     * Sets the closing balance (Field :62F:).
     */
    public function closingBalance(Balance $balance): self {
        $this->closingBalance = $balance;
        return $this;
    }

    /**
     * Sets the closing available balance (Field :64:).
     */
    public function closingAvailableBalance(?Balance $balance): self {
        $this->closingAvailableBalance = $balance;
        return $this;
    }

    /**
     * Adds a transaction (Field :61:).
     */
    public function addTransaction(Transaction $transaction): self {
        $this->transactions[] = $transaction;
        return $this;
    }

    /**
     * Adds multiple transactions.
     * 
     * @param Transaction[] $transactions
     */
    public function addTransactions(array $transactions): self {
        foreach ($transactions as $transaction) {
            if (!$transaction instanceof Transaction) {
                throw new InvalidArgumentException('All elements must be Transaction instances.');
            }
            $this->transactions[] = $transaction;
        }
        return $this;
    }

    /**
     * Builds the MT950 document.
     * 
     * @throws InvalidArgumentException if required fields are missing
     * @throws RuntimeException if balance validation fails
     */
    public function build(): Document {
        if ($this->accountId === null) {
            throw new InvalidArgumentException('Account ID is required');
        }
        if ($this->openingBalance === null) {
            throw new InvalidArgumentException('Opening balance is required');
        }
        if ($this->closingBalance === null) {
            throw new InvalidArgumentException('Closing balance is required');
        }

        // Validate balance if not skipped
        if (!$this->skipBalanceValidation) {
            $this->validateBalances();
        }

        return new Document(
            accountId: $this->accountId,
            referenceId: $this->referenceId,
            statementNumber: $this->statementNumber,
            openingBalance: $this->openingBalance,
            closingBalance: $this->closingBalance,
            transactions: $this->transactions,
            closingAvailableBalance: $this->closingAvailableBalance,
            relatedReference: $this->relatedReference
        );
    }

    /**
     * Validates that the balances are consistent with transactions.
     */
    private function validateBalances(): void {
        $expectedClosing = $this->openingBalance->getSignedAmount();

        foreach ($this->transactions as $transaction) {
            if ($transaction->isCredit()) {
                $expectedClosing += $transaction->getAmount();
            } else {
                $expectedClosing -= $transaction->getAmount();
            }
        }

        $actualClosing = $this->closingBalance->getSignedAmount();

        // Allow small rounding differences (0.01)
        if (abs($expectedClosing - $actualClosing) > 0.01) {
            throw new RuntimeException(sprintf(
                'Balance mismatch: expected %.2f, got %.2f. Use skipBalanceValidation() to override.',
                $expectedClosing,
                $actualClosing
            ));
        }
    }
}
