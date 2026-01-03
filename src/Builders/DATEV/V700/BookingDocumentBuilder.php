<?php
/*
 * Created on   : Sat Dec 14 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : BookingDocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\DATEV\V700;

use CommonToolkit\Builders\CSVDocumentBuilder;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV\Document;
use CommonToolkit\Entities\CSV\DataLine;
use CommonToolkit\FinancialFormats\Entities\DATEV\MetaHeaderLine;
use CommonToolkit\FinancialFormats\Entities\DATEV\Documents\BookingBatch;
use CommonToolkit\FinancialFormats\Entities\DATEV\Header\V700\MetaHeaderDefinition;
use CommonToolkit\FinancialFormats\Entities\DATEV\Header\BookingBatchHeaderLine;
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\V700\{MetaHeaderField, BookingBatchHeaderField};
use RuntimeException;
use DateTimeImmutable;

/**
 * Builder for DATEV BookingBatch documents (V700).
 * Erstellt komplette DATEV-Export-Dateien mit MetaHeader, FieldHeader und Buchungsdaten.
 */
final class BookingDocumentBuilder extends CSVDocumentBuilder {

    private ?MetaHeaderLine $metaHeader = null;
    private ?BookingBatchHeaderLine $fieldHeader = null;
    /** @var DataLine[] */
    private array $bookingLines = [];

    public function __construct(string $delimiter = Document::DEFAULT_DELIMITER, string $enclosure = '"') {
        parent::__construct($delimiter, $enclosure);
    }

    /**
     * Setzt den MetaHeader mit Standard-BookingBatch-Konfiguration.
     */
    public function setMetaHeader(?MetaHeaderLine $metaHeader = null): self {
        $this->metaHeader = $metaHeader ?? $this->createDefaultMetaHeader();
        return $this;
    }

    /**
     * Setzt den FieldHeader (Spaltenbeschreibungen).
     */
    public function setFieldHeader(?BookingBatchHeaderLine $fieldHeader = null): self {
        $this->fieldHeader = $fieldHeader ?? BookingBatchHeaderLine::createV700();
        return $this;
    }

    /**
     * Adds a booking line.
     */
    public function addBooking(DataLine $booking): self {
        $this->bookingLines[] = $booking;
        return $this;
    }

    /**
     * Convenience method for adding a simple booking.
     * Erstellt eine DataLine mit den wichtigsten Buchungsfeldern.
     */
    public function addSimpleBooking(
        float $amount,
        string $sollHaben,
        string $account,
        string $contraAccount,
        DateTimeImmutable|string $date,
        string $documentRef,
        string $text
    ): self {
        if (!$this->fieldHeader) {
            $this->setFieldHeader();
        }

        // Datum formatieren
        $dateStr = $date instanceof DateTimeImmutable
            ? $date->format('dm')
            : (new DateTimeImmutable($date))->format('dm');

        // Leeres Array mit allen Feldern initialisieren
        $fieldCount = $this->fieldHeader->countFields();
        $values = array_fill(0, $fieldCount, '');

        // Wichtige Felder setzen
        $values[$this->fieldHeader->getFieldIndex(BookingBatchHeaderField::Umsatz)] = number_format(abs($amount), 2, ',', '');
        $values[$this->fieldHeader->getFieldIndex(BookingBatchHeaderField::SollHabenKennzeichen)] = $sollHaben;
        $values[$this->fieldHeader->getFieldIndex(BookingBatchHeaderField::Konto)] = $account;
        $values[$this->fieldHeader->getFieldIndex(BookingBatchHeaderField::Gegenkonto)] = $contraAccount;
        $values[$this->fieldHeader->getFieldIndex(BookingBatchHeaderField::Belegdatum)] = $dateStr;
        $values[$this->fieldHeader->getFieldIndex(BookingBatchHeaderField::Belegfeld1)] = $documentRef;
        $values[$this->fieldHeader->getFieldIndex(BookingBatchHeaderField::Buchungstext)] = $text;

        $booking = new DataLine($values, $this->delimiter, $this->enclosure);
        return $this->addBooking($booking);
    }

    /**
     * Setzt Berater- und Mandantennummer im MetaHeader.
     */
    public function setClient(int $advisorNumber, int $clientNumber): self {
        if (!$this->metaHeader) {
            $this->setMetaHeader();
        }

        $this->metaHeader->set(MetaHeaderField::Beraternummer, $advisorNumber);
        $this->metaHeader->set(MetaHeaderField::Mandantennummer, $clientNumber);

        return $this;
    }

    /**
     * Setzt den Zeitraum der Buchungen im MetaHeader.
     */
    public function setDateRange(DateTimeImmutable $from, DateTimeImmutable $to): self {
        if (!$this->metaHeader) {
            $this->setMetaHeader();
        }

        $this->metaHeader->set(MetaHeaderField::DatumVon, $from->format('Ymd'));
        $this->metaHeader->set(MetaHeaderField::DatumBis, $to->format('Ymd'));

        return $this;
    }

    /**
     * Setzt die Beschreibung des BookingBatchs.
     */
    public function setDescription(string $description): self {
        if (!$this->metaHeader) {
            $this->setMetaHeader();
        }

        $this->metaHeader->set(MetaHeaderField::Bezeichnung, $description);

        return $this;
    }

    /**
     * Erstellt das komplette DATEV-Dokument.
     */
    public function build(): BookingBatch {
        if (!$this->metaHeader) {
            $this->setMetaHeader();
        }

        if (!$this->fieldHeader) {
            $this->setFieldHeader();
        }

        if (empty($this->bookingLines)) {
            static::logWarning('BookingBatch ohne Buchungszeilen erstellt');
        }

        // Validierung
        $this->validate();

        return new BookingBatch(
            $this->metaHeader,
            $this->fieldHeader,
            $this->bookingLines
        );
    }

    /**
     * Validiert den Builder-Zustand vor dem Build.
     */
    private function validate(): void {
        if (!$this->metaHeader) {
            throw new RuntimeException('MetaHeader muss gesetzt sein');
        }

        if (!$this->fieldHeader) {
            throw new RuntimeException('FieldHeader muss gesetzt sein');
        }

        // Prüfe Feldanzahl der Buchungszeilen
        $expectedFieldCount = $this->fieldHeader->countFields();
        foreach ($this->bookingLines as $index => $booking) {
            $actualFieldCount = count($booking->getFields());
            if ($actualFieldCount !== $expectedFieldCount) {
                throw new RuntimeException(
                    "Buchungszeile $index hat $actualFieldCount Felder, erwartet: $expectedFieldCount"
                );
            }
        }
    }

    /**
     * Creates a standard MetaHeader for BookingBatch.
     */
    private function createDefaultMetaHeader(): MetaHeaderLine {
        $definition = new MetaHeaderDefinition();
        $metaHeader = new MetaHeaderLine($definition);

        // Setze aktuelle Zeit als Erzeugungszeitpunkt
        $metaHeader->set(
            MetaHeaderField::ErzeugtAm,
            (new DateTimeImmutable())->format('YmdHis') . '000'
        );

        return $metaHeader;
    }

    /**
     * Returns statistics about the current builder state.
     */
    public function getStats(): array {
        return [
            'metaHeader_set' => $this->metaHeader !== null,
            'fieldHeader_set' => $this->fieldHeader !== null,
            'booking_count' => count($this->bookingLines),
            'field_count' => $this->fieldHeader?->countFields() ?? 0,
        ];
    }
}
