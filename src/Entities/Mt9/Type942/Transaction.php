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
    private ?string $purpose;

    public function __construct(
        DateTimeImmutable|string $bookingDate,
        DateTimeImmutable|string|null $valutaDate,
        float $amount,
        CreditDebit $creditDebit,
        CurrencyCode $currency,
        Reference $reference,
        ?string $purpose = null
    ) {
        $bookingDateParsed = $bookingDate instanceof DateTimeImmutable
            ? $bookingDate
            : (DateTimeImmutable::createFromFormat('ymd', $bookingDate)
                ?: throw new RuntimeException("Ungültiges Buchungsdatum: $bookingDate"));

        $valutaDateParsed = match (true) {
            $valutaDate instanceof DateTimeImmutable => $valutaDate,
            is_string($valutaDate) => (DateTimeImmutable::createFromFormat('Ymd', $bookingDateParsed->format('Y') . $valutaDate)
                ?: throw new RuntimeException("Ungültiges Valutadatum: $valutaDate")),
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
        $this->purpose = $purpose;
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
        return $this->purpose;
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
            sprintf(':61:%s%s%s%s%s', $valutaStr, $bookingDateStr, $direction, $amountStr, (string) $this->reference),
        ];

        // :86: Mehrzweckfeld
        $segments = str_split($this->purpose ?? '', 27);
        $first = array_shift($segments);
        $lines[] = ':86:' . ($first ?? '');

        $i = 20;
        foreach ($segments as $segment) {
            if ($i > 29 && $i < 60) {
                $i = 60;
            }
            if ($i > 63) {
                break;
            }
            $lines[] = sprintf('?%02d%s', $i++, $segment);
        }

        return $lines;
    }

    public function __toString(): string {
        return implode("\r\n", $this->toMt942Lines()) . "\r\n";
    }
}
