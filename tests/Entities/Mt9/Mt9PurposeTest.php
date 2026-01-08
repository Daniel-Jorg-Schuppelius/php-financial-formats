<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt940PurposeTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\CommonToolkit\FinancialFormats\Entities\Mt9;

use CommonToolkit\FinancialFormats\Entities\Mt9\Purpose;
use CommonToolkit\FinancialFormats\Enums\Mt\GvcCode;
use CommonToolkit\FinancialFormats\Enums\Mt\PurposeCode;
use CommonToolkit\FinancialFormats\Enums\Mt\TextKeyExtension;
use Tests\Contracts\BaseTestCase;

class Mt9PurposeTest extends BaseTestCase {

    public function testParseStructuredPurpose(): void {
        $lines = [
            '166SEPA-Lastschrift',
            '?00FOLGELASTSCHRIFT',
            '?10123456',
            '?20EREF+ORDER12345',
            '?21MREF+MANDATE67890',
            '?22CRED+DE98ZZZ09999999999',
            '?23SVWZ+Mitgliedsbeitrag',
            '?24Januar 2025',
            '?30DEUTDEDB',
            '?31DE89370400440532013000',
            '?32Max Mustermann',
            '?33GmbH',
            '?34997',
        ];

        $purpose = Purpose::fromRawLines($lines);

        $this->assertEquals(GvcCode::SEPA_CREDIT_TRANSFER, $purpose->getGvcCode());
        $this->assertEquals('FOLGELASTSCHRIFT', $purpose->getBookingText());
        $this->assertEquals('123456', $purpose->getPrimanotenNr());
        $this->assertCount(5, $purpose->getPurposeLines());
        $this->assertEquals('DEUTDEDB', $purpose->getPayerBlz());
        $this->assertEquals('DE89370400440532013000', $purpose->getPayerAccount());
        $this->assertEquals('Max Mustermann', $purpose->getPayerName1());
        $this->assertEquals('GmbH', $purpose->getPayerName2());
        $this->assertEquals('Max Mustermann GmbH', $purpose->getPayerName());
        $this->assertEquals(TextKeyExtension::SEPA_CREDIT, $purpose->getTextKeyExt());
    }

    public function testParseUnstructuredPurpose(): void {
        $lines = [
            'SEPA-Überweisung',
            'Rechnung 12345',
        ];

        $purpose = Purpose::fromRawLines($lines);

        $this->assertNull($purpose->getGvcCode());
        $this->assertNull($purpose->getBookingText());
        $this->assertStringContainsString('SEPA-Überweisung', $purpose->getRawText());
        $this->assertStringContainsString('Rechnung 12345', $purpose->getRawText());
    }

    public function testFromString(): void {
        $purpose = Purpose::fromString('Testzahlung für Projekt ABC');

        $this->assertEquals('Testzahlung für Projekt ABC', $purpose->getRawText());
        $this->assertEquals('Testzahlung für Projekt ABC', $purpose->getFullText());
    }

    public function testGetPurposeText(): void {
        $purpose = new Purpose(
            gvcCode: GvcCode::TRANSFER,
            purposeLines: ['Verwendungszweck Zeile 1', 'Zeile 2', 'Zeile 3']
        );

        $this->assertEquals('Verwendungszweck Zeile 1 Zeile 2 Zeile 3', $purpose->getPurposeText());
    }

    public function testGetFullText(): void {
        $purpose = new Purpose(
            bookingText: 'ÜBERWEISUNG',
            purposeLines: ['Rechnung 12345'],
            payerName1: 'Max Mustermann',
            payerName2: 'GmbH'
        );

        $fullText = $purpose->getFullText();
        $this->assertStringContainsString('ÜBERWEISUNG', $fullText);
        $this->assertStringContainsString('Rechnung 12345', $fullText);
        $this->assertStringContainsString('Max Mustermann GmbH', $fullText);
    }

