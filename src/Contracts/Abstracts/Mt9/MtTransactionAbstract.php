<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : MtTransactionAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9;

use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\Helper\Data\CurrencyHelper;
use DateTimeImmutable;

/**
 * Abstract base class for MT9xx transactions (SWIFT Cash Management).
 * 
 * Gemeinsame Eigenschaften aller MT9-Transaktionstypen:
 * - Buchungsdatum / Valutadatum
 * - Amount and currency
 * - Soll/Haben-Kennzeichen
 * 
 * Entspricht Feld :61: in SWIFT-Notation.
 * 
 * @package CommonToolkit\Contracts\Abstracts\Common\Banking\Mt9
 */
abstract class MtTransactionAbstract {
    protected DateTimeImmutable $bookingDate;
    protected ?DateTimeImmutable $valutaDate;
    protected float $amount;
    protected CreditDebit $creditDebit;
    protected CurrencyCode $currency;
    protected bool $isReversal;

    public function __construct(
        DateTimeImmutable $bookingDate,
        ?DateTimeImmutable $valutaDate,
        float $amount,
        CreditDebit $creditDebit,
        CurrencyCode $currency,
        bool $isReversal = false
    ) {
        $this->bookingDate = $bookingDate;
        $this->valutaDate = $valutaDate;
        $this->amount = round(abs($amount), 2);
        $this->creditDebit = $creditDebit;
        $this->currency = $currency;
        $this->isReversal = $isReversal;
    }

    /**
     * Returns the booking date.
     */
    public function getBookingDate(): DateTimeImmutable {
        return $this->bookingDate;
    }

    /**
     * Alias for getBookingDate() - compatibility with MT940 conventions.
     */
    public function getDate(): DateTimeImmutable {
        return $this->bookingDate;
    }

    /**
     * Returns the value date.
     */
    public function getValutaDate(): ?DateTimeImmutable {
        return $this->valutaDate;
    }

    /**
     * Returns the amount (immer positiv).
     */
    public function getAmount(): float {
        return $this->amount;
    }

    /**
     * Returns the signed amount.
     */
    public function getSignedAmount(): float {
        return $this->creditDebit === CreditDebit::DEBIT ? -$this->amount : $this->amount;
    }

    /**
     * Returns the debit/credit indicator.
     */
    public function getCreditDebit(): CreditDebit {
        return $this->creditDebit;
    }

    /**
     * Returns the currency.
     */
    public function getCurrency(): CurrencyCode {
        return $this->currency;
    }

    /**
     * Checks if this is a debit entry.
     */
    public function isDebit(): bool {
        return $this->creditDebit === CreditDebit::DEBIT;
    }

    /**
     * Checks if this is a credit entry.
     */
    public function isCredit(): bool {
        return $this->creditDebit === CreditDebit::CREDIT;
    }

    /**
     * Checks if this is a reversal (Storno) entry.
     */
    public function isReversal(): bool {
        return $this->isReversal;
    }

    /**
     * Returns the MT940 direction code in SWIFT format (C, D, RC, RD).
     * 
     * Format: R prefix for reversals per SWIFT standard.
     * DATEV documentation (Dok-Nr. 9226962) also specifies: RC = Storno Credit, RD = Storno Debit
     */
    public function getMt940DirectionCode(): string {
        $base = $this->creditDebit->toMt940Code();
        return $this->isReversal ? 'R' . $base : $base;
    }

    /**
     * Returns the sign as string.
     */
    public function getSign(): string {
        return $this->creditDebit->getSymbol();
    }

    /**
     * Returns the formatted amount.
     */
    public function getFormattedAmount(?string $locale = null): string {
        return CurrencyHelper::format($this->amount, $this->currency, $locale);
    }

    /**
     * Serialisiert die Transaktion im SWIFT MT-Format.
     */
    abstract public function __toString(): string;
}
