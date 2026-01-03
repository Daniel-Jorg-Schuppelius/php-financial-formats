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
        return $this->getReceiptFieldHandling($rowIndex, RecurringBookingsHeaderField::B1->getPosition());
    }

    /**
     * Setzt die Belegfeld1-Behandlung einer Buchungszeile.
     */
    public function setReceiptFieldHandlingValue(int $rowIndex, ReceiptFieldHandling $handling): void {
        $this->setReceiptFieldHandling($rowIndex, RecurringBookingsHeaderField::B1->getPosition(), $handling);
    }

    // ==================== UMSATZ (FELD 2-5) ====================

    /**
     * Returns the turnover amount of a booking line.
     */
    public function getAmount(int $rowIndex): ?string {
        return $this->getFieldValue($rowIndex, RecurringBookingsHeaderField::Umsatz->getPosition());
    }

    /**
     * Returns debit/credit of a booking line.
     */
    public function getCreditDebitValue(int $rowIndex): ?CreditDebit {
        return $this->getCreditDebit($rowIndex, RecurringBookingsHeaderField::SollHabenKennzeichen->getPosition());
    }

    /**
     * Setzt Soll/Haben einer Buchungszeile.
     */
    public function setCreditDebitValue(int $rowIndex, CreditDebit $creditDebit): void {
        $this->setCreditDebit($rowIndex, RecurringBookingsHeaderField::SollHabenKennzeichen->getPosition(), $creditDebit);
    }

    /**
     * Returns the currency code of a booking line.
     */
    public function getCurrencyCodeValue(int $rowIndex): ?CurrencyCode {
        return $this->getCurrencyCode($rowIndex, RecurringBookingsHeaderField::WKZUmsatz->getPosition());
    }

    /**
     * Sets the currency code of a booking line.
     */
    public function setCurrencyCodeValue(int $rowIndex, CurrencyCode $currencyCode): void {
        $this->setCurrencyCode($rowIndex, RecurringBookingsHeaderField::WKZUmsatz->getPosition(), $currencyCode);
    }

    // ==================== POSTENSPERRE (FELD 21) ====================

    /**
     * Returns the item lock of a booking line.
     */
    public function getItemLockValue(int $rowIndex): ?ItemLock {
        return $this->getItemLock($rowIndex, RecurringBookingsHeaderField::Postensperre->getPosition());
    }

    /**
     * Setzt die Postensperre einer Buchungszeile.
     */
    public function setItemLockValue(int $rowIndex, ItemLock $itemLock): void {
        $this->setItemLock($rowIndex, RecurringBookingsHeaderField::Postensperre->getPosition(), $itemLock);
    }

    // ==================== SACHVERHALT (FELD 24) ====================

    /**
     * Returns the case type of a booking line (dunning interest/dunning fee).
     */
    public function getDunningSubjectValue(int $rowIndex): ?DunningSubject {
        return $this->getDunningSubject($rowIndex, RecurringBookingsHeaderField::Sachverhalt->getPosition());
    }

    /**
     * Setzt den Sachverhalt einer Buchungszeile.
     */
    public function setDunningSubjectValue(int $rowIndex, DunningSubject $subject): void {
        $this->setDunningSubject($rowIndex, RecurringBookingsHeaderField::Sachverhalt->getPosition(), $subject);
    }

    // ==================== ZINSSPERRE (FELD 25) ====================

    /**
     * Returns the interest lock of a booking line.
     */
    public function getInterestLockValue(int $rowIndex): ?InterestLock {
        return $this->getInterestLock($rowIndex, RecurringBookingsHeaderField::Zinssperre->getPosition());
    }

    /**
     * Setzt die Zinssperre einer Buchungszeile.
     */
    public function setInterestLockValue(int $rowIndex, InterestLock $interestLock): void {
        $this->setInterestLock($rowIndex, RecurringBookingsHeaderField::Zinssperre->getPosition(), $interestLock);
    }

    // ==================== ZEITINTERVALL (FELDER 81-82) ====================

    /**
     * Returns the time interval type of a booking line (DAY/MON).
     */
    public function getTimeIntervalTypeValue(int $rowIndex): ?TimeIntervalType {
        return $this->getTimeIntervalType($rowIndex, RecurringBookingsHeaderField::Zeitintervallart->getPosition());
    }

    /**
     * Setzt die Zeitintervallart einer Buchungszeile.
     */
    public function setTimeIntervalTypeValue(int $rowIndex, TimeIntervalType $intervalType): void {
        $this->setTimeIntervalType($rowIndex, RecurringBookingsHeaderField::Zeitintervallart->getPosition(), $intervalType);
    }

    /**
     * Returns the time interval (every n days/months).
     */
    public function getTimeInterval(int $rowIndex): ?int {
        $value = $this->getFieldValue($rowIndex, RecurringBookingsHeaderField::Zeitabstand->getPosition());
        if ($value === null) return null;

        $cleanValue = trim($value, '"');
        return $cleanValue !== '' && is_numeric($cleanValue) ? (int)$cleanValue : null;
    }

    /**
     * Setzt das Zeitintervall.
     */
    public function setTimeInterval(int $rowIndex, int $interval): void {
        $this->setFieldValue($rowIndex, RecurringBookingsHeaderField::Zeitabstand->getPosition(), (string)$interval);
    }

    // ==================== WOCHENTAG (FELD 83) ====================

    /**
     * Returns the weekday bitmask of a booking line.
     */
    public function getWeekdayMaskValue(int $rowIndex): ?int {
        return $this->getWeekdayMask($rowIndex, RecurringBookingsHeaderField::Wochentag->getPosition());
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
        $this->setWeekdayMask($rowIndex, RecurringBookingsHeaderField::Wochentag->getPosition(), $mask);
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
        $value = $this->getFieldValue($rowIndex, RecurringBookingsHeaderField::OrdnungszahlTagImMonat->getPosition());
        if ($value === null) return null;

        $cleanValue = trim($value, '"');
        return $cleanValue !== '' && is_numeric($cleanValue) ? (int)$cleanValue : null;
    }

    /**
     * Setzt die Ordnungszahl Tag im Monat (1-31).
     */
    public function setDayOfMonth(int $rowIndex, int $day): void {
        $this->setFieldValue($rowIndex, RecurringBookingsHeaderField::OrdnungszahlTagImMonat->getPosition(), (string)$day);
    }

    // ==================== ORDNUNGSZAHL WOCHENTAG (FELD 86) ====================

    /**
     * Returns the weekday ordinal (1=first, 5=last).
     */
    public function getWeekdayOrdinalValue(int $rowIndex): ?WeekdayOrdinal {
        return $this->getWeekdayOrdinal($rowIndex, RecurringBookingsHeaderField::OrdnungszahlWochentag->getPosition());
    }

    /**
     * Setzt die Ordnungszahl des Wochentags.
     */
    public function setWeekdayOrdinalValue(int $rowIndex, WeekdayOrdinal $ordinal): void {
        $this->setWeekdayOrdinal($rowIndex, RecurringBookingsHeaderField::OrdnungszahlWochentag->getPosition(), $ordinal);
    }

    // ==================== ENDETYP UND ENDDATUM (FELDER 80, 87) ====================

    /**
     * Returns the end date of the booking series (field 80).
     * Muss nach dem Beginndatum liegen. Format: TTMMJJJJ
     */
    public function getEndDate(int $rowIndex): ?string {
        return $this->getFieldValue($rowIndex, RecurringBookingsHeaderField::Enddatum->getPosition());
    }

    /**
     * Setzt das Enddatum der Buchungsserie (Feld 80).
     */
    public function setEndDate(int $rowIndex, string $endDate): void {
        $this->setFieldValue($rowIndex, RecurringBookingsHeaderField::Enddatum->getPosition(), $endDate);
    }

    /**
     * Returns the end type of a booking line (field 87).
     * 1 = kein Enddatum, 2 = Endzeitpunkt bei Anzahl Ereignissen, 3 = Endet am
     */
    public function getEndTypeValue(int $rowIndex): ?EndType {
        return $this->getEndType($rowIndex, RecurringBookingsHeaderField::Endetyp->getPosition());
    }

    /**
     * Setzt den Endetyp einer Buchungszeile.
     */
    public function setEndTypeValue(int $rowIndex, EndType $endType): void {
        $this->setEndType($rowIndex, RecurringBookingsHeaderField::Endetyp->getPosition(), $endType);
    }

    // ==================== GESELLSCHAFTER (FELDER 88-89) ====================

    /**
     * Returns the shareholder name (field 88).
     * Must match the assigned shareholder in the central master data.
     */
    public function getPartnerName(int $rowIndex): ?string {
        $value = $this->getFieldValue($rowIndex, RecurringBookingsHeaderField::Gesellschaftername->getPosition());
        if ($value === null) return null;

        return trim($value, '"');
    }

    /**
     * Setzt den Gesellschafternamen (Feld 88).
     */
    public function setPartnerName(int $rowIndex, string $name): void {
        $this->setFieldValue($rowIndex, RecurringBookingsHeaderField::Gesellschaftername->getPosition(), '"' . $name . '"');
    }

    /**
     * Returns the participant number (field 89).
     * Required field for assigning shareholders to the booking entry.
     * The participant number must correspond to the official number from the assessment declaration.
     */
    public function getPartnerNumber(int $rowIndex): ?int {
        $value = $this->getFieldValue($rowIndex, RecurringBookingsHeaderField::Beteiligtennummer->getPosition());
        if ($value === null) return null;

        $cleanValue = trim($value, '"');
        return $cleanValue !== '' && is_numeric($cleanValue) ? (int)$cleanValue : null;
    }

    /**
     * Setzt die Beteiligtennummer (Feld 89).
     */
    public function setPartnerNumber(int $rowIndex, int $number): void {
        $this->setFieldValue($rowIndex, RecurringBookingsHeaderField::Beteiligtennummer->getPosition(), (string)$number);
    }

    // ==================== SOBIL / GENERALUMKEHR (FELDER 96-97) ====================

    /**
     * Returns the special balance booking value.
     */
    public function getSoBilValue(int $rowIndex): ?ItemLock {
        return $this->getItemLock($rowIndex, RecurringBookingsHeaderField::KennzeichenSoBilBuchung->getPosition());
    }

    /**
     * Setzt den SoBil-Buchung-Wert.
     */
    public function setSoBilValue(int $rowIndex, ItemLock $soBil): void {
        $this->setItemLock($rowIndex, RecurringBookingsHeaderField::KennzeichenSoBilBuchung->getPosition(), $soBil);
    }

    /**
     * Returns the general reversal value.
     */
    public function getGeneralReversalValue(int $rowIndex): ?string {
        return $this->getFieldValue($rowIndex, RecurringBookingsHeaderField::Generalumkehr->getPosition());
    }

    /**
     * Setzt den Generalumkehr-Wert (0, 1, oder G).
     */
    public function setGeneralReversalValue(int $rowIndex, string $value): void {
        $this->setFieldValue($rowIndex, RecurringBookingsHeaderField::Generalumkehr->getPosition(), $value);
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