    public function testToMt940Lines(): void {
        $purpose = new Purpose(
            gvcCode: GvcCode::TRANSFER,
            bookingText: 'ÜBERWEISUNG',
            primanotenNr: '12345',
            purposeLines: ['Verwendungszweck Zeile 1', 'Zeile 2'],
            payerBlz: 'DEUTDEDB',
            payerAccount: 'DE89370400440532013000',
            payerName1: 'Max Mustermann',
            payerName2: 'GmbH'
        );

        $lines = $purpose->toMt940Lines();

        $this->assertStringStartsWith(':86:', $lines[0]);
        $this->assertLessThanOrEqual(6, count($lines));
        $this->assertStringContainsString('ÜBERWEISUNG', $lines[0]);
    }

    public function testToMt940LinesFromRawText(): void {
        // Langer Text, der in 27-Zeichen-Segmente aufgeteilt werden muss
        $longText = 'SEPA-Überweisung Max Mustermann GmbH Rechnung 12345 vom 01.01.2025';
        $purpose = Purpose::fromString($longText);

        $lines = $purpose->toMt940Lines();

        $this->assertStringStartsWith(':86:', $lines[0]);
        // Es sollte Fortsetzungszeilen geben
        $this->assertGreaterThan(1, count($lines));
    }

    public function testToDatevLines(): void {
        $purpose = new Purpose(
            gvcCode: GvcCode::TRANSFER,
            bookingText: 'ÜBERWEISUNG',
            primanotenNr: '12345',
            purposeLines: ['Verwendungszweck Zeile 1', 'Zeile 2'],
            payerBlz: 'DEUTDEDB',
            payerAccount: 'DE89370400440532013000',
            payerName1: 'Max Mustermann',
            payerName2: 'GmbH'
        );

        $lines = $purpose->toDatevLines();

        $this->assertStringStartsWith(':86:020', $lines[0]);
        $this->assertContains('?1012345', $lines);
        $this->assertContains('?20Verwendungszweck Zeile 1', $lines);
        $this->assertContains('?21Zeile 2', $lines);
        $this->assertContains('?30DEUTDEDB', $lines);
        $this->assertContains('?31DE89370400440532013000', $lines);
        $this->assertContains('?32Max Mustermann', $lines);
        $this->assertContains('?33GmbH', $lines);
    }

    public function testToString(): void {
        $purpose = new Purpose(
            purposeLines: ['Test Verwendungszweck']
        );

        $this->assertEquals('Test Verwendungszweck', (string) $purpose);
    }

    public function testExtendedPurposeFields(): void {
        // Test für ?60-?63 Felder
        $lines = [
            '020ÜBERWEISUNG',
            '?20Zeile 20',
            '?21Zeile 21',
            '?22Zeile 22',
            '?23Zeile 23',
            '?24Zeile 24',
            '?25Zeile 25',
            '?26Zeile 26',
            '?27Zeile 27',
            '?28Zeile 28',
            '?29Zeile 29',
            '?60Zeile 60',
            '?61Zeile 61',
            '?62Zeile 62',
            '?63Zeile 63',
        ];

        $purpose = Purpose::fromRawLines($lines);

        // Sollte alle 14 Verwendungszweck-Zeilen enthalten
        $this->assertCount(14, $purpose->getPurposeLines());
    }

