<?php
/*
 * Created on   : Sun Dec 16 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : RecurringBookings.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\DATEV\Documents;

use CommonToolkit\Entities\CSV\ColumnWidthConfig;
use CommonToolkit\Entities\CSV\HeaderLine;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV\Document;
use CommonToolkit\FinancialFormats\Entities\DATEV\MetaHeaderLine;
use CommonToolkit\Enums\{CreditDebit, CurrencyCode, Weekday};
use CommonToolkit\Enums\Common\CSV\TruncationStrategy;
use CommonToolkit\FinancialFormats\Enums\DATEV\{
    DunningSubject,
    EndType,
    InterestLock,
    ItemLock,
    ReceiptFieldHandling,
    TimeIntervalType,
    WeekdayOrdinal
};
use CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields\Format\Category;
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\V700\RecurringBookingsHeaderField;

/**
 * DATEV-Wiederkehrende Buchungen-Dokument.
 * Special document class for recurring bookings format (Category 65).
 * 
 * Die Spaltenbreiten werden automatisch basierend auf den DATEV-Spezifikationen
 * aus RecurringBookingsHeaderField::getMaxLength() angewendet.
 * 
 * @see https://developer.datev.de/de/file-format/details/datev-format/format-description/recurring-bookings
 */
final class RecurringBookings extends Document {
    public function __construct(?MetaHeaderLine $metaHeader, ?HeaderLine $header, array $rows = []) {
        parent::__construct($metaHeader, $header, $rows);
    }

    /**
     * Erstellt eine ColumnWidthConfig basierend auf den DATEV-Spezifikationen.
     * Maximum field lengths are derived from RecurringBookingsHeaderField::getMaxLength().
     * 
     * @param TruncationStrategy $strategy Truncation strategy (Default: TRUNCATE for DATEV conformity)
     * @return ColumnWidthConfig
     */
    public static function createDatevColumnWidthConfig(TruncationStrategy $strategy = TruncationStrategy::TRUNCATE): ColumnWidthConfig {
        $config = new ColumnWidthConfig(null, $strategy);

        foreach (RecurringBookingsHeaderField::ordered() as $index => $field) {
            $maxLength = $field->getMaxLength();
            if ($maxLength !== null) {
                $config->setColumnWidth($index, $maxLength);
            }
        }

        return $config;
    }

    /**
     * Returns the DATEV category for this document type.
     */
    public function getCategory(): Category {
        return Category::WiederkehrendeBuchungen;
    }

    /**
     * Returns the DATEV format type.
     */
    public function getFormatType(): string {
        return Category::WiederkehrendeBuchungen->nameValue();
    }

    /**
     * Validiert Wiederkehrende Buchungen-spezifische Regeln.
     */
    public function validate(): void {
        parent::validate();

        $metaFields = $this->getMetaHeader()?->getFields() ?? [];
        if (count($metaFields) > 2 && (int)$metaFields[2]->getValue() !== 65) {
            throw new \RuntimeException('Ungültige Kategorie für Wiederkehrende Buchungen-Dokument. Erwartet: 65');
        }
    }

    // ==================== BELEGFELD-BEHANDLUNG (FELD 1) ====================

    /**
     * Returns the receipt field 1 handling of a booking line.
     */
    public function getReceiptFieldHandlingValue(int $rowIndex): ?ReceiptFieldHandling {
        $value = $this->getField($rowIndex, RecurringBookingsHeaderField::B1);
        return $value !== null && $value !== '' ? ReceiptFieldHandling::tryFromString($value) : null;
    }

