<?php
/*
 * Created on   : Thu May 09 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt940DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\Mt;

use CommonToolkit\FinancialFormats\Converters\Banking\CamtToMt940Converter;
use CommonToolkit\FinancialFormats\Converters\Banking\MtInterConverter;
use CommonToolkit\FinancialFormats\Converters\DATEV\BankTransactionToMt940Converter;
use CommonToolkit\FinancialFormats\Entities\DATEV\Documents\BankTransaction;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type52\Document as Camt052Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53\Document as Camt053Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type54\Document as Camt054Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type940\Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type940\Transaction;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type941\Document as Mt941Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type942\Document as Mt942Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;
use CommonToolkit\Enums\CreditDebit;
use InvalidArgumentException;
use RuntimeException;

final class Mt940DocumentBuilder {
    private string $accountId;
    private string $referenceId = 'COMMON';
    private ?string $relatedReference = null;
    private string $statementNumber = '00000';
    private bool $skipBalanceValidation = false;

    /** @var Transaction[] */
    private array $transactions = [];

    private ?Balance $openingBalance = null;
    private ?Balance $closingBalance = null;
    private ?Balance $closingAvailableBalance = null;
    /** @var Balance[] */
    private array $forwardAvailableBalances = [];
    private ?string $statementInfo = null;

    /**
     * Skip balance validation when building from parsed data.
     * Real-world bank data may have rounding differences or intermediate transactions.
     */
    public function skipBalanceValidation(bool $skip = true): self {
        $clone = clone $this;
        $clone->skipBalanceValidation = $skip;
        return $clone;
    }

    public function setAccountId(string $accountId): self {
        $clone = clone $this;
        $clone->accountId = $accountId;
        return $clone;
    }

    public function setReferenceId(string $referenceId): self {
        $clone = clone $this;
        $clone->referenceId = $referenceId;
        return $clone;
    }

    /**
     * Set the Related Reference (:21:).
     * Contains the field 20 of the MT 920 request message if this statement was requested.
     */
    public function setRelatedReference(?string $relatedReference): self {
        $clone = clone $this;
        $clone->relatedReference = $relatedReference;
        return $clone;
    }

    public function setStatementNumber(string $statementNumber): self {
        $clone = clone $this;
        $clone->statementNumber = $statementNumber;
        return $clone;
    }

    public function addTransaction(Transaction $transaction): self {
        $clone = clone $this;
        $clone->transactions[] = $transaction;
        return $clone;
    }

    public function addTransactions(array $transactions): self {
        foreach ($transactions as $transaction) {
            if (!$transaction instanceof Transaction) {
                throw new InvalidArgumentException('Alle Elemente müssen vom Typ Transaction sein.');
            }
            $this->transactions[] = $transaction;
        }
        return $this;
    }

    public function setOpeningBalance(Balance $balance): self {
        $clone = clone $this;
        $clone->openingBalance = $balance;
        return $clone;
    }

    public function setClosingBalance(Balance $balance): self {
        $clone = clone $this;
        $clone->closingBalance = $balance;
        return $clone;
    }

    /**
     * Set the Closing Available Balance (:64:).
     * Indicates funds available to the account owner.
     */
    public function setClosingAvailableBalance(?Balance $balance): self {
        $clone = clone $this;
        $clone->closingAvailableBalance = $balance;
        return $clone;
    }

    /**
     * Set the Forward Available Balance (:65:).
     * Indicates funds available at a future date.
     * @deprecated Use addForwardAvailableBalance() for multiple balances
     */
    public function setForwardAvailableBalance(?Balance $balance): self {
        $clone = clone $this;
        $clone->forwardAvailableBalances = $balance !== null ? [$balance] : [];
        return $clone;
    }

    /**
     * Add a Forward Available Balance (:65:).
     * Can be repeated for multiple future dates.
     */
    public function addForwardAvailableBalance(Balance $balance): self {
        $clone = clone $this;
        $clone->forwardAvailableBalances[] = $balance;
        return $clone;
    }

    /**
     * Set all Forward Available Balances (:65:).
     * @param Balance[] $balances
     */
    public function setForwardAvailableBalances(array $balances): self {
        $clone = clone $this;
        $clone->forwardAvailableBalances = $balances;
        return $clone;
    }

    /**
     * Set the Statement-Level Information (:86: after :62:).
     * Contains additional information about the statement as a whole.
     */
    public function setStatementInfo(?string $statementInfo): self {
        $clone = clone $this;
        $clone->statementInfo = $statementInfo;
        return $clone;
    }

    public function build(): Document {
        if (!$this->openingBalance && !$this->closingBalance) {
            throw new RuntimeException("Mindestens ein Saldo (Opening oder Closing) muss angegeben werden.");
        }

        if (!$this->openingBalance) {
            $opening = $this->reverseCalculateBalance($this->closingBalance);
            $closing = $this->closingBalance;
        } elseif (!$this->closingBalance) {
            $opening = $this->openingBalance;
            $closing = $this->calculateClosingBalance($opening);
        } else {
            $opening = $this->openingBalance;
            $closing = $this->closingBalance;

            if (!$this->skipBalanceValidation) {
                $expected = $this->calculateClosingBalance($opening);
                if ((string) $expected !== (string) $closing) {
                    throw new RuntimeException("Opening- und Closing-Salden stimmen nicht überein. Erwartet: " . $expected);
                }
            }
        }

        return new Document(
            $this->accountId,
            $this->referenceId,
            $this->statementNumber,
            $opening,
            $closing,
            $this->transactions,
            $this->closingAvailableBalance,
            $this->forwardAvailableBalances,
            null, // creationDateTime
            $this->relatedReference,
            $this->statementInfo
        );
    }

    private function calculateClosingBalance(Balance $opening): Balance {
        $total = $opening->isDebit() ? -$opening->getAmount() : $opening->getAmount();

        // Letztes Transaktionsdatum ermitteln
        $lastDate = $opening->getDate();
        foreach ($this->transactions as $txn) {
            $sign = $txn->getCreditDebit() === CreditDebit::CREDIT ? 1 : -1;
            $total += $sign * $txn->getAmount();
            $lastDate = $txn->getDate(); // Letztes Datum speichern
        }

        $direction = $total >= 0 ? CreditDebit::CREDIT : CreditDebit::DEBIT;
        return new Balance($direction, $lastDate, $opening->getCurrency(), abs($total));
    }

    private function reverseCalculateBalance(Balance $closing): Balance {
        $total = $closing->isDebit() ? -$closing->getAmount() : $closing->getAmount();

        foreach ($this->transactions as $txn) {
            $sign = $txn->getCreditDebit() === CreditDebit::CREDIT ? 1 : -1;
            $total -= $sign * $txn->getAmount();
        }

        $direction = $total >= 0 ? CreditDebit::CREDIT : CreditDebit::DEBIT;
        return new Balance($direction, $closing->getDate(), $closing->getCurrency(), abs($total));
    }

    // =========================================================================
    // Convenience Factory Methods für Format-Konvertierung
    // =========================================================================

    /**
     * Konvertiert ein CAMT.052 Dokument zu MT940.
     * 
     * @param Camt052Document $document CAMT.052 Account Report
     * @return Document MT940 Dokument
     */
    public static function fromCamt052(Camt052Document $document): Document {
        return CamtToMt940Converter::fromCamt052($document);
    }

    /**
     * Konvertiert ein CAMT.053 Dokument zu MT940.
     * 
     * @param Camt053Document $document CAMT.053 Bank Statement
     * @return Document MT940 Dokument
     */
    public static function fromCamt053(Camt053Document $document): Document {
        return CamtToMt940Converter::fromCamt053($document);
    }

    /**
     * Konvertiert ein CAMT.054 Dokument zu MT940.
     * 
     * @param Camt054Document $document CAMT.054 Debit/Credit Notification
     * @return Document MT940 Dokument
     */
    public static function fromCamt054(Camt054Document $document): Document {
        return CamtToMt940Converter::fromCamt054($document);
    }

    /**
     * Erstellt ein MT940 Dokument aus einem MT941 Dokument.
     *
     * @param Mt941Document $mt941 Das MT941 Dokument
     * @return Document Das konvertierte MT940 Dokument
     */
    public static function fromMt941(Mt941Document $mt941): Document {
        return MtInterConverter::mt941ToMt940($mt941);
    }

    /**
     * Erstellt ein MT940 Dokument aus einem MT942 Dokument.
     *
     * @param Mt942Document $mt942 Das MT942 Dokument
     * @return Document Das konvertierte MT940 Dokument
     */
    public static function fromMt942(Mt942Document $mt942): Document {
        return MtInterConverter::mt942ToMt940($mt942);
    }

    /**
     * Erstellt ein MT940 Dokument aus einem DATEV BankTransaction-Dokument.
     *
     * @param BankTransaction $document DATEV ASCII-Weiterverarbeitungsdokument
     * @param float|null $openingBalanceAmount Anfangssaldo (optional, wird sonst berechnet)
     * @param CreditDebit|null $openingBalanceCreditDebit Credit/Debit des Anfangssaldos
     * @return Document Das konvertierte MT940 Dokument
     */
    public static function fromBankTransaction(BankTransaction $document, ?float $openingBalanceAmount = null, ?CreditDebit $openingBalanceCreditDebit = null): Document {
        return BankTransactionToMt940Converter::convert($document, $openingBalanceAmount, $openingBalanceCreditDebit);
    }
}
