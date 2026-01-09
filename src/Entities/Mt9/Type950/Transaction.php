<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Transaction.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Mt9\Type950;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\MtTransactionAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt9\Reference;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\Helper\Data\CurrencyHelper;
use DateTimeImmutable;
use RuntimeException;

/**
 * MT950 Transaction - Statement Line.
 * 
 * Represents a single transaction (Field :61:) in an MT950 statement.
 * Simplified compared to MT940 - no :86: purpose field.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt9\Type950
 */
class Transaction extends MtTransactionAbstract {
    private Reference $reference;
    private ?string $supplementaryDetails;

    public function __construct(
        DateTimeImmutable|string $bookingDate,
        DateTimeImmutable|string|null $valutaDate,
        float $amount,
        CreditDebit $creditDebit,
        CurrencyCode $currency,
        Reference $reference,
        ?string $supplementaryDetails = null,
        bool $isReversal = false
    ) {
        $bookingDateParsed = $bookingDate instanceof DateTimeImmutable
            ? $bookingDate
            : (DateTimeImmutable::createFromFormat('ymd', $bookingDate)
                ?: throw new RuntimeException("Invalid booking date: $bookingDate"));

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
            $currency,
            $isReversal
        );

        $this->reference = $reference;
        $this->supplementaryDetails = $supplementaryDetails;
    }

    /**
     * Parses a valuta date string relative to the booking date.
     */
    private function parseValutaDateString(DateTimeImmutable $bookingDate, string $valutaDate): DateTimeImmutable {
        if (strlen($valutaDate) === 4) {
            // MMDD format - use year from booking date
            $fullDate = $bookingDate->format('y') . $valutaDate;
            $parsed = DateTimeImmutable::createFromFormat('ymd', $fullDate);
            if ($parsed) {
                return $parsed;
            }
        }

        // Try full YYMMDD format
        $parsed = DateTimeImmutable::createFromFormat('ymd', $valutaDate);
        if ($parsed) {
            return $parsed;
        }

        throw new RuntimeException("Invalid valuta date: $valutaDate");
    }

    /**
     * Returns the transaction reference.
     */
    public function getReference(): Reference {
        return $this->reference;
    }

    /**
     * Returns the supplementary details (Subfield 9 of :61:).
     */
    public function getSupplementaryDetails(): ?string {
        return $this->supplementaryDetails;
    }

    /**
     * Generates the SWIFT MT :61: line.
     * 
     * @return string[] Array with :61: line
     */
    public function toMt950Lines(): array {
        $amountStr = CurrencyHelper::usToDe((string) $this->amount);

        $valutaStr = $this->valutaDate ? $this->valutaDate->format('ymd') : $this->bookingDate->format('ymd');

        // Booking date only as MMDD if different from valuta
        $bookingDateStr = '';
        if ($this->valutaDate !== null && $this->bookingDate->format('ymd') !== $this->valutaDate->format('ymd')) {
            $bookingDateStr = $this->bookingDate->format('md');
        }

        $direction = $this->getMt940DirectionCode();

        $line = ':61:' . $valutaStr . $bookingDateStr . $direction . $amountStr;
        $line .= $this->reference->toSwift();

        if ($this->supplementaryDetails !== null) {
            $line .= "\r\n" . $this->supplementaryDetails;
        }

        return [$line];
    }

    /**
     * Returns the transaction as a string.
     */
    public function __toString(): string {
        return implode("\r\n", $this->toMt950Lines());
    }
}
