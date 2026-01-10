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

namespace CommonToolkit\FinancialFormats\Entities\Mt9\Type940;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9\TransactionAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt9\Purpose;
use CommonToolkit\FinancialFormats\Entities\Mt9\Reference;
use CommonToolkit\FinancialFormats\Enums\Mt\Mt940OutputFormat;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\Helper\Data\CurrencyHelper;
use DateTimeImmutable;
use RuntimeException;

/**
 * MT940 Transaction - Customer Statement Entry.
 * 
 * Represents a single transaction (Field :61:) with associated
 * Mehrzweckfeld (Feld :86:) im Tagesendeauszug.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt9\Type940
 */
class Transaction extends TransactionAbstract {
    private Reference $reference;
    private ?Purpose $purpose;
    private ?string $purposeRaw;
    private ?string $supplementaryDetails;

    public function __construct(
        DateTimeImmutable|string $bookingDate,
        DateTimeImmutable|string|null $valutaDate,
        float $amount,
        CreditDebit $creditDebit,
        CurrencyCode $currency,
        Reference $reference,
        Purpose|string|null $purpose = null,
        ?string $supplementaryDetails = null,
        bool $isReversal = false
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
            $currency,
            $isReversal
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
        $this->supplementaryDetails = $supplementaryDetails;
    }

    /**
     * Returns the transaction reference.
     */
    public function getReference(): Reference {
        return $this->reference;
    }

    /**
     * Returns the raw purpose text (Field :86:).
     * 
     * @deprecated Use getPurposeRaw() or getPurposeObject() instead.
     */
    public function getPurpose(): ?string {
        return $this->purposeRaw;
    }

    /**
     * Returns the raw purpose text (Field :86:).
     */
    public function getPurposeRaw(): ?string {
        return $this->purposeRaw;
    }

    /**
     * Returns the parsed purpose object with structured fields.
     * 
     * Supports:
     * - DATEV/DFÜ format (?00, ?10, ?20-?29, ?30-?34, ?60-?63)
     * - SWIFT keywords (/EREF/, /REMI/, /ORDP/, /BENM/, etc.)
     * - SEPA keywords (EREF+, MREF+, CRED+, SVWZ+, etc.)
     */
    public function getPurposeObject(): ?Purpose {
        return $this->purpose;
    }

    /**
     * Returns the supplementary details (Subfield 9 of :61:).
     * Contains additional information like exchange rate /EXCH/ or other details.
     * Maximum 34 characters.
     */
    public function getSupplementaryDetails(): ?string {
        return $this->supplementaryDetails;
    }

    /**
     * Generiert die SWIFT MT :61: und :86: Zeilen.
     *
     * @param Mt940OutputFormat $format Output format (SWIFT or DATEV)
     */
    public function toMt940Lines(Mt940OutputFormat $format = Mt940OutputFormat::SWIFT): array {
        $amountStr = CurrencyHelper::usToDe((string) $this->amount);

        // Laut DATEV-Spezifikation (Dok.-Nr. 9226962):
        // :61:[Valuta JJMMTT][Buchungsdatum MMTT optional][C/D/RC/RD][Währung optional][Betrag][N+Code][Referenz]//[Bankreferenz]
        $valutaStr = $this->valutaDate ? $this->valutaDate->format('ymd') : $this->bookingDate->format('ymd');

        // Buchungsdatum nur als MMTT, wenn unterschiedlich von Valuta
        $bookingDateStr = '';
        if ($this->valutaDate !== null && $this->bookingDate->format('ymd') !== $this->valutaDate->format('ymd')) {
            $bookingDateStr = $this->bookingDate->format('md');
        }

        // Both SWIFT and DATEV use RC/RD format per DATEV documentation (Dok-Nr. 9226962)
        $direction = $this->getMt940DirectionCode();

        $currencyCode = $this->currency->value;
        $currencyChar = ($currencyCode !== '' && strtoupper($currencyCode) !== 'EUR')
            ? substr($currencyCode, -1)
            : '';

        $bookingKey = $this->reference->getBookingKeyWithCode();
        $referenceStr = $this->reference->getReference() !== '' ? $this->reference->getReference() : 'NONREF';

        $lines = [
            sprintf(':61:%s%s%s%s%s%s', $valutaStr, $bookingDateStr, $direction, $currencyChar, $amountStr, $bookingKey . $referenceStr),
        ];
        if ($this->reference->getBankReference() !== null) {
            $lines[0] .= '//' . $this->reference->getBankReference();
        }

        // Subfield 9: Supplementary Details [34x] - optional, on next line
        if ($this->supplementaryDetails !== null && $this->supplementaryDetails !== '') {
            $lines[] = substr($this->supplementaryDetails, 0, 34);
        }

        if ($this->purpose !== null) {
            foreach ($this->purpose->toMt940Lines($format) as $line) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * Generates MT940 lines in DATEV format.
     */
    public function toDatevLines(): array {
        return $this->toMt940Lines(Mt940OutputFormat::DATEV);
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

    public function __toString(): string {
        return implode("\r\n", $this->toMt940Lines()) . "\r\n";
    }
}
