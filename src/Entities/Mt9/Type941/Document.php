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

namespace CommonToolkit\FinancialFormats\Entities\Mt9\Type941;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\MtDocumentAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;
use CommonToolkit\FinancialFormats\Enums\MtType;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt941Generator;
use DateTimeImmutable;

/**
 * MT941 Document - Balance Report.
 * 
 * Balance information without transaction details according to SWIFT standard.
 * Contains only Opening and Closing Balance as well as optional Available Balances.
 * 
 * Verwendung:
 * - Quick balance query without complete transaction list
 * - Account balance overview for Treasury/Cash Management
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt9\Type941
 */
class Document extends MtDocumentAbstract {
    private Balance $openingBalance;
    private Balance $closingBalance;
    private ?Balance $closingAvailableBalance;

    /** @var Balance[] Forward Available Balances */
    private array $forwardAvailableBalances = [];

    public function __construct(
        string $accountId,
        string $referenceId,
        string $statementNumber,
        Balance $openingBalance,
        Balance $closingBalance,
        ?Balance $closingAvailableBalance = null,
        array $forwardAvailableBalances = [],
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
        $this->forwardAvailableBalances = $forwardAvailableBalances;
    }

    public function getMtType(): MtType {
        return MtType::MT941;
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
     * Returns the Forward Available Balances.
     * Feld :65: in SWIFT-Notation (kann mehrfach vorkommen).
     * @return Balance[]
     */
    public function getForwardAvailableBalances(): array {
        return $this->forwardAvailableBalances;
    }

    /**
     * Adds a Forward Available Balance.
     */
    public function addForwardAvailableBalance(Balance $balance): void {
        $this->forwardAvailableBalances[] = $balance;
    }

    /**
     * MT941 contains no transactions.
     */
    public function countEntries(): int {
        return 0;
    }

    /**
     * Berechnet die Differenz zwischen Opening und Closing Balance.
     */
    public function getBalanceChange(): float {
        $openingAmount = $this->openingBalance->isCredit()
            ? $this->openingBalance->getAmount()
            : -$this->openingBalance->getAmount();

        $closingAmount = $this->closingBalance->isCredit()
            ? $this->closingBalance->getAmount()
            : -$this->closingBalance->getAmount();

        return $closingAmount - $openingAmount;
    }

    public function __toString(): string {
        return (new Mt941Generator())->generate($this);
    }
}