    public function testFromStringWithDatevFormat(): void {
        // DATEV format with all ?xx fields in one line
        $raw = "166?00SEPA-ÜBERWEISUNG?10PRIM123?20EREF+ORDER-2025-0001?21MREF+MANDATE-001?22CRED+DE98ZZZ09999999999?23SVWZ+Gehalt Januar 2025?24Lohn und Gehaltsabrechnung?30COBADEFFXXX?31DE89370400440532013001?32Max Mustermann?33GmbH?34997";

        $purpose = Purpose::fromString($raw);

        $this->assertEquals(GvcCode::SEPA_CREDIT_TRANSFER, $purpose->getGvcCode());
        $this->assertEquals('SEPA-ÜBERWEISUNG', $purpose->getBookingText());
        $this->assertEquals('PRIM123', $purpose->getPrimanotenNr());
        $this->assertEquals('ORDER-2025-0001', $purpose->getEndToEndReference());
        $this->assertEquals('MANDATE-001', $purpose->getMandateReference());
        $this->assertEquals('DE98ZZZ09999999999', $purpose->getCreditorId());
        $this->assertEquals('COBADEFFXXX', $purpose->getPayerBlz());
        $this->assertEquals('DE89370400440532013001', $purpose->getPayerAccount());
        $this->assertEquals('Max Mustermann', $purpose->getPayerName1());
        $this->assertEquals('GmbH', $purpose->getPayerName2());
        $this->assertEquals(TextKeyExtension::SEPA_CREDIT, $purpose->getTextKeyExt());
    }

    public function testFromStringWithSepaKeywords(): void {
        // SEPA keywords in purpose lines
        $lines = [
            '166SEPA-Lastschrift',
            '?00FOLGELASTSCHRIFT',
            '?20EREF+ORDER12345',
            '?21MREF+MANDATE67890',
            '?22CRED+DE98ZZZ09999999999',
            '?23SVWZ+Mitgliedsbeitrag',
        ];

        $purpose = Purpose::fromRawLines($lines);

        $this->assertEquals('ORDER12345', $purpose->getEndToEndReference());
        $this->assertEquals('MANDATE67890', $purpose->getMandateReference());
        $this->assertEquals('DE98ZZZ09999999999', $purpose->getCreditorId());
        $this->assertStringContainsString('Mitgliedsbeitrag', $purpose->getRemittanceInfo());
    }

    public function testFromStringWithSwiftKeywords(): void {
        // SWIFT format with /XXX/value/ patterns
        $raw = "/EREF/ORDER-2025-0001//MREF/MANDATE-001//CRED/DE98ZZZ09999999999//REMI/Gehalt Januar 2025//BENM/NAME/Max Mustermann/";

        $purpose = Purpose::fromString($raw);

        $this->assertEquals('ORDER-2025-0001', $purpose->getEndToEndReference());
        $this->assertEquals('MANDATE-001', $purpose->getMandateReference());
        $this->assertEquals('DE98ZZZ09999999999', $purpose->getCreditorId());
        $this->assertEquals('Gehalt Januar 2025', $purpose->getRemittanceInfo());
        $this->assertEquals('Max Mustermann', $purpose->getBeneficiaryName());
    }

    public function testSwiftKeywordsParsing(): void {
        // All SWIFT keywords
        $raw = "/EREF/E2E123//PREF/PI456//IREF/IN789//MREF/MR101//CRED/CR202//REMI/Remittance Info//ORDP/NAME/Ordering Party//ULTD/NAME/Ultimate Debtor//OCMT/EUR100,50//CHGS/EUR5,00//EXCH/1,25//PURP/CD/SALA//RTRN/AM05//TR/TR999//VACC/VA888//NBTR/5/";

        $purpose = Purpose::fromString($raw);

        $this->assertEquals('E2E123', $purpose->getEndToEndReference());
        $this->assertEquals('PI456', $purpose->getPaymentInfoId());
        $this->assertEquals('IN789', $purpose->getInstructionId());
        $this->assertEquals('MR101', $purpose->getMandateReference());
        $this->assertEquals('CR202', $purpose->getCreditorId());
        $this->assertEquals('Remittance Info', $purpose->getRemittanceInfo());
        $this->assertEquals('Ordering Party', $purpose->getOrderingPartyName());
        $this->assertEquals('Ultimate Debtor', $purpose->getUltimateDebtorName());
        $this->assertEquals('EUR100,50', $purpose->getOriginalAmount());
        $this->assertEquals('EUR5,00', $purpose->getCharges());
        $this->assertEquals('1,25', $purpose->getExchangeRate());
        $this->assertEquals(PurposeCode::SALA, $purpose->getPurposeCode());
        $this->assertEquals('AM05', $purpose->getReturnReason());
        $this->assertEquals('TR999', $purpose->getTransactionReference());
        $this->assertEquals('VA888', $purpose->getVirtualAccount());
        $this->assertEquals('5', $purpose->getNumberOfTransactions());
    }

