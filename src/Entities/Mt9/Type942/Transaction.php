<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Transaction.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Mt9\Type942;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\MtTransactionAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt9\Purpose;
use CommonToolkit\FinancialFormats\Entities\Mt9\Reference;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\Helper\Data\CurrencyHelper;
use DateTimeImmutable;
use RuntimeException;

/**
 * MT942 Transaction - Interim Transaction Entry.
 * 
 * Represents a single intraday transaction (Field :61:)
 * with associated multi-purpose field (Field :86:).
 * 
 * Im Wesentlichen identisch mit MT940 Transaction, aber im
 * Kontext eines Intraday-Reports.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt9\Type942
 */
class Transaction extends MtTransactionAbstract {
    private Reference $reference;
    private ?Purpose $purpose;
    private ?string $purposeRaw;

    public function __construct(
        DateTimeImmutable|string $bookingDate,
        DateTimeImmutable|string|null $valutaDate,
        float $amount,
        CreditDebit $creditDebit,
        CurrencyCode $currency,
        Reference $reference,
        Purpose|string|null $purpose = null
    ) {
        $bookingDateParsed = $bookingDate instanceof DateTimeImmutable
            ? $bookingDate
            : (DateTimeImmutable::createFromFormat('ymd', $bookingDate)
                ?: throw new RuntimeException("Ungültiges Buchungsdatum: $bookingDate"));

        $valutaDateParsed = match (true) {
            $valutaDate instanceof DateTimeImmutable => $valutaDate,
            is_string($valutaDate) => $this->parseValutaDateString($bookingDateParsed, $valutaDate),
            default => null
        };

        parent::__construct(
            $bookingDateParsed,
            $valutaDateParsed,
            $amount,
            $creditDebit,
            $currency
        );

        $this->reference = $reference;
        if ($purpose instanceof Purpose) {
            $this->purpose = $purpose;
            $this->purposeRaw = $purpose->getFullText();
        } elseif (is_string($purpose) && trim($purpose) !== '') {
            $this->purpose = Purpose::fromString($purpose);
            $this->purposeRaw = $purpose;
        } else {
            $this->purpose = null;
            $this->purposeRaw = null;
        }
    }

    /**
     * Returns the transaction reference.
     */
    public function getReference(): Reference {
        return $this->reference;
    }

    /**
     * Returns the purpose of payment (Field :86:).
     */
    public function getPurpose(): ?string {
        return $this->purposeRaw;
    }

    /**
     * Generiert die SWIFT MT :61: und :86: Zeilen.
     */
    private function toMt942Lines(): array {
        $amountStr = CurrencyHelper::usToDe((string) $this->amount);

        $valutaStr = $this->valutaDate ? $this->valutaDate->format('ymd') : $this->bookingDate->format('ymd');

        $bookingDateStr = '';
        if ($this->valutaDate !== null && $this->bookingDate->format('ymd') !== $this->valutaDate->format('ymd')) {
            $bookingDateStr = $this->bookingDate->format('md');
        }

        $direction = $this->creditDebit->toMt940Code();

        $lines = [
            sprintf(':61:%s%s%s%s%s%s', $valutaStr, $bookingDateStr, $direction, $this->getCurrencyChar(), $amountStr, $this->reference->getBookingKeyWithCode() . ($this->reference->getReference() !== '' ? $this->reference->getReference() : 'NONREF')),
        ];
        if ($this->reference->getBankReference() !== null) {
            $lines[0] .= '//' . $this->reference->getBankReference();
        }

        if ($this->purpose !== null) {
            foreach ($this->purpose->toMt940Lines() as $line) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    private function parseValutaDateString(DateTimeImmutable $bookingDate, string $valutaDate): DateTimeImmutable {
        $valutaDate = trim($valutaDate);
        if (preg_match('/^\d{6}$/', $valutaDate)) {
            $parsed = DateTimeImmutable::createFromFormat('ymd', $valutaDate);
        } elseif (preg_match('/^\d{4}$/', $valutaDate)) {
            $parsed = DateTimeImmutable::createFromFormat('Ymd', $bookingDate->format('Y') . $valutaDate);
        } elseif (preg_match('/^\d{8}$/', $valutaDate)) {
            $parsed = DateTimeImmutable::createFromFormat('Ymd', $valutaDate);
        } else {
            $parsed = false;
        }

        return $parsed ?: throw new RuntimeException("Ungültiges Valutadatum: $valutaDate");
    }

    private function getCurrencyChar(): string {
        $currencyCode = $this->currency->value;
        return ($currencyCode !== '' && strtoupper($currencyCode) !== 'EUR')
            ? substr($currencyCode, -1)
            : '';
    }

    public function __toString(): string {
        return implode("\r\n", $this->toMt942Lines()) . "\r\n";
    }
}
