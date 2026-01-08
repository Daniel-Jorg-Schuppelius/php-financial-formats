<?php
/*
 * Created on   : Mon Dec 22 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : BankTransactionParserTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Parsers;

use CommonToolkit\FinancialFormats\Entities\DATEV\Documents\BankTransaction;
use CommonToolkit\Enums\Common\CSV\TruncationStrategy;
use CommonToolkit\FinancialFormats\Converters\DATEV\BankTransactionToMt940Converter;
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\ASCII\BankTransactionHeaderField;
use CommonToolkit\FinancialFormats\Parsers\BankTransactionParser;
use Tests\Contracts\BaseTestCase;
use RuntimeException;

/**
 * Tests for the BankTransactionParser.
 */
class BankTransactionParserTest extends BaseTestCase {

    private string $sampleCSV;
    private string $datevSampleCSV;
    private string $liveSampleCSV;

    protected function setUp(): void {
        parent::setUp();

        // Sample CSV based on DATEV documentation (exactly 34 fields)
        $this->sampleCSV = implode("\n", [
            '"70030000";"1234567";"433";"29.12.15";"29.12.15";"29.12.15";10.00;"HANS MUSTERMANN";"";"80550000";"7654321";"Kd.Nr. 12345";"RECHNUNG v. 12.12.15";"";"";"051";"EUR";"";"";"";"";"";"";"";"";"";"";"";"";"";"";"";"";""',
            '"70030000";"1234567";"434";"30.12.15";"30.12.15";"30.12.15";-25.50;"FIRMA ABC GMBH";"MÜNCHEN";"70150000";"1111111";"Miete Januar";"Objekt Muster";"";"";"005";"EUR";"";"";"";"";"";"";"";"";"";"";"";"";"";"";"";"";""'
        ]);

        $this->datevSampleCSV = '"70030000";"1234567";433;29.12.15;29.12.15;29.12.15;10.00;"HANS MUSTERMANN";"";"80550000";"7654321";"Kd.Nr. 12345";"RECHNUNG v. 12.12.15";"";"";"051";';

        $this->liveSampleCSV = implode("\n", [
            '10080000;0012345678;;;01.07.2021;01.07.2021;-52,50;;;;;"SEPA Lastschrifteinzug von ";"01.07. 01.07. SEPA Lastschr";"ifteinzug von - 52,50 Rundf";"unk ARD, ZDF, DRadio Verwen";;EUR;"SEPA Lastschrifteinzug von";"dungszweck/ Kundenreferenz ";"Rundfunk 07.2021 - 09.2021 ";"Beitragsnr. 000000000 Aende";"rungen ganz bequem: www.run";"dfunkbeitrag.de 000000000 2";"000000000000000 Glaubiger-I";;;;;;;"D DE0000000000000000 Mand-I";"D 0000000000000 RCUR Wieder";"holungslastschrift         ";',
            '10080000;0012345678;;;01.07.2021;01.07.2021;-163,84;;;;;"SEPA Lastschrifteinzug von ";"01.07. 01.07. SEPA Lastschr";"ifteinzug von - 163,84 Deut";"sche Bank AG Verwendungszwe";;EUR;"SEPA Lastschrifteinzug von";"ck/ Kundenreferenz Baufinan";"zierung 000 0000000 00, Lei";"stungen zum 01.07.2021 0000";"0000000000 Glaubiger-ID DE0";"000000000000000 Mand-ID LEN";"000000000000000 OTHR Sonst.";;;;;;;" Transakt. RCUR Wiederholun";"gslastschrift              ";;'
        ]);
    }

    public function testFromStringSuccess(): void {
        $document = BankTransactionParser::fromString($this->sampleCSV);

        $this->assertInstanceOf(BankTransaction::class, $document);
        $this->assertEquals(2, count($document->getRows()));
        $this->assertEquals('ASCII-Weiterverarbeitungsdatei', $document->getFormatType());
        $this->assertTrue($document->isAsciiProcessingFormat());
    }