    public function testPlainTextPurpose(): void {
        // Plain text without any keywords
        $raw = "Rechnung 12345 vom 01.01.2025";

        $purpose = Purpose::fromString($raw);

        $this->assertNull($purpose->getGvcCode());
        $this->assertNull($purpose->getEndToEndReference());
        $this->assertEquals($raw, $purpose->getRawText());
    }

    public function testGetGvcCodeEnum(): void {
        $raw = "166?00SEPA-ÜBERWEISUNG?20SVWZ+Test";
        $purpose = Purpose::fromString($raw);

        $gvcEnum = $purpose->getGvcCode();
        $this->assertNotNull($gvcEnum);
        $this->assertEquals(GvcCode::SEPA_CREDIT_TRANSFER, $gvcEnum);
        $this->assertTrue($gvcEnum->isSepa());
        $this->assertTrue($gvcEnum->isCredit());
    }

    public function testGetGvcCodeEnumUnknown(): void {
        // Unknown GVC code should return null
        $lines = ['999'];
        $purpose = Purpose::fromRawLines($lines);

        // 999 ist kein bekannter GVC-Code, also null
        $this->assertNull($purpose->getGvcCode());
    }

    public function testGetTextKeyExtEnum(): void {
        $raw = "166?00SEPA?34997";
        $purpose = Purpose::fromString($raw);

        $textKeyExt = $purpose->getTextKeyExt();
        $this->assertNotNull($textKeyExt);
        $this->assertEquals(TextKeyExtension::SEPA_CREDIT, $textKeyExt);
        $this->assertTrue($textKeyExt->isSepa());
    }

    public function testGetPurposeCodeEnum(): void {
        $raw = "/PURP/CD/SALA/";
        $purpose = Purpose::fromString($raw);

        $purposeCode = $purpose->getPurposeCode();
        $this->assertNotNull($purposeCode);
        $this->assertEquals(PurposeCode::SALA, $purposeCode);
        $this->assertEquals('salary', $purposeCode->category());
    }

    public function testIsSepaTransaction(): void {
        $raw = "166?00SEPA-ÜBERWEISUNG";
        $purpose = Purpose::fromString($raw);

        $this->assertTrue($purpose->isSepaTransaction());
        $this->assertTrue($purpose->isCreditTransaction());
        $this->assertFalse($purpose->isDebitTransaction());
        $this->assertFalse($purpose->isReturnTransaction());
    }

    public function testToSwiftKeywordString(): void {
        $purpose = new Purpose(
            endToEndReference: 'ORDER-123',
            mandateReference: 'MANDATE-456',
            remittanceInfo: 'Payment for invoice',
            purposeCode: PurposeCode::SALA
        );

        $swiftString = $purpose->toSwiftKeywordString();

        $this->assertStringContainsString('/EREF/ORDER-123/', $swiftString);
        $this->assertStringContainsString('/MREF/MANDATE-456/', $swiftString);
        $this->assertStringContainsString('/REMI/Payment for invoice/', $swiftString);
        $this->assertStringContainsString('/PURP/CD/SALA/', $swiftString);
    }

    public function testToSepaKeywordString(): void {
        $purpose = new Purpose(
            endToEndReference: 'ORDER-123',
            mandateReference: 'MANDATE-456',
            remittanceInfo: 'Payment for invoice'
        );

        $sepaString = $purpose->toSepaKeywordString();

        $this->assertStringContainsString('EREF+ORDER-123', $sepaString);
        $this->assertStringContainsString('MREF+MANDATE-456', $sepaString);
        $this->assertStringContainsString('SVWZ+Payment for invoice', $sepaString);
    }
}
