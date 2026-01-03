<?php
/*
 * Created on   : Tue Dec 23 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : BankTransaction.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\DATEV\Documents;

use CommonToolkit\Entities\CSV\{HeaderField, DataLine, Document as CSVDocument, ColumnWidthConfig};
use CommonToolkit\FinancialFormats\Entities\DATEV\Header\ASCII\BankTransactionHeaderLine;
use CommonToolkit\FinancialFormats\Entities\DATEV\Header\ASCII\BankTransactionHeaderDefinition;
use CommonToolkit\Enums\Common\CSV\TruncationStrategy;
use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\ASCII\BankTransactionHeaderField;
use RuntimeException;

/**
 * ASCII processing document for DATEV bank transactions.
 * 
 * Standalone document format WITHOUT MetaHeader and DATEV categories.
 * Verwendet das einfache CSV-Document als Basis, nicht das DATEV-Document.
 * These ASCII files are independent from the standard DATEV V700 system.
 * 
 * Die Spaltenbreiten werden automatisch basierend auf den DATEV-Spezifikationen
 * aus BankTransactionHeaderField::getMaxLength() angewendet.
 * 
 * @package CommonToolkit\Entities\DATEV\Documents
 */
final class BankTransaction extends CSVDocument {

    public function __construct(array $rows = [], string $delimiter = ';', string $enclosure = '"', ?ColumnWidthConfig $columnWidthConfig = null) {
        // ASCII-Weiterverarbeitungsdateien haben keinen Header, aber wir erstellen einen internen aus der Definition
        $internalHeader = $this->createInternalHeader($delimiter, $enclosure);

        // Falls keine ColumnWidthConfig übergeben wurde, erstelle eine basierend auf DATEV-Spezifikation
        $columnWidthConfig ??= self::createDatevColumnWidthConfig();

        parent::__construct($internalHeader, $rows, $delimiter, $enclosure, $columnWidthConfig);

        // ASCII-Weiterverarbeitungsdateien werden OHNE Header exportiert
        $this->exportWithHeader = false;
    }

    /**
     * Erstellt eine ColumnWidthConfig basierend auf den DATEV-Spezifikationen.
     * Maximum field lengths are derived from BankTransactionHeaderField::getMaxLength().
     * 
     * @param TruncationStrategy $strategy Truncation strategy (Default: TRUNCATE for DATEV conformity)
     * @return ColumnWidthConfig
     */
    public static function createDatevColumnWidthConfig(TruncationStrategy $strategy = TruncationStrategy::TRUNCATE): ColumnWidthConfig {
        $config = new ColumnWidthConfig(null, $strategy);

        foreach (BankTransactionHeaderField::ordered() as $index => $field) {
            $maxLength = $field->getMaxLength();
            if ($maxLength !== null) {
                // Verwende Index-basierte Spaltenbreiten (0-basiert)
                $config->setColumnWidth($index, $maxLength);
            }
        }

        return $config;
    }

    /**
     * Creates an internal header from the HeaderDefinition for internal purposes.
     */
    private function createInternalHeader(string $delimiter, string $enclosure): BankTransactionHeaderLine {
        $headerFields = [];

        // Nutze die geordneten Felder aus dem BankTransactionHeaderField Enum
        foreach (BankTransactionHeaderField::ordered() as $field) {
            $headerFields[] = new HeaderField($field->value, $enclosure);
        }

        return new BankTransactionHeaderLine($headerFields, $delimiter, $enclosure);
    }

    /**
     * Overrides getHeader() to return the correct BankTransactionHeaderLine type.
     */
    public function getHeader(): ?BankTransactionHeaderLine {
        return $this->header instanceof BankTransactionHeaderLine ? $this->header : null;
    }

    /**
     * Returns the format type.
     */
    public function getFormatType(): string {
        return 'ASCII-Weiterverarbeitungsdatei';
    }

    /**
     * Retrieves the trimmed value of a specific field from a data row.
     * 
     * Ermöglicht typsicheren Zugriff auf Feldwerte über das BankTransactionHeaderField Enum
     * anstatt über numerische Indizes.
     * 
     * Beispiel:
     * ```php
     * $blz = $document->getField($rowIndex, BankTransactionHeaderField::BLZ_BIC_KONTOINHABER);
     * $iban = $document->getField($rowIndex, BankTransactionHeaderField::KONTONUMMER_IBAN_KONTOINHABER);
     * ```
     * 
     * @param int $rowIndex Der Zeilenindex (0-basiert)
     * @param BankTransactionHeaderField $field Das gewünschte Feld
     * @return string|null Der getrimmte Feldwert oder null wenn Zeile/Feld nicht existiert
     */
    public function getField(int $rowIndex, BankTransactionHeaderField $field): ?string {
        $row = $this->getRow($rowIndex);
        if ($row === null) {
            return null;
        }

        return $this->getHeader()?->getFieldValue($row, $field);
    }

    /**
     * Checks if a specific field exists and has a non-empty value.
     * 
     * @param int $rowIndex Der Zeilenindex (0-basiert)
     * @param BankTransactionHeaderField $field Das zu prüfende Feld
     * @return bool True wenn das Feld existiert und einen nicht-leeren Wert hat
     */
    public function hasField(int $rowIndex, BankTransactionHeaderField $field): bool {
        $value = $this->getField($rowIndex, $field);
        return $value !== null && $value !== '';
    }

    /**
     * Validates the ASCII BankTransaction format.
     * 
     * @throws RuntimeException On validation errors
     */
    public function validate(): void {
        if (empty($this->rows)) {
            throw new RuntimeException('Empty ASCII processing file');
        }

        // Validate field count (7-34 fields required)
        $firstRow = $this->rows[0];
        $fieldCount = $firstRow->countFields();
        if ($fieldCount < 7 || $fieldCount > 34) {
            throw new RuntimeException('Invalid field count (expected: 7-34 fields)');
        }

        // Validate required fields using header definition
        $values = array_map(fn($field) => $field->getValue(), $firstRow->getFields());
        $headerDef = new BankTransactionHeaderDefinition();
        if (!$headerDef->matches($values)) {
            throw new RuntimeException('ASCII processing file validation failed');
        }

        // Validate date sorting
        $bookingDates = $this->getColumnByName(BankTransactionHeaderField::BUCHUNGSDATUM->value);
        $previousDate = null;
        foreach ($bookingDates as $currentDate) {
            if (!empty($currentDate)) {
                if ($previousDate !== null && $currentDate < $previousDate) {
                    throw new RuntimeException('Booking dates are not sorted in ascending order');
                }
                $previousDate = $currentDate;
            }
        }
    }

    /**
     * Checks if the document is ASCII processing format.
     * ASCII-Weiterverarbeitungsdateien haben keinen Header im Export.
     */
    public function isAsciiProcessingFormat(): bool {
        return $this->header instanceof BankTransactionHeaderLine;
    }

    /**
     * Returns the document as associative array with ASCII-specific metadata.
     */
    public function toAssoc(): array {
        $rows = parent::toAssoc();

        return [
            'meta' => [
                'format' => 'ASCII-Weiterverarbeitung',
                'formatType' => $this->getFormatType(),
                'columns' => $this->header->countFields(),
                'rows' => count($rows),
                'hasMetaHeader' => false,
            ],
            'data' => $rows,
        ];
    }

