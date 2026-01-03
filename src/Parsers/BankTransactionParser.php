<?php
/*
 * Created on   : Mon Dec 22 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : BankTransactionParser.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Parsers;

use CommonToolkit\Contracts\Abstracts\HelperAbstract;
use CommonToolkit\Entities\CSV\DataLine;
use CommonToolkit\Entities\CSV\Document;
use CommonToolkit\Helper\FileSystem\File;
use CommonToolkit\Parsers\CSVDocumentParser;
use CommonToolkit\FinancialFormats\Entities\DATEV\Documents\BankTransaction;
use CommonToolkit\FinancialFormats\Entities\DATEV\Header\ASCII\BankTransactionHeaderDefinition;
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\ASCII\BankTransactionHeaderField;
use Exception;
use RuntimeException;

/**
 * Parser for DATEV ASCII processing files for bank account transactions.
 * 
 * Standalone parser for the special ASCII format without MetaHeader.
 * This format is independent of the standard DATEV import/export system.
 * 
 * @see https://help-center.apps.datev.de/documents/9226961
 */
class BankTransactionParser extends HelperAbstract {

    /**
     * Parses an ASCII processing file from a string.
     *
     * @param string $csv The CSV content
     * @param string $delimiter CSV delimiter (default: semicolon)
     * @param string $enclosure CSV text delimiter (default: double quotes)
     * @param bool $hasHeader Ignored - ASCII files have no header
     * @return BankTransaction The parsed BankTransaction document
     * @throws RuntimeException On parsing errors or invalid format
     */
    public static function fromString(string $csv, string $delimiter = ';', string $enclosure = '"', bool $hasHeader = false): BankTransaction {
        // Handle empty input before delegating to CSVDocumentParser
        if (trim($csv) === '') {
            static::logError('Empty ASCII processing file');
            throw new RuntimeException('Empty ASCII processing file');
        }

        // Use CSVDocumentParser for robust CSV parsing (handles multi-line fields)
        try {
            $csvDocument = CSVDocumentParser::fromString($csv, $delimiter, $enclosure, $hasHeader);
        } catch (RuntimeException $e) {
            static::logError('CSV parsing failed: ' . $e->getMessage());
            throw new RuntimeException('ASCII processing file parsing failed: ' . $e->getMessage(), 0, $e);
        }

        return self::fromDocument($csvDocument);
    }

    /**
     * Parses an ASCII processing file from a file.
     *
     * Uses CSVDocumentParser::fromFile() for robust parsing with automatic encoding detection.
     *
     * @param string $filePath Path to the CSV file
     * @param string $delimiter CSV delimiter (default: semicolon)
     * @param string $enclosure CSV text delimiter (default: double quotes)
     * @param bool $hasHeader Ignored - ASCII files have no header
     * @return BankTransaction The parsed BankTransaction document
     * @throws RuntimeException On file access or parsing errors
     */
    public static function fromFile(string $filePath, string $delimiter = ';', string $enclosure = '"', bool $hasHeader = false): BankTransaction {
        // Use CSVDocumentParser::fromFile() for automatic encoding detection
        try {
            $csvDocument = CSVDocumentParser::fromFile($filePath, $delimiter, $enclosure, false);
        } catch (RuntimeException $e) {
            static::logError('File parsing failed: ' . $e->getMessage());
            throw new RuntimeException('ASCII processing file parsing failed: ' . $e->getMessage(), 0, $e);
        }

        return self::fromDocument($csvDocument);
    }

    /**
     * Creates a BankTransaction from a parsed CSV Document.
     *
     * @param Document $csvDocument The parsed CSV document
     * @return BankTransaction The validated BankTransaction document
     * @throws RuntimeException On validation errors
     */
    public static function fromDocument(Document $csvDocument): BankTransaction {
        $dataRows = $csvDocument->getRows();

        if (empty($dataRows)) {
            static::logError('Empty ASCII processing file');
            throw new RuntimeException('Empty ASCII processing file');
        }

        // Create BankTransaction and validate
        $document = new BankTransaction($dataRows);
        $document->validate();

        return $document;
    }

    /**
     * Checks if this is a valid ASCII processing file.
     */
    public static function isValidBankTransactionFormat(string $firstLine, string $delimiter = ';', string $enclosure = '"'): bool {
        try {
            $dataLine = DataLine::fromString($firstLine, $delimiter, $enclosure);

            // ASCII processing files have at least 7 fields (up to amount), max 34 fields
            $fieldCount = $dataLine->countFields();
            if ($fieldCount < 7 || $fieldCount > 34) {
                return false;
            }

            // Format detection via HeaderDefinition
            $values = array_map(fn($field) => $field->getValue(), $dataLine->getFields());
            $definition = new BankTransactionHeaderDefinition();
            return $definition->matches($values);
        } catch (Exception $e) {
            static::logError('Format validation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**

     * Checks if a file is an ASCII processing file.
     */
    public static function canParse(string $filePath): bool {
        if (!File::exists($filePath)) {
            return false;
        }

        try {
            $lines = File::readLinesAsArray($filePath, true, 1);
            if (empty($lines)) {
                return false;
            }
            return self::isValidBankTransactionFormat($lines[0]);
        } catch (Exception) {
            return false;
        }
    }
}