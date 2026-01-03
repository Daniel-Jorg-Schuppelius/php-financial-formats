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

    // ==== Banking-spezifische Methoden ====

    /**
     * Checks if the document contains valid bank data.
     */
    public function hasValidBankData(): bool {
        foreach ($this->rows as $row) {
            $fields = $row->getFields();
            if (count($fields) < 11) continue;

            $blz = trim($fields[0]->getValue());
            $account = trim($fields[1]->getValue());

            if (!empty($blz) && !empty($account)) {
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

        $fields = $this->rows[$rowIndex]->getFields();
        if (count($fields) < 2) {
            return null;
        }

        return [
            'blz_bic' => trim($fields[0]->getValue()),
            'account_number' => trim($fields[1]->getValue()),
        ];
    }

    /**
     * Returns payer bank data for a specific line.
     */
    public function getPayerBankData(int $rowIndex): ?array {
        if (!isset($this->rows[$rowIndex])) {
            return null;
        }

        $fields = $this->rows[$rowIndex]->getFields();
        if (count($fields) < 11) {
            return null;
        }

        return [
            'name1' => trim($fields[7]->getValue()),
            'name2' => trim($fields[8]->getValue()),
            'blz_bic' => trim($fields[9]->getValue()),
            'account_number' => trim($fields[10]->getValue()),
        ];
    }

    /**
     * Returns transaction data for a specific line.
     */
    public function getTransactionData(int $rowIndex, CurrencyCode $currencyCode = CurrencyCode::Euro): ?array {
        if (!isset($this->rows[$rowIndex])) {
            return null;
        }

        $fields = $this->rows[$rowIndex]->getFields();
        if (count($fields) < 17) {
            return null;
        }

        // Für 34-Feld Format:
        // - Auszugsnummer ist Feld 3 (Index 2)
        // - Buchungsdatum ist Feld 6 (Index 5)
        // - Valutadatum ist Feld 5 (Index 4)
        // - Betrag ist Feld 7 (Index 6)
        // - Currency ist bei Position 17 (Index 16) falls vorhanden

        $currency = count($fields) > 16 ? trim($fields[16]->getValue()) : $currencyCode->value;

        return [
            'statement_number' => trim($fields[2]->getValue()),
            'booking_date' => trim($fields[5]->getValue()),
            'valuta_date' => trim($fields[4]->getValue()),
            'amount' => trim($fields[6]->getValue()),
            'currency' => $currency,
        ];
    }

    /**
     * Returns remittance purposes for a specific line.
     */
    public function getUsagePurposes(int $rowIndex): array {
        if (!isset($this->rows[$rowIndex])) {
            return [];
        }

        $fields = $this->rows[$rowIndex]->getFields();
        if (count($fields) < 14) {
            return [];
        }

        $purposes = [];
        for ($i = 11; $i <= 13; $i++) {
            $purpose = trim($fields[$i]->getValue());
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

        foreach ($this->rows as $row) {
            $fields = $row->getFields();
            if (count($fields) < 7) continue;

            // Betrag summieren
            $amount = (float) trim($fields[6]->getValue());
            $totalAmount += $amount;

            // Datum sammeln
            if (count($fields) >= 6) {
                $date = trim($fields[5]->getValue());
                if (!empty($date)) {
                    $dates[] = $date;
                }
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
        $row = $this->getRow($rowIndex);
        if ($row === null) {
            return [];
        }

        $fields = $row->getFields();
        $additionalAmounts = [];

        // Felder 26-34 für zusätzliche Beträge (Index 25-33)
        // Feld 27 (Index 26): Ursprungsbetrag
        // Feld 28 (Index 27): Ursprungswährung
        // Feld 29 (Index 28): Äquivalentbetrag
        // Feld 30 (Index 29): Äquivalentwährung
        // Feld 31 (Index 30): Gebührenbetrag
        // Feld 32 (Index 31): Gebührenwährung

        if (count($fields) >= 27) {
            $additionalAmounts['original_amount'] = trim($fields[26]->getValue());
        }
        if (count($fields) >= 28) {
            $additionalAmounts['original_currency'] = trim($fields[27]->getValue());
        }
        if (count($fields) >= 29) {
            $additionalAmounts['equivalent_amount'] = trim($fields[28]->getValue());
        }
        if (count($fields) >= 30) {
            $additionalAmounts['equivalent_currency'] = trim($fields[29]->getValue());
        }
        if (count($fields) >= 31) {
            $additionalAmounts['fee_amount'] = trim($fields[30]->getValue());
        }
        if (count($fields) >= 32) {
            $additionalAmounts['fee_currency'] = trim($fields[31]->getValue());
        }

        return $additionalAmounts;
    }
}
