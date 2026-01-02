<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : BankTransactionTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\DATEV\Documents;

use CommonToolkit\Entities\CSV\DataField;
use CommonToolkit\Entities\CSV\DataLine;
use CommonToolkit\FinancialFormats\Entities\DATEV\Documents\BankTransaction;
use CommonToolkit\FinancialFormats\Entities\DATEV\Header\ASCII\BankTransactionHeaderLine;
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\ASCII\BankTransactionHeaderField;
use Tests\Contracts\BaseTestCase;

/**
 * Tests für die DATEV BankTransaction Document Entity.
 */
class BankTransactionTest extends BaseTestCase {
    private function createTestDataLine(array $values): DataLine {
        $fields = [];
        foreach ($values as $value) {
            $fields[] = new DataField((string)$value, '"');
        }
        return new DataLine($fields, ';', '"');
    }

    private function createFullTestRow(): DataLine {
        // Erstelle eine vollständige 34-Feld Zeile
        $values = [];
        foreach (BankTransactionHeaderField::ordered() as $field) {
            $values[] = match ($field) {
                BankTransactionHeaderField::BLZ_BIC_KONTOINHABER => '37040044',
                BankTransactionHeaderField::KONTONUMMER_IBAN_KONTOINHABER => 'DE89370400440532013000',
                BankTransactionHeaderField::AUSZUGSNUMMER => '001',
                BankTransactionHeaderField::AUSZUGSDATUM => '15012025',
                BankTransactionHeaderField::VALUTA => '15012025',
                BankTransactionHeaderField::BUCHUNGSDATUM => '15012025',
                BankTransactionHeaderField::UMSATZ => '1000,50',
                BankTransactionHeaderField::AUFTRAGGEBERNAME_1 => 'Max Mustermann',
                BankTransactionHeaderField::AUFTRAGGEBERNAME_2 => 'GmbH',
                BankTransactionHeaderField::BLZ_BIC_AUFTRAGGEBER => 'COBADEFFXXX',
                BankTransactionHeaderField::KONTONUMMER_IBAN_AUFTRAGGEBER => 'DE89370400440000000001',
                BankTransactionHeaderField::VERWENDUNGSZWECK_1 => 'Rechnung Nr. 12345',
                BankTransactionHeaderField::VERWENDUNGSZWECK_2 => 'Kunde: Mustermann',
                BankTransactionHeaderField::VERWENDUNGSZWECK_3 => 'Januar 2025',
                BankTransactionHeaderField::VERWENDUNGSZWECK_4 => '',
                BankTransactionHeaderField::GESCHAEFTSVORGANGSCODE => '051',
                BankTransactionHeaderField::WAEHRUNG => 'EUR',
                default => '',
            };
        }
        return $this->createTestDataLine($values);
    }

    public function testConstructor(): void {
        $document = new BankTransaction();

        $this->assertEmpty($document->getRows());
        $this->assertInstanceOf(BankTransactionHeaderLine::class, $document->getHeader());
        $this->assertSame('ASCII-Weiterverarbeitungsdatei', $document->getFormatType());
        $this->assertTrue($document->isAsciiProcessingFormat());
    }

    public function testConstructorWithRows(): void {
        $row = $this->createFullTestRow();
        $document = new BankTransaction(rows: [$row]);

        $this->assertCount(1, $document->getRows());
    }

    public function testGetAccountHolderBankData(): void {
        $row = $this->createFullTestRow();
        $document = new BankTransaction(rows: [$row]);

        $bankData = $document->getAccountHolderBankData(0);

        $this->assertNotNull($bankData);
        $this->assertSame('37040044', $bankData['blz_bic']);
        $this->assertSame('DE89370400440532013000', $bankData['account_number']);
    }

    public function testGetAccountHolderBankDataInvalidIndex(): void {
        $document = new BankTransaction();

        $bankData = $document->getAccountHolderBankData(0);

        $this->assertNull($bankData);
    }

    public function testGetPayerBankData(): void {
        $row = $this->createFullTestRow();
        $document = new BankTransaction(rows: [$row]);

        $payerData = $document->getPayerBankData(0);

        $this->assertNotNull($payerData);
        $this->assertSame('Max Mustermann', $payerData['name1']);
        $this->assertSame('GmbH', $payerData['name2']);
        $this->assertSame('COBADEFFXXX', $payerData['blz_bic']);
        $this->assertSame('DE89370400440000000001', $payerData['account_number']);
    }

    public function testGetTransactionData(): void {
        $row = $this->createFullTestRow();
        $document = new BankTransaction(rows: [$row]);

        $transactionData = $document->getTransactionData(0);

        $this->assertNotNull($transactionData);
        $this->assertSame('001', $transactionData['statement_number']);
        $this->assertSame('15012025', $transactionData['booking_date']);
        $this->assertSame('15012025', $transactionData['valuta_date']);
        $this->assertSame('1000,50', $transactionData['amount']);
        $this->assertSame('EUR', $transactionData['currency']);
    }

    public function testGetUsagePurposes(): void {
        $row = $this->createFullTestRow();
        $document = new BankTransaction(rows: [$row]);

        $purposes = $document->getUsagePurposes(0);

        $this->assertCount(3, $purposes);
        $this->assertSame('Rechnung Nr. 12345', $purposes[0]);
        $this->assertSame('Kunde: Mustermann', $purposes[1]);
        $this->assertSame('Januar 2025', $purposes[2]);
    }

    public function testGetUsagePurposesWithEmptyFields(): void {
        $document = new BankTransaction();

        $purposes = $document->getUsagePurposes(0);

        $this->assertEmpty($purposes);
    }

    public function testHasValidBankData(): void {
        $row = $this->createFullTestRow();
        $document = new BankTransaction(rows: [$row]);

        $this->assertTrue($document->hasValidBankData());
    }

    public function testHasValidBankDataEmpty(): void {
        $document = new BankTransaction();

        $this->assertFalse($document->hasValidBankData());
    }

    public function testGetTransactionSummary(): void {
        $row = $this->createFullTestRow();
        $document = new BankTransaction(rows: [$row]);

        $summary = $document->getTransactionSummary();

        $this->assertSame(1, $summary['total_transactions']);
        $this->assertArrayHasKey('total_amount', $summary);
        $this->assertArrayHasKey('currencies', $summary);
        $this->assertArrayHasKey('date_range', $summary);
    }

    public function testToAssoc(): void {
        $row = $this->createFullTestRow();
        $document = new BankTransaction(rows: [$row]);

        $assoc = $document->toAssoc();

        $this->assertArrayHasKey('meta', $assoc);
        $this->assertArrayHasKey('data', $assoc);
        $this->assertSame('ASCII-Weiterverarbeitung', $assoc['meta']['format']);
        $this->assertSame('ASCII-Weiterverarbeitungsdatei', $assoc['meta']['formatType']);
        $this->assertFalse($assoc['meta']['hasMetaHeader']);
        $this->assertCount(1, $assoc['data']);
    }

    public function testCreateDatevColumnWidthConfig(): void {
        $config = BankTransaction::createDatevColumnWidthConfig();

        $this->assertNotNull($config);
        // Prüfe, dass die Konfiguration die korrekten Spaltenbreiten hat
        foreach (BankTransactionHeaderField::ordered() as $index => $field) {
            $maxLength = $field->getMaxLength();
            if ($maxLength !== null) {
                $this->assertSame($maxLength, $config->getColumnWidth($index));
            }
        }
    }

    public function testValidateWithValidData(): void {
        $row = $this->createFullTestRow();
        $document = new BankTransaction(rows: [$row]);

        // Sollte keine Exception werfen
        $document->validate();
        $this->assertTrue(true);
    }
}