    /**
     * Checks if the document contains valid bank data.
     */
    public function hasValidBankData(): bool {
        foreach ($this->rows as $index => $row) {
            if (
                $this->hasField($index, BankTransactionHeaderField::BLZ_BIC_KONTOINHABER) &&
                $this->hasField($index, BankTransactionHeaderField::KONTONUMMER_IBAN_KONTOINHABER)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns account holder bank data for a specific line.
     */
    public function getAccountHolderBankData(int $rowIndex): ?array {
        if (!isset($this->rows[$rowIndex])) {
            return null;
        }

        $blz = $this->getField($rowIndex, BankTransactionHeaderField::BLZ_BIC_KONTOINHABER);
        $account = $this->getField($rowIndex, BankTransactionHeaderField::KONTONUMMER_IBAN_KONTOINHABER);

        if ($blz === null || $account === null) {
            return null;
        }

        return [
            'blz_bic' => $blz,
            'account_number' => $account,
        ];
    }

    /**
     * Returns payer bank data for a specific line.
     */
    public function getPayerBankData(int $rowIndex): ?array {
        if (!isset($this->rows[$rowIndex])) {
            return null;
        }

        // Prüfe ob die erforderlichen Felder existieren (mindestens 11 Felder)
        if ($this->getField($rowIndex, BankTransactionHeaderField::KONTONUMMER_IBAN_AUFTRAGGEBER) === null) {
            return null;
        }

        return [
            'name1' => $this->getField($rowIndex, BankTransactionHeaderField::AUFTRAGGEBERNAME_1) ?? '',
            'name2' => $this->getField($rowIndex, BankTransactionHeaderField::AUFTRAGGEBERNAME_2) ?? '',
            'blz_bic' => $this->getField($rowIndex, BankTransactionHeaderField::BLZ_BIC_AUFTRAGGEBER) ?? '',
            'account_number' => $this->getField($rowIndex, BankTransactionHeaderField::KONTONUMMER_IBAN_AUFTRAGGEBER) ?? '',
        ];
    }

    /**
     * Returns transaction data for a specific line.
     */
    public function getTransactionData(int $rowIndex, CurrencyCode $currencyCode = CurrencyCode::Euro): ?array {
        if (!isset($this->rows[$rowIndex])) {
            return null;
        }

        // Prüfe ob die erforderlichen Felder existieren (mindestens 17 Felder für Währung)
        $bookingDate = $this->getField($rowIndex, BankTransactionHeaderField::BUCHUNGSDATUM);
        if ($bookingDate === null) {
            return null;
        }

        $currency = $this->getField($rowIndex, BankTransactionHeaderField::WAEHRUNG);

        return [
            'statement_number' => $this->getField($rowIndex, BankTransactionHeaderField::AUSZUGSNUMMER) ?? '',
            'booking_date' => $bookingDate,
            'valuta_date' => $this->getField($rowIndex, BankTransactionHeaderField::VALUTA) ?? '',
            'amount' => $this->getField($rowIndex, BankTransactionHeaderField::UMSATZ) ?? '',
            'currency' => !empty($currency) ? $currency : $currencyCode->value,
        ];
    }

    /**
     * Returns remittance purposes for a specific line.
     * 
     */
    public function getUsagePurposes(int $rowIndex): array {
        if (!isset($this->rows[$rowIndex])) {
            return [];
        }

        $purposeFields = [
            BankTransactionHeaderField::VERWENDUNGSZWECK_1,
            BankTransactionHeaderField::VERWENDUNGSZWECK_2,
            BankTransactionHeaderField::VERWENDUNGSZWECK_3,
            BankTransactionHeaderField::VERWENDUNGSZWECK_4,
            BankTransactionHeaderField::VERWENDUNGSZWECK_5,
            BankTransactionHeaderField::VERWENDUNGSZWECK_6,
            BankTransactionHeaderField::VERWENDUNGSZWECK_7,
            BankTransactionHeaderField::VERWENDUNGSZWECK_8,
            BankTransactionHeaderField::VERWENDUNGSZWECK_9,
            BankTransactionHeaderField::VERWENDUNGSZWECK_10,
            BankTransactionHeaderField::VERWENDUNGSZWECK_11,
            BankTransactionHeaderField::VERWENDUNGSZWECK_12,
            BankTransactionHeaderField::VERWENDUNGSZWECK_13,
            BankTransactionHeaderField::VERWENDUNGSZWECK_14,
        ];

        $purposes = [];
        foreach ($purposeFields as $field) {
            $purpose = $this->getField($rowIndex, $field);
            if (!empty($purpose)) {
                $purposes[] = $purpose;
            }
        }

        return $purposes;
    }

    /**
     * Returns a summary of all transactions.
     */
    public function getTransactionSummary(): array {
        $totalAmount = 0.0;
        $currencies = ['EUR' => 0];
        $dates = [];
        $totalTransactions = count($this->rows);

        foreach ($this->rows as $index => $row) {
            // Betrag summieren
            $amount = (float) ($this->getField($index, BankTransactionHeaderField::UMSATZ) ?? '0');
            $totalAmount += $amount;

            // Datum sammeln
            $date = $this->getField($index, BankTransactionHeaderField::BUCHUNGSDATUM);
            if (!empty($date)) {
                $dates[] = $date;
            }
        }

        sort($dates);

        return [
            'total_transactions' => $totalTransactions,
            'total_amount' => $totalAmount,
            'currencies' => $currencies,
            'date_range' => [
                'from' => $dates[0] ?? '',
                'to' => end($dates) ?: '',
            ],
        ];
    }

    /**
     * Extracts additional amounts from the extended fields (25-34).
     * These fields often contain original amounts in other currencies,
     * conversion amounts and fees.
     *
     * @param int $rowIndex Index der Zeile (0-basiert)
     * @return array<string, string> Additional amounts with currencies
     */
    public function getAdditionalAmounts(int $rowIndex): array {
        if (!isset($this->rows[$rowIndex])) {
            return [];
        }

        $fieldMapping = [
            'original_amount' => BankTransactionHeaderField::URSPRUNGSBETRAG,
            'original_currency' => BankTransactionHeaderField::WAEHRUNG_URSPRUNGSBETRAG,
            'equivalent_amount' => BankTransactionHeaderField::AEQUIVALENZBETRAG,
            'equivalent_currency' => BankTransactionHeaderField::WAEHRUNG_AEQUIVALENZBETRAG,
            'fee_amount' => BankTransactionHeaderField::GEBUEHR,
            'fee_currency' => BankTransactionHeaderField::WAEHRUNG_GEBUEHR,
        ];

        $additionalAmounts = [];
        foreach ($fieldMapping as $key => $field) {
            $value = $this->getField($rowIndex, $field);
            if ($value !== null) {
                $additionalAmounts[$key] = $value;
            }
        }

        return $additionalAmounts;
    }
}