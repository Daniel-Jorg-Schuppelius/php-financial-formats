<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : BankTransactionConverterAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV;

use CommonToolkit\Entities\CSV\DataField;
use CommonToolkit\Entities\CSV\DataLine;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\ASCII\BankTransactionHeaderField as F;
use CommonToolkit\Helper\Data\BankHelper;
use CommonToolkit\Helper\Data\CurrencyHelper;
use DateTimeImmutable;

/**
 * Abstract base class for BankTransaction converters.
 * 
 * Provides common helper methods for conversion between
 * DATEV ASCII-Weiterverarbeitungsdateien und Banking-Formaten bereit.
 * 
 * @package CommonToolkit\Contracts\Abstracts\DATEV
 */
abstract class BankTransactionConverterAbstract {
    /** Maximum length for purpose fields (DATEV standard). */
    protected const VERWENDUNGSZWECK_MAX_LENGTH = 27;

    /** Deutsches Datumsformat für DATEV-Export. */
    protected const DATE_FORMAT = 'd.m.Y';

    /** Standard-Transaktionscode wenn keiner angegeben. */
    protected const DEFAULT_TRANSACTION_CODE = 'TRF';

    /** Default currency wenn keine angegeben. */
    protected const DEFAULT_CURRENCY = 'EUR';

    /**
     * List of all purpose fields in correct order.
     * 
     * @return F[]
     */
    protected static function getPurposeFields(): array {
        return [
            F::VERWENDUNGSZWECK_1,
            F::VERWENDUNGSZWECK_2,
            F::VERWENDUNGSZWECK_3,
            F::VERWENDUNGSZWECK_4,
            F::VERWENDUNGSZWECK_5,
            F::VERWENDUNGSZWECK_6,
            F::VERWENDUNGSZWECK_7,
            F::VERWENDUNGSZWECK_8,
            F::VERWENDUNGSZWECK_9,
            F::VERWENDUNGSZWECK_10,
            F::VERWENDUNGSZWECK_11,
            F::VERWENDUNGSZWECK_12,
            F::VERWENDUNGSZWECK_13,
            F::VERWENDUNGSZWECK_14,
        ];
    }

    /**
     * Gets the value of a field by header enum from a field array.
     * 
     * @param array $fields Array der DataField-Objekte
     * @param F $field Header field definition
     * @return string Field value or empty string
     */
    protected static function getField(array $fields, F $field): string {
        $idx = $field->index();
        return ($idx >= 0 && count($fields) > $idx) ? trim($fields[$idx]->getValue()) : '';
    }

    /**
     * Gets the value of a field preserving all spaces (for purpose fields).
     * 
     * This is important for VERWENDUNGSZWECK fields where both leading
     * and trailing spaces are significant for proper 27-character block alignment.
     * 
     * @param array $fields Array der DataField-Objekte
     * @param F $field Header field definition
     * @return string Field value or empty string
     */
    protected static function getFieldRaw(array $fields, F $field): string {
        $idx = $field->index();
        return ($idx >= 0 && count($fields) > $idx) ? $fields[$idx]->getValue() : '';
    }

    /**
     * Creates an initialized field array for DATEV export.
     * 
     * @return string[] Array mit 34 Leerstrings
     */
    protected static function initializeFieldValues(): array {
        return array_fill(0, 34, '');
    }

    /**
     * Converts a field array to DataField objects.
     * 
     * @param string[] $values Field values
     * @return DataField[]
     */
    protected static function valuesToDataFields(array $values): array {
        return array_map(fn(string $v) => new DataField($v), $values);
    }

    /**
     * Creates a DataLine from a field values array.
     * 
     * @param string[] $values Field values
     * @return DataLine
     */
    protected static function createDataLine(array $values): DataLine {
        return new DataLine(self::valuesToDataFields($values));
    }

    /**
     * Formatiert einen Betrag im DATEV-Format (deutsches Format mit Vorzeichen).
     * 
     * @param float $amount Betrag
     * @param CreditDebit $creditDebit Credit/Debit-Kennzeichen
     * @return string Formatted amount with quotes
     */
    protected static function formatAmount(float $amount, CreditDebit $creditDebit): string {
        $sign = $creditDebit === CreditDebit::CREDIT ? '+' : '-';
        return '"' . $sign . CurrencyHelper::usToDe(number_format($amount, 2, '.', '')) . '"';
    }

    /**
     * Parst einen DATEV-Betrag (deutsches Format mit Vorzeichen).
     * 
     * @param string $amountStr Betrags-String
     * @return array{amount: float, creditDebit: CreditDebit}
     */
    protected static function parseAmount(string $amountStr): array {
        $amount = (float) CurrencyHelper::deToUs(ltrim($amountStr, '+'));
        $creditDebit = str_starts_with($amountStr, '-') ? CreditDebit::DEBIT : CreditDebit::CREDIT;

        return [
            'amount' => abs($amount),
            'creditDebit' => $creditDebit,
        ];
    }

    /**
     * Parses a currency from a string.
     * 
     * @param string $currencyStr Currency string (e.g. "EUR")
     * @return CurrencyCode
     */
    protected static function parseCurrency(string $currencyStr): CurrencyCode {
        if (empty($currencyStr)) {
            return CurrencyCode::Euro;
        }
        return CurrencyCode::tryFrom(strtoupper($currencyStr)) ?? CurrencyCode::Euro;
    }