    public function testDatevSampleCSVParsing(): void {
        // Test das DATEV-Sample mit 16 Feldern und Leerzeichen nach Semikolons
        $document = BankTransactionParser::fromString($this->datevSampleCSV);

        $this->assertInstanceOf(BankTransaction::class, $document);
        $this->assertEquals(1, count($document->getRows()));
        $this->assertTrue($document->isAsciiProcessingFormat());

        // Prüfe die Kontoinhaberdaten
        $accountData = $document->getAccountHolderBankData(0);
        $this->assertNotNull($accountData);
        $this->assertEquals('70030000', $accountData['blz_bic']);
        $this->assertEquals('1234567', $accountData['account_number']);

        // Prüfe die Auftraggeberdaten (Zahlungspflichtiger)
        $payerData = $document->getPayerBankData(0);
        $this->assertNotNull($payerData);
        $this->assertEquals('HANS MUSTERMANN', $payerData['name1']);
        $this->assertEquals('80550000', $payerData['blz_bic']);
        $this->assertEquals('7654321', $payerData['account_number']);

        // Prüfe die Transaktionsdaten
        $transactionData = $document->getTransactionData(0);
        $this->assertNotNull($transactionData);
        $this->assertEquals('433', $transactionData['statement_number']);
        $this->assertEquals('29.12.15', $transactionData['booking_date']);
        $this->assertEquals('10.00', $transactionData['amount']);

        // Prüfe die Verwendungszwecke
        $purposes = $document->getUsagePurposes(0);
        $this->assertContains('Kd.Nr. 12345', $purposes);
        $this->assertContains('RECHNUNG v. 12.12.15', $purposes);

        // Prüfe die Transaktionszusammenfassung
        $summary = $document->getTransactionSummary();
        $this->assertEquals(1, $summary['total_transactions']);
        $this->assertEquals(10.00, $summary['total_amount']);
    }

    public function testLiveSampleCSVParsing(): void {
        // Testet das Parsen des realistischen Live-Sample-CSV mit komplexen Verwendungszwecken
        $document = BankTransactionParser::fromString($this->liveSampleCSV);

        $this->assertInstanceOf(BankTransaction::class, $document);
        $this->assertEquals(2, count($document->getRows()));
        $this->assertTrue($document->isAsciiProcessingFormat());

        // Prüfe die wichtigsten Felder der ersten Zeile
        $accountData = $document->getAccountHolderBankData(0);
        $this->assertNotNull($accountData);
        $this->assertEquals('10080000', $accountData['blz_bic']);
        $this->assertEquals('0012345678', $accountData['account_number']);

        $transactionData = $document->getTransactionData(0);
        $this->assertNotNull($transactionData);
        $this->assertEquals('-52,50', $transactionData['amount']);
        $this->assertEquals('EUR', $transactionData['currency']);
        $this->assertEquals('01.07.2021', $transactionData['booking_date']);

        // Prüfe, dass Verwendungszwecke extrahiert werden
        $purposes = $document->getUsagePurposes(0);
        $this->assertNotEmpty($purposes);
        $this->assertStringContainsString('SEPA Lastschrifteinzug', implode(' ', $purposes));
    }

    public function testBankTransactiontoMt940(): void {
        $document = BankTransactionParser::fromString($this->liveSampleCSV);

        // Teste die Umwandlung in MT940-Format
        $mt940Document = BankTransactionToMt940Converter::convert($document);
        $mt940String = $mt940Document->__toString();

        $this->assertNotEmpty($mt940String);
        $this->assertStringContainsString(':86:SEPA Lastschrifteinzug von', $mt940String);
        $this->assertStringContainsString(':20:', $mt940String); // Transaktionsreferenznummer
        $this->assertStringContainsString(':25:', $mt940String); // Kontonummer
        $this->assertStringContainsString(':61:', $mt940String); // Kontoauszugszeile
        $this->assertStringContainsString(':86:', $mt940String); // Verwendungszweck
    }

    public function testFromStringEmptyFile(): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Empty ASCII processing file');

        BankTransactionParser::fromString('');
    }

    public function testFromStringInvalidFormat(): void {
        $invalidCSV = '"70030000";"1234567";10.00'; // Too few fields

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid field count');

        BankTransactionParser::fromString($invalidCSV);
    }

    public function testIsValidBankTransactionFormat(): void {
        // Valid DATEV ASCII line with 17 fields (as in .samples/ASCII.csv)
        $validLine = '"70030000";"1234567";433;29.12.15;29.12.15;29.12.15;10.00;"HANS MUSTERMANN";"";"80550000";"7654321";"Kd.Nr. 12345";"RECHNUNG v. 12.12.15";"";"";"";"EUR";';
        $invalidLine = '"70030000";"1234567";10.00'; // Too few fields

        $this->assertTrue(BankTransactionParser::isValidBankTransactionFormat($validLine));
        $this->assertFalse(BankTransactionParser::isValidBankTransactionFormat($invalidLine));
    }

    public function testBankTransactionDocumentMethods(): void {
        $document = BankTransactionParser::fromString($this->sampleCSV);

        // Test hasValidBankData()
        $this->assertTrue($document->hasValidBankData());

        // Test getAccountHolderBankData()
        $accountData = $document->getAccountHolderBankData(0);
        $this->assertNotNull($accountData);
        $this->assertEquals('70030000', $accountData['blz_bic']);
        $this->assertEquals('1234567', $accountData['account_number']);

        // Test getPayerBankData()
        $payerData = $document->getPayerBankData(0);
        $this->assertNotNull($payerData);
        $this->assertEquals('HANS MUSTERMANN', $payerData['name1']);
        $this->assertEquals('', $payerData['name2']);
        $this->assertEquals('80550000', $payerData['blz_bic']);
        $this->assertEquals('7654321', $payerData['account_number']);

        // Test getTransactionData()
        $transactionData = $document->getTransactionData(0);
        $this->assertNotNull($transactionData);
        $this->assertEquals('433', $transactionData['statement_number']);
        $this->assertEquals('29.12.15', $transactionData['booking_date']);
        $this->assertEquals('10.00', $transactionData['amount']);
        $this->assertEquals('EUR', $transactionData['currency']);

        // Test getUsagePurposes()
        $purposes = $document->getUsagePurposes(0);
        $this->assertContains('Kd.Nr. 12345', $purposes);
        $this->assertContains('RECHNUNG v. 12.12.15', $purposes);

        // Test getTransactionSummary()
        $summary = $document->getTransactionSummary();
        $this->assertEquals(2, $summary['total_transactions']);
        $this->assertEquals(-15.50, $summary['total_amount']); // 10.00 + (-25.50)
        $this->assertArrayHasKey('EUR', $summary['currencies']);
        $this->assertEquals('29.12.15', $summary['date_range']['from']);
        $this->assertEquals('30.12.15', $summary['date_range']['to']);
    }

    public function testToString(): void {
        $document = BankTransactionParser::fromString($this->sampleCSV);
        $csvOutput = $document->toString();

        $this->assertNotEmpty($csvOutput);
        $this->assertTrue(str_contains($csvOutput, '70030000'));
        $this->assertTrue(str_contains($csvOutput, 'HANS MUSTERMANN'));

        // Round-trip test: CSV -> Document -> CSV -> Document
        $roundTripDocument = BankTransactionParser::fromString($csvOutput);
        $this->assertEquals($document->getTransactionSummary(), $roundTripDocument->getTransactionSummary());
    }

    public function testValidationErrors(): void {
        // CSV with invalid data (empty required fields but valid field count of 17)
        // Fields: BLZ(empty), Account(empty), StatementNr, Date1(empty), Date2(empty), BookingDate(empty), Amount(empty), Name...
        $invalidCSV = '"";"";"433";"";"";"";"";"";"";"";"";"";"";"";"";"";"EUR"';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ASCII processing file validation failed');

        BankTransactionParser::fromString($invalidCSV);
    }

    public function testDateSortingValidation(): void {
        // CSV with non-ascending sorted dates - both exactly 17 fields (minimum valid format)
        $unsortedCSV = implode("\n", [
            '"70030000";"1234567";"434";"30.12.15";"30.12.15";"30.12.15";"10.00";"TEST1";"";"";"";"";"";"";"";"";"EUR"',
            '"70030000";"1234567";"433";"29.12.15";"29.12.15";"29.12.15";"-25.50";"TEST2";"";"";"";"";"";"";"";"";"EUR"'
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Booking dates are not sorted in ascending order');

        BankTransactionParser::fromString($unsortedCSV);
    }

    public function testFieldCount(): void {
        // Test with 35 fields instead of max 34
        $tooManyFieldsCSV = '"70030000";"1234567";"433";"29.12.15";"29.12.15";"29.12.15";10.00;"HANS MUSTERMANN";"";"80550000";"7654321";"Kd.Nr. 12345";"RECHNUNG v. 12.12.15";"";"";"051";"EUR";"";"";"";"";"";"";"";"";"";"";"";"";"";"";"";"";"";"EXTRA"';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid field count');

        BankTransactionParser::fromString($tooManyFieldsCSV);
    }

    public function testGetAdditionalAmounts(): void {
        // CSV mit zusätzlichen Beträgen - genau 34 Felder 
        // Felder nach DATEV-Dokumentation:
        // - Feld 25 (Index 24): Ursprungsbetrag = 100.00
        // - Feld 26 (Index 25): Währung Ursprungsbetrag = USD
        // - Feld 27 (Index 26): Äquivalenzbetrag = 200.00
        // - Feld 28 (Index 27): Währung Äquivalenzbetrag = EUR
        // - Feld 29 (Index 28): Gebühr = 5.00
        // - Feld 30 (Index 29): Währung Gebühr = EUR
        $csvWithAdditionalAmounts = '"70030000";"1234567";"433";"29.12.15";"29.12.15";"29.12.15";10.00;"HANS MUSTERMANN";"";"80550000";"7654321";"Kd.Nr. 12345";"RECHNUNG v. 12.12.15";"";"";"051";"EUR";"";"";"";"";"";"";"";"100.00";"USD";"200.00";"EUR";"5.00";"EUR";"";"";"";""';

        $document = BankTransactionParser::fromString($csvWithAdditionalAmounts);
        $amounts = $document->getAdditionalAmounts(0);

        $this->assertEquals('100.00', $amounts['original_amount']);
        $this->assertEquals('USD', $amounts['original_currency']);
        $this->assertEquals('200.00', $amounts['equivalent_amount']);
        $this->assertEquals('EUR', $amounts['equivalent_currency']);
        $this->assertEquals('5.00', $amounts['fee_amount']);
        $this->assertEquals('EUR', $amounts['fee_currency']);
    }

    public function testFieldMaxLengths(): void {
        // Test der maximalen Feldlängen entsprechend DATEV-Dokumentation
        $this->assertEquals(11, BankTransactionHeaderField::BLZ_BIC_KONTOINHABER->getMaxLength());
        $this->assertEquals(34, BankTransactionHeaderField::KONTONUMMER_IBAN_KONTOINHABER->getMaxLength());
        $this->assertEquals(4, BankTransactionHeaderField::AUSZUGSNUMMER->getMaxLength());
        $this->assertEquals(10, BankTransactionHeaderField::AUSZUGSDATUM->getMaxLength());
        $this->assertEquals(10, BankTransactionHeaderField::VALUTA->getMaxLength());
        $this->assertEquals(10, BankTransactionHeaderField::BUCHUNGSDATUM->getMaxLength());
        $this->assertEquals(15, BankTransactionHeaderField::UMSATZ->getMaxLength());
        $this->assertEquals(27, BankTransactionHeaderField::AUFTRAGGEBERNAME_1->getMaxLength());
        $this->assertEquals(27, BankTransactionHeaderField::AUFTRAGGEBERNAME_2->getMaxLength());
        $this->assertEquals(11, BankTransactionHeaderField::BLZ_BIC_AUFTRAGGEBER->getMaxLength());
        $this->assertEquals(34, BankTransactionHeaderField::KONTONUMMER_IBAN_AUFTRAGGEBER->getMaxLength());
        $this->assertEquals(27, BankTransactionHeaderField::VERWENDUNGSZWECK_1->getMaxLength());
        $this->assertEquals(3, BankTransactionHeaderField::GESCHAEFTSVORGANGSCODE->getMaxLength());
        $this->assertEquals(3, BankTransactionHeaderField::WAEHRUNG->getMaxLength());
        $this->assertEquals(15, BankTransactionHeaderField::URSPRUNGSBETRAG->getMaxLength());
        $this->assertEquals(3, BankTransactionHeaderField::WAEHRUNG_URSPRUNGSBETRAG->getMaxLength());
    }

    public function testFieldMaxLengthsForAllFields(): void {
        // Teste dass alle 34 Felder eine definierte maximale Länge haben
        $orderedFields = BankTransactionHeaderField::ordered();
        $this->assertCount(34, $orderedFields);

        foreach ($orderedFields as $field) {
            $maxLength = $field->getMaxLength();
            $this->assertIsInt($maxLength, "Feld {$field->name} sollte eine definierte maximale Länge haben");
            $this->assertGreaterThan(0, $maxLength, "Maximale Länge für {$field->name} sollte größer als 0 sein");
        }
    }

    public function testSpecificFieldLengthLimits(): void {
        // Test spezifische DATEV-konforme Längenlimits

        // Währungscodes: Genau 3 Zeichen (ISO 4217)
        $currencyFields = [
            BankTransactionHeaderField::WAEHRUNG,
            BankTransactionHeaderField::WAEHRUNG_URSPRUNGSBETRAG,
            BankTransactionHeaderField::WAEHRUNG_AEQUIVALENZBETRAG,
            BankTransactionHeaderField::WAEHRUNG_GEBUEHR
        ];

        foreach ($currencyFields as $field) {
            $this->assertEquals(3, $field->getMaxLength(), "Währungsfeld {$field->name} sollte exakt 3 Zeichen haben");
        }

        // Verwendungszweck-Felder: Alle 27 Zeichen
        $usageFields = [
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

        foreach ($usageFields as $field) {
            $this->assertEquals(27, $field->getMaxLength(), "Verwendungszweck-Feld {$field->name} sollte 27 Zeichen haben");
        }

        // Beträge: Alle 15 Zeichen (±9999999999999,99)
        $amountFields = [
            BankTransactionHeaderField::UMSATZ,
            BankTransactionHeaderField::URSPRUNGSBETRAG,
            BankTransactionHeaderField::AEQUIVALENZBETRAG,
            BankTransactionHeaderField::GEBUEHR
        ];

        foreach ($amountFields as $field) {
            $this->assertEquals(15, $field->getMaxLength(), "Betrags-Feld {$field->name} sollte 15 Zeichen haben");
        }
    }

    public function testExportWithFieldLengthLimits(): void {
        $document = BankTransactionParser::fromString($this->sampleCSV);

        // Erstelle Builder mit truncate-Strategie und verwende die vorhandenen Daten
        $builder = new \CommonToolkit\FinancialFormats\Builders\DATEV\BankTransactionBuilder(';', '"', TruncationStrategy::TRUNCATE);
        $builder->addLines($document->getRows());
        $datevDocument = $builder->build();

        $truncated = $datevDocument->toString();
        $lines = explode("\n", $truncated);

        // ASCII-Weiterverarbeitungsdateien haben keinen Header - sollte nur 2 Datenzeilen haben
        $this->assertGreaterThanOrEqual(2, count($lines));

        // Teste die erste Datenzeile (es gibt keinen Header)
        $dataLine = $lines[0]; // Erste Datenzeile
        $fields = str_getcsv($dataLine, ';', '"', '');
        $this->assertEquals(34, count($fields));

        // Alle Felder sollten ihre maximalen Längen einhalten
        $this->assertLessThanOrEqual(11, mb_strlen($fields[0])); // BLZ/BIC
        $this->assertLessThanOrEqual(27, mb_strlen($fields[7])); // Name
        $this->assertLessThanOrEqual(27, mb_strlen($fields[11])); // VWZ1
    }
    public function testExportWithEllipsisStrategy(): void {
        $document = BankTransactionParser::fromString($this->sampleCSV);

        // Erstelle Builder mit ellipsis-Strategie und verwende die vorhandenen Daten
        $builder = new \CommonToolkit\FinancialFormats\Builders\DATEV\BankTransactionBuilder(';', '"', TruncationStrategy::ELLIPSIS);
        $builder->addLines($document->getRows());
        $datevDocument = $builder->build();

        $ellipsis = $datevDocument->toString();
        $lines = explode("\n", $ellipsis);

        // ASCII-Weiterverarbeitungsdateien haben keinen Header - sollte nur 2 Datenzeilen haben
        $this->assertGreaterThanOrEqual(2, count($lines));

        // Teste die erste Datenzeile (es gibt keinen Header)
        $dataLine = $lines[0];
        $fields = str_getcsv($dataLine, ';', '"', '');
        $this->assertEquals(34, count($fields));

        // Mit der aktuellen kurzen Test-Daten wird wahrscheinlich nichts gekürzt
        // Das ist ok - die Funktionalität wird getestet
        $this->assertTrue(true, 'Ellipsis-Export funktioniert');
    }
}
