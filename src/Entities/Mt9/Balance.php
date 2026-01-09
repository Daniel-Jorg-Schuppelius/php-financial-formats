<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Balance.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Mt9;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\BalanceInterface;
use CommonToolkit\Enums\{CreditDebit, CurrencyCode};
use CommonToolkit\Helper\Data\CurrencyHelper;
use DateTimeImmutable;
use RuntimeException;

/**
 * Common Balance class for all MT9xx message types.
 * 
 * Represents balances in SWIFT MT messages:
 * - :60F: Opening Balance (Final)
 * - :60M: Opening Balance (Interim - MT942)
 * - :62F: Closing Balance (Final - MT940)
 * - :62M: Closing Balance (Interim - MT942)
 * - :64:  Closing Available Balance
 * - :65:  Forward Available Balance
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt9
 */
class Balance implements BalanceInterface {
    private CreditDebit $creditDebit;
    private DateTimeImmutable $date;
    private CurrencyCode $currency;
    private float $amount;
    private string $type;

    /**
     * @param CreditDebit $creditDebit Soll/Haben-Kennzeichen
     * @param DateTimeImmutable|string $date Date (Format: ymd for string)
     * @param CurrencyCode $currency Currency
     * @param float $amount Betrag
     * @param string $type Balance-Typ (F=Final, M=Interim, A=Available)
     */
    public function __construct(
        CreditDebit $creditDebit,
        DateTimeImmutable|string $date,
        CurrencyCode $currency,
        float $amount,
        string $type = 'F'
    ) {
        $this->creditDebit = $creditDebit;
        $this->date = $date instanceof DateTimeImmutable
            ? $date
            : (DateTimeImmutable::createFromFormat('ymd', $date) ?: throw new RuntimeException("Ungültiges Datum: $date"));
        $this->currency = $currency;
        $this->amount = round($amount, 2);
        $this->type = $type;
    }

    public function getCreditDebit(): CreditDebit {
        return $this->creditDebit;
    }

    public function getDate(): DateTimeImmutable {
        return $this->date;
    }

    public function getCurrency(): CurrencyCode {
        return $this->currency;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    /**
     * Returns the balance type.
     * F = Final (MT940), M = Interim (MT942), A = Available
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * Checks if this is a Final balance (MT940).
     */
    public function isFinal(): bool {
        return $this->type === 'F';
    }

    /**
     * Checks if this is an Interim balance (MT942).
     */
    public function isInterim(): bool {
        return $this->type === 'M';
    }

    public function isCredit(): bool {
        return $this->creditDebit === CreditDebit::CREDIT;
    }

    public function isDebit(): bool {
        return $this->creditDebit === CreditDebit::DEBIT;
    }

    /**
     * Returns the signed amount (positive for Credit, negative for Debit).
     */
    public function getSignedAmount(): float {
        return $this->isCredit() ? $this->amount : -$this->amount;
    }

    /**
     * Returns the formatted amount.
     */
    public function getFormattedAmount(?string $locale = null): string {
        return CurrencyHelper::format($this->amount, $this->currency, $locale);
    }

    /**
     * Serialisiert im SWIFT MT-Format.
     * Format: [C/D][Date YYMMDD][Currency][Amount with comma]
     */
    public function __toString(): string {
        return sprintf(
            '%s%s%s%s',
            $this->creditDebit->toMt940Code(),
            $this->date->format('ymd'),
            $this->currency->value,
            number_format($this->amount, 2, ',', '')
        );
    }
}