    /**
     * Parst ein Datum in verschiedenen Formaten.
     * 
     * Supported formats:
     * - ISO: Y-m-d (2025-12-27)
     * - Deutsch: d.m.Y, d.m.y (27.12.2025, 27.12.25)
     * - Kompakt: Ymd, ymd, dmY, dmy
     * - Alternativ: d/m/Y, d-m-Y
     * 
     * @param string $dateStr Datums-String
     * @return DateTimeImmutable|null Geparster Zeitstempel oder null
     */
    protected static function parseDate(string $dateStr): ?DateTimeImmutable {
        $dateStr = trim($dateStr);

        if (empty($dateStr)) {
            return null;
        }

        $formats = [
            'Y-m-d',      // ISO Format
            'd.m.Y',      // Deutsches Format
            'd.m.y',      // Deutsches Format kurz
            'Ymd',        // Kompakt
            'ymd',        // Kompakt kurz
            'dmY',        // Kompakt deutsch
            'dmy',        // Kompakt deutsch kurz
            'd/m/Y',      // Alternativ
            'd-m-Y',      // Alternativ
        ];

        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat($format, $dateStr);
            if ($date !== false) {
                // Bei 2-stelligem Jahr: 00-30 = 20XX, 31-99 = 19XX
                if (in_array($format, ['ymd', 'dmy', 'd.m.y'])) {
                    $year = (int) $date->format('Y');
                    if ($year < 100) {
                        $year = $year <= 30 ? 2000 + $year : 1900 + $year;
                        $date = $date->setDate($year, (int) $date->format('m'), (int) $date->format('d'));
                    }
                }
                return $date;
            }
        }

        // Fallback mit strtotime
        $timestamp = strtotime($dateStr);
        if ($timestamp !== false) {
            return (new DateTimeImmutable())->setTimestamp($timestamp);
        }

        return null;
    }

    /**
     * Splits text into lines with maximum length (word boundary aware).
     * 
     * @param string $text Text zum Aufteilen
     * @param int $maxLength Maximum line length
     * @return string[]
     */
    protected static function splitText(string $text, int $maxLength = self::VERWENDUNGSZWECK_MAX_LENGTH): array {
        if ($text === '') {
            return [];
        }

        $lines = [];
        $words = explode(' ', $text);
        $currentLine = '';

        foreach ($words as $word) {
            if ($currentLine === '') {
                $currentLine = $word;
            } elseif (strlen($currentLine . ' ' . $word) <= $maxLength) {
                $currentLine .= ' ' . $word;
            } else {
                $lines[] = $currentLine;
                $currentLine = $word;
            }
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        return array_map(fn($line) => substr($line, 0, $maxLength), $lines);
    }

    /**
     * Teilt den Verwendungszweck in Zeilen mit max. 27 Zeichen.
     * 
     * @param string|null $purpose Verwendungszweck-Text
     * @return string[]
     */
    protected static function splitPurpose(?string $purpose): array {
        if ($purpose === null || $purpose === '') {
            return [];
        }

        // Normalisiere Whitespace
        $purpose = preg_replace('/\s+/', ' ', trim($purpose)) ?? $purpose;

        return self::splitText($purpose, self::VERWENDUNGSZWECK_MAX_LENGTH);
    }

    /**
     * Parst eine Account-ID in BLZ und Kontonummer.
     * 
     * Supported formats:
     * - BLZ/Kontonummer: "12345678/0123456789"
     * - IBAN: "DE89370400440532013000"
     * - Nur Kontonummer: "0123456789"
     * 
     * @param string $accountId Account-ID
     * @return array{blz: string, account: string}
     */
    protected static function parseAccountId(string $accountId): array {
        // Format: BLZ/Kontonummer oder BIC/IBAN
        if (str_contains($accountId, '/')) {
            $parts = explode('/', $accountId, 2);
            return [
                'blz' => $parts[0],
                'account' => $parts[1] ?? '',
            ];
        }

        // IBAN-Format via BankHelper validieren
        if (BankHelper::isIBAN($accountId)) {
            $ibanParts = BankHelper::splitIBAN($accountId);
            if ($ibanParts !== false) {
                return [
                    'blz' => $ibanParts['BLZ'],
                    'account' => $accountId,
                ];
            }
            return [
                'blz' => '',
                'account' => $accountId,
            ];
        }

        // Nur Kontonummer
        return [
            'blz' => '',
            'account' => $accountId,
        ];
    }

    /**
     * Extrahiert BLZ oder BIC aus einer IBAN.
     * 
     * Uses BankHelper for IBAN validation and BIC lookup.
     * 
     * @param string $iban IBAN
     * @return string BIC, BLZ oder leerer String
     */
    protected static function extractBlzFromIban(string $iban): string {
        if (!BankHelper::isIBAN($iban)) {
            return '';
        }

        // Versuche BIC aus Bundesbank-Datenbank
        $bic = BankHelper::bicFromIBAN($iban);
        if (!empty($bic)) {
            return $bic;
        }

        // Fallback: BLZ aus IBAN extrahieren
        $ibanParts = BankHelper::splitIBAN($iban);
        if ($ibanParts !== false) {
            return $ibanParts['BLZ'];
        }

        return '';
    }

    /**
     * Fills all purpose fields in a values array.
     * 
     * @param string[] $values Reference to field array
     * @param string[] $purposeLines Verwendungszweck-Zeilen
     */
    protected static function fillPurposeFields(array &$values, array $purposeLines): void {
        $felder = self::getPurposeFields();
        foreach ($felder as $i => $field) {
            $values[$field->index()] = $purposeLines[$i] ?? '';
        }
    }

    /**
     * Collects all purpose values from a field array.
     * 
     * @param array $fields Array der DataField-Objekte
     * @return string[] Nicht-leere Verwendungszweck-Werte
     */
    protected static function collectVerwendungszweck(array $fields): array {
        $parts = [];
        foreach (self::getPurposeFields() as $field) {
            $vz = self::getField($fields, $field);
            if (!empty($vz)) {
                $parts[] = $vz;
            }
        }
        return $parts;
    }
}