    /**
     * Setzt die Belegfeld1-Behandlung einer Buchungszeile.
     */
    public function setReceiptFieldHandlingValue(int $rowIndex, ReceiptFieldHandling $handling): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::B1, $handling->value);
    }

    // ==================== UMSATZ (FELD 2-5) ====================

    /**
     * Returns the turnover amount of a booking line.
     */
    public function getAmount(int $rowIndex): ?string {
        return $this->getField($rowIndex, RecurringBookingsHeaderField::Umsatz);
    }

    /**
     * Setzt den Umsatzbetrag einer Buchungszeile.
     */
    public function setAmount(int $rowIndex, string $amount): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::Umsatz, $amount);
    }

    /**
     * Returns debit/credit of a booking line.
     */
    public function getCreditDebitValue(int $rowIndex): ?CreditDebit {
        $value = $this->getField($rowIndex, RecurringBookingsHeaderField::SollHabenKennzeichen);
        return $value !== null && $value !== '' ? CreditDebit::fromDatevCode($value) : null;
    }

    /**
     * Setzt Soll/Haben einer Buchungszeile.
     */
    public function setCreditDebitValue(int $rowIndex, CreditDebit $creditDebit): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::SollHabenKennzeichen, $creditDebit->toDatevCode());
    }

    /**
     * Returns the currency code of a booking line.
     */
    public function getCurrencyCodeValue(int $rowIndex): ?CurrencyCode {
        $value = $this->getField($rowIndex, RecurringBookingsHeaderField::WKZUmsatz);
        return $value !== null && $value !== '' ? CurrencyCode::fromCode($value) : null;
    }

    /**
     * Sets the currency code of a booking line.
     */
    public function setCurrencyCodeValue(int $rowIndex, CurrencyCode $currencyCode): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::WKZUmsatz, $currencyCode->value);
    }

    // ==================== POSTENSPERRE (FELD 21) ====================

    /**
     * Returns the item lock of a booking line.
     */
    public function getItemLockValue(int $rowIndex): ?ItemLock {
        $value = $this->getField($rowIndex, RecurringBookingsHeaderField::Postensperre);
        return $value !== '' && is_numeric($value) ? ItemLock::fromInt((int)$value) : null;
    }

    /**
     * Setzt die Postensperre einer Buchungszeile.
     */
    public function setItemLockValue(int $rowIndex, ItemLock $itemLock): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::Postensperre, (string)$itemLock->value);
    }

    // ==================== SACHVERHALT (FELD 24) ====================

    /**
     * Returns the case type of a booking line (dunning interest/dunning fee).
     */
    public function getDunningSubjectValue(int $rowIndex): ?DunningSubject {
        $value = $this->getField($rowIndex, RecurringBookingsHeaderField::Sachverhalt);
        return $value !== null && $value !== '' ? DunningSubject::tryFromString($value) : null;
    }

    /**
     * Setzt den Sachverhalt einer Buchungszeile.
     */
    public function setDunningSubjectValue(int $rowIndex, DunningSubject $subject): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::Sachverhalt, $subject->value);
    }

    // ==================== ZINSSPERRE (FELD 25) ====================

    /**
     * Returns the interest lock of a booking line.
     */
    public function getInterestLockValue(int $rowIndex): ?InterestLock {
        $value = $this->getField($rowIndex, RecurringBookingsHeaderField::Zinssperre);
        return $value !== '' && is_numeric($value) ? InterestLock::fromInt((int)$value) : null;
    }

    /**
     * Setzt die Zinssperre einer Buchungszeile.
     */
    public function setInterestLockValue(int $rowIndex, InterestLock $interestLock): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::Zinssperre, (string)$interestLock->value);
    }

    // ==================== ZEITINTERVALL (FELDER 81-82) ====================

    /**
     * Returns the time interval type of a booking line (DAY/MON).
     */
    public function getTimeIntervalTypeValue(int $rowIndex): ?TimeIntervalType {
        $value = $this->getField($rowIndex, RecurringBookingsHeaderField::Zeitintervallart);
        return $value !== null && $value !== '' ? TimeIntervalType::tryFromString($value) : null;
    }

    /**
     * Setzt die Zeitintervallart einer Buchungszeile.
     */
    public function setTimeIntervalTypeValue(int $rowIndex, TimeIntervalType $intervalType): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::Zeitintervallart, $intervalType->value);
    }

    /**
     * Returns the time interval (every n days/months).
     */
    public function getTimeInterval(int $rowIndex): ?int {
        $value = $this->getField($rowIndex, RecurringBookingsHeaderField::Zeitabstand);
        return $value !== '' && is_numeric($value) ? (int)$value : null;
    }

    /**
     * Setzt das Zeitintervall.
     */
    public function setTimeInterval(int $rowIndex, int $interval): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::Zeitabstand, (string)$interval);
    }

    // ==================== WOCHENTAG (FELD 83) ====================

    /**
     * Returns the weekday bitmask of a booking line.
     */
    public function getWeekdayMaskValue(int $rowIndex): ?int {
        $value = $this->getField($rowIndex, RecurringBookingsHeaderField::Wochentag);
        return $value !== '' && is_numeric($value) ? (int)$value : null;
    }

    /**
     * Returns the weekdays as array of Weekday enums.
     *
     * @return array<Weekday>
     */
    public function getWeekdays(int $rowIndex): array {
        $mask = $this->getWeekdayMaskValue($rowIndex);
        return $mask !== null ? Weekday::fromMask($mask) : [];
    }

    /**
     * Setzt die Wochentag-Bitmaske einer Buchungszeile.
     */
    public function setWeekdayMaskValue(int $rowIndex, int $mask): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::Wochentag, (string)$mask);
    }

    /**
     * Setzt die Wochentage aus Weekday-Enums.
     *
     * @param Weekday ...$weekdays
     */
    public function setWeekdays(int $rowIndex, Weekday ...$weekdays): void {
        $mask = Weekday::createMask(...$weekdays);
        $this->setWeekdayMaskValue($rowIndex, $mask);
    }

    // ==================== ORDNUNGSZAHL TAG IM MONAT (FELD 85) ====================

    /**
     * Returns the day ordinal in month (1-31).
     * For time interval type MON: day of month for the booking.
     */
    public function getDayOfMonth(int $rowIndex): ?int {
        $value = $this->getField($rowIndex, RecurringBookingsHeaderField::OrdnungszahlTagImMonat);
        return $value !== '' && is_numeric($value) ? (int)$value : null;
    }

    /**
     * Setzt die Ordnungszahl Tag im Monat (1-31).
     */
    public function setDayOfMonth(int $rowIndex, int $day): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::OrdnungszahlTagImMonat, (string)$day);
    }

    // ==================== ORDNUNGSZAHL WOCHENTAG (FELD 86) ====================

    /**
     * Returns the weekday ordinal (1=first, 5=last).
     */
    public function getWeekdayOrdinalValue(int $rowIndex): ?WeekdayOrdinal {
        $value = $this->getField($rowIndex, RecurringBookingsHeaderField::OrdnungszahlWochentag);
        return $value !== '' && is_numeric($value) ? WeekdayOrdinal::tryFrom((int)$value) : null;
    }

    /**
     * Setzt die Ordnungszahl des Wochentags.
     */
    public function setWeekdayOrdinalValue(int $rowIndex, WeekdayOrdinal $ordinal): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::OrdnungszahlWochentag, (string)$ordinal->value);
    }

    // ==================== ENDETYP UND ENDDATUM (FELDER 80, 87) ====================

    /**
     * Returns the end date of the booking series (field 80).
     * Muss nach dem Beginndatum liegen. Format: TTMMJJJJ
     */
    public function getEndDate(int $rowIndex): ?string {
        return $this->getField($rowIndex, RecurringBookingsHeaderField::Enddatum);
    }

    /**
     * Setzt das Enddatum der Buchungsserie (Feld 80).
     */
    public function setEndDate(int $rowIndex, string $endDate): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::Enddatum, $endDate);
    }

    /**
     * Returns the end type of a booking line (field 87).
     * 1 = kein Enddatum, 2 = Endzeitpunkt bei Anzahl Ereignissen, 3 = Endet am
     */
    public function getEndTypeValue(int $rowIndex): ?EndType {
        $value = $this->getField($rowIndex, RecurringBookingsHeaderField::Endetyp);
        return $value !== '' && is_numeric($value) ? EndType::tryFrom((int)$value) : null;
    }

    /**
     * Setzt den Endetyp einer Buchungszeile.
     */
    public function setEndTypeValue(int $rowIndex, EndType $endType): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::Endetyp, (string)$endType->value);
    }

    // ==================== GESELLSCHAFTER (FELDER 88-89) ====================

    /**
     * Returns the shareholder name (field 88).
     * Must match the assigned shareholder in the central master data.
     */
    public function getPartnerName(int $rowIndex): ?string {
        $value = $this->getField($rowIndex, RecurringBookingsHeaderField::Gesellschaftername);
        return $value !== null && $value !== '' ? $value : null;
    }

    /**
     * Setzt den Gesellschafternamen (Feld 88).
     */
    public function setPartnerName(int $rowIndex, string $name): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::Gesellschaftername, $name);
    }

    /**
     * Returns the participant number (field 89).
     * Required field for assigning shareholders to the booking entry.
     * The participant number must correspond to the official number from the assessment declaration.
     */
    public function getPartnerNumber(int $rowIndex): ?int {
        $value = $this->getField($rowIndex, RecurringBookingsHeaderField::Beteiligtennummer);
        return $value !== '' && is_numeric($value) ? (int)$value : null;
    }

    /**
     * Setzt die Beteiligtennummer (Feld 89).
     */
    public function setPartnerNumber(int $rowIndex, int $number): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::Beteiligtennummer, (string)$number);
    }

    // ==================== SOBIL / GENERALUMKEHR (FELDER 96-97) ====================

    /**
     * Returns the special balance booking value.
     */
    public function getSoBilValue(int $rowIndex): ?ItemLock {
        $value = $this->getField($rowIndex, RecurringBookingsHeaderField::KennzeichenSoBilBuchung);
        return $value !== '' && is_numeric($value) ? ItemLock::fromInt((int)$value) : null;
    }

    /**
     * Setzt den SoBil-Buchung-Wert.
     */
    public function setSoBilValue(int $rowIndex, ItemLock $soBil): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::KennzeichenSoBilBuchung, (string)$soBil->value);
    }

    /**
     * Returns the general reversal value.
     */
    public function getGeneralReversalValue(int $rowIndex): ?string {
        return $this->getField($rowIndex, RecurringBookingsHeaderField::Generalumkehr);
    }

    /**
     * Setzt den Generalumkehr-Wert (0, 1, oder G).
     */
    public function setGeneralReversalValue(int $rowIndex, string $value): void {
        $this->setField($rowIndex, RecurringBookingsHeaderField::Generalumkehr, $value);
    }

    // ==================== HILFSMETHODEN ====================

    /**
     * Checks if a booking line is configured as daily interval.
     */
    public function isDailyInterval(int $rowIndex): bool {
        $intervalType = $this->getTimeIntervalTypeValue($rowIndex);
        return $intervalType?->isDaily() ?? false;
    }

    /**
     * Checks if a booking line is configured as monthly interval.
     */
    public function isMonthlyInterval(int $rowIndex): bool {
        $intervalType = $this->getTimeIntervalTypeValue($rowIndex);
        return $intervalType?->isMonthly() ?? false;
    }

    /**
     * Checks if a booking line runs indefinitely.
     */
    public function isUnlimitedRepetition(int $rowIndex): bool {
        $endType = $this->getEndTypeValue($rowIndex);
        return $endType?->isUnlimited() ?? false;
    }

    /**
     * Returns a readable description of the repetition interval.
     */
    public function getIntervalDescription(int $rowIndex): string {
        $intervalType = $this->getTimeIntervalTypeValue($rowIndex);
        $interval = $this->getTimeInterval($rowIndex);

        if ($intervalType === null || $interval === null) {
            return 'Kein Intervall definiert';
        }

        $unit = $intervalType->isDaily() ? 'Tag(e)' : 'Monat(e)';
        return "Alle $interval $unit";
    }

    /**
     * Returns a readable description of the end type.
     */
    public function getEndDescription(int $rowIndex): string {
        $endType = $this->getEndTypeValue($rowIndex);

        if ($endType === null) {
            return 'Kein Endetyp definiert';
        }

        return match ($endType) {
            EndType::NO_END => 'Unbegrenzt',
            EndType::BY_COUNT => 'Nach Anzahl Ereignissen',
            EndType::BY_DATE => sprintf('Am %s', $this->getEndDate($rowIndex) ?? 'unbekannt'),
        };
    }
}