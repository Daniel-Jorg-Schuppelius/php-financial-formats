<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CamtParserTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Parsers\ISO20022;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type26\Document as Camt026Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type27\Document as Camt027Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type28\Document as Camt028Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\Document as Camt029Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type30\Document as Camt030Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type31\Document as Camt031Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type33\Document as Camt033Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type34\Document as Camt034Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type35\Document as Camt035Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type36\Document as Camt036Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type37\Document as Camt037Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type38\Document as Camt038Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type39\Document as Camt039Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type52\Document as Camt052Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53\Document as Camt053Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type54\Document as Camt054Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55\Document as Camt055Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type56\Document as Camt056Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type57\Document as Camt057Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type58\Document as Camt058Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type59\Document as Camt059Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type87\Document as Camt087Document;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\FinancialFormats\Parsers\ISO20022\CamtParser;
use Tests\Contracts\BaseTestCase;

/**
 * Tests für den generischen CamtParser.
 */
class CamtParserTest extends BaseTestCase {
    private string $samplesPath;

    protected function setUp(): void {
        parent::setUp();
        $this->samplesPath = dirname(__DIR__, 3) . '/.samples/Banking/CAMT/';
    }

    // ============================================================
    // CAMT.052 Tests
    // ============================================================

    public function testParseCamt052Bareinzahlung(): void {
        $file = $this->samplesPath . '01_EBICS_camt.052_Bareinzahlung_auf_Dot.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt052Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(Camt052Document::class, $document);
        $this->assertEquals(CamtType::CAMT052, $document->getCamtType());
        $this->assertEquals('DE00IBANdesDotationskontos', $document->getAccountIdentifier());
        $this->assertEquals('EUR', $document->getCurrency()->value);

        // Opening Balance
        $openingBalance = $document->getOpeningBalance();
        $this->assertNotNull($openingBalance);
        $this->assertEquals(0.0, $openingBalance->getAmount());
        $this->assertTrue($openingBalance->isCredit());

        // Closing Balance
        $closingBalance = $document->getClosingBalance();
        $this->assertNotNull($closingBalance);
        $this->assertEquals(100000.0, $closingBalance->getAmount());
        $this->assertTrue($closingBalance->isCredit());

        // Entries
        $this->assertGreaterThan(0, $document->countEntries());

        /** @var \CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type52\Transaction $firstEntry */
        $firstEntry = $document->getEntries()[0];
        $this->assertEquals(100000.0, $firstEntry->getAmount());
        $this->assertTrue($firstEntry->isCredit());
        $this->assertEquals(\CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionDomain::PMNT, $firstEntry->getDomainCode());
        $this->assertEquals(\CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionFamily::CNTR, $firstEntry->getFamilyCode());
        $this->assertEquals(\CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionSubFamily::CDPT, $firstEntry->getSubFamilyCode());
    }

    public function testParseCamt052Barscheckauszahlung(): void {
        $file = $this->samplesPath . '02_EBICS_camt.052_Barscheckauszahlung_vom_Dot.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt052Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(Camt052Document::class, $document);
        $this->assertGreaterThan(0, $document->countEntries());

        // Barscheckauszahlung sollte DBIT sein
        $entries = $document->getEntries();
        $hasDebit = false;
        foreach ($entries as $entry) {
            if ($entry->isDebit()) {
                $hasDebit = true;
                break;
            }
        }
        $this->assertTrue($hasDebit, 'Barscheckauszahlung sollte mindestens eine Soll-Buchung haben');
    }

    public function testParseCamt052DetectsType(): void {
        $file = $this->samplesPath . '01_EBICS_camt.052_Bareinzahlung_auf_Dot.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT052, $type);
        $this->assertEquals('BkToCstmrAcctRpt', $type->getRootElement());
        $this->assertEquals('Rpt', $type->getStatementElement());
    }

    // ============================================================
    // CAMT.053 Tests
    // ============================================================

    public function testParseCamt053Kontoauszug(): void {
        $file = $this->samplesPath . '11_EBICS_camt.053_Kontoauszug_mit_allen_Umsätzen.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt053Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(Camt053Document::class, $document);
        $this->assertEquals(CamtType::CAMT053, $document->getCamtType());
        $this->assertEquals('DE00IBANdesDotationskontos', $document->getAccountIban());

        // Mehrere Transaktionen erwartet
        $this->assertGreaterThan(5, $document->countEntries());

        // Prüfe Credits und Debits
        $this->assertGreaterThan(0, $document->getTotalCredits());
        $this->assertGreaterThan(0, $document->getTotalDebits());
    }

    public function testParseCamt053RtgsDca(): void {
        $file = $this->samplesPath . '4. camt.053-Beispieldatei RTGS DCA.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt053Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(Camt053Document::class, $document);

        // Opening Balance prüfen
        $openingBalance = $document->getOpeningBalance();
        $this->assertNotNull($openingBalance);
        $this->assertEquals(5368506.70, $openingBalance->getAmount());

        // Closing Balance prüfen
        $closingBalance = $document->getClosingBalance();
        $this->assertNotNull($closingBalance);
        $this->assertEquals(5368206.70, $closingBalance->getAmount());
    }

    public function testParseCamt053SubAccount(): void {
        $file = $this->samplesPath . '5. camt.053-Beispieldatei Sub-Account.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt053Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(Camt053Document::class, $document);
        $this->assertEquals(11, $document->countEntries());
    }

    // ============================================================
    // CAMT.054 Tests
    // ============================================================

    public function testParseCamt054LiquidityTransferOrder(): void {
        $file = $this->samplesPath . '1. camt.054- Beispieldatei liquidity transfer order.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt054Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(Camt054Document::class, $document);
        $this->assertEquals(CamtType::CAMT054, $document->getCamtType());

        $this->assertGreaterThan(0, $document->countEntries());

        /** @var \CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type54\Transaction $firstEntry */
        $firstEntry = $document->getEntries()[0];
        $this->assertEquals(258808.98, $firstEntry->getAmount());
        $this->assertTrue($firstEntry->isDebit());
        $this->assertEquals('LIQT', $firstEntry->getBankTransactionCode());

        // Referenzen prüfen
        $this->assertNotNull($firstEntry->getInstructionId());
        $this->assertNotNull($firstEntry->getEndToEndId());

        // Agent BICs prüfen
        $this->assertEquals('ZYBUDEFFSEK', $firstEntry->getInstructingAgentBic());
        $this->assertEquals('MARKDEFFSCL', $firstEntry->getDebtorAgentBic());
    }

    public function testParseCamt054Booking(): void {
        $file = $this->samplesPath . '2. camt.054- Beispieldatei booking.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt054Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(Camt054Document::class, $document);
        $this->assertGreaterThan(0, $document->countEntries());
    }

    public function testParseCamt054DetectsType(): void {
        $file = $this->samplesPath . '1. camt.054- Beispieldatei liquidity transfer order.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT054, $type);
        $this->assertEquals('BkToCstmrDbtCdtNtfctn', $type->getRootElement());
        $this->assertEquals('Ntfctn', $type->getStatementElement());
    }

    // ============================================================
    // CamtType Enum Tests
    // ============================================================

    public function testCamtTypeDescriptions(): void {
        $this->assertEquals('Intraday account movement information', CamtType::CAMT052->getDescription());
        $this->assertEquals('Daily account statement', CamtType::CAMT053->getDescription());
        $this->assertEquals('Debit/Credit Notification', CamtType::CAMT054->getDescription());
    }

    public function testCamtTypeMessageNames(): void {
        $this->assertEquals('BankToCustomerAccountReport', CamtType::CAMT052->getMessageName());
        $this->assertEquals('BankToCustomerStatement', CamtType::CAMT053->getMessageName());
        $this->assertEquals('BankToCustomerDebitCreditNotification', CamtType::CAMT054->getMessageName());
    }

    public function testCamtTypeNamespaces(): void {
        $namespaces052 = CamtType::CAMT052->getNamespaces();
        $this->assertArrayHasKey('02', $namespaces052);
        $this->assertArrayHasKey('08', $namespaces052);
        $this->assertEquals('urn:iso:std:iso:20022:tech:xsd:camt.052.001.08', $namespaces052['08']);

        $namespaces053 = CamtType::CAMT053->getNamespaces();
        $this->assertArrayHasKey('02', $namespaces053);
        $this->assertArrayHasKey('08', $namespaces053);
        $this->assertEquals('urn:iso:std:iso:20022:tech:xsd:camt.053.001.02', $namespaces053['02']);
    }

    // ============================================================
    // XML Generation Tests
    // ============================================================

    public function testCamt052ToXml(): void {
        $file = $this->samplesPath . '01_EBICS_camt.052_Bareinzahlung_auf_Dot.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt052Document $document */
        $document = CamtParser::parseFile($file);
        $this->assertInstanceOf(Camt052Document::class, $document);

        $xml = $document->toXml();

        $this->assertStringContainsString('<?xml version="1.0"', $xml);
        $this->assertStringContainsString('BkToCstmrAcctRpt', $xml);
        $this->assertStringContainsString('Rpt', $xml);
        $this->assertStringContainsString('camt.052', $xml);
    }

    public function testCamt054ToXml(): void {
        $file = $this->samplesPath . '1. camt.054- Beispieldatei liquidity transfer order.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt054Document $document */
        $document = CamtParser::parseFile($file);
        $this->assertInstanceOf(Camt054Document::class, $document);

        $xml = $document->toXml();

        $this->assertStringContainsString('<?xml version="1.0"', $xml);
        $this->assertStringContainsString('BkToCstmrDbtCdtNtfctn', $xml);
        $this->assertStringContainsString('Ntfctn', $xml);
        $this->assertStringContainsString('camt.054', $xml);
    }

    // ============================================================
    // Edge Cases & Error Handling
    // ============================================================

    public function testParseInvalidXmlThrowsException(): void {
        $this->expectException(\RuntimeException::class);
        CamtParser::parse('not valid xml');
    }

    public function testParseUnknownTypeThrowsException(): void {
        $xml = '<?xml version="1.0"?><Document>
    <Unknown />
</Document>';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unbekannter CAMT-Dokumenttyp');
        CamtParser::parse($xml);
    }

    public function testParseFileNotFoundThrowsException(): void {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Datei nicht gefunden');
        CamtParser::parseFile('/nonexistent/path/file.xml');
    }

    // ============================================================
    // XSD-Validierung Tests
    // ============================================================

    public function testParseWithValidation(): void {
        $file = $this->samplesPath . '01_EBICS_camt.052_Bareinzahlung_auf_Dot.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

// Ohne Validierung (Standard)
        /** @var Camt052Document $document */
        $document = CamtParser::parseFile($file);
        $this->assertInstanceOf(Camt052Document::class, $document);

// Mit Validierung
        /** @var Camt052Document $document */
        $document = CamtParser::parseFile($file, validate: true);
        $this->assertInstanceOf(Camt052Document::class, $document);
    }

    public function testParseWithValidationFailsOnInvalidXml(): void {
        // Erstelle ein XML das zwar CAMT-ähnlich aussieht aber nicht valide ist
        $invalidXml = '
<?xml version="1.0" encoding="UTF-8"?>
<Document xmlns="urn:iso:std:iso:20022:tech:xsd:camt.052.001.02">
    <BkToCstmrAcctRpt>
        <GrpHdr>
            <MsgId>INVALID</MsgId>
        </GrpHdr>
    </BkToCstmrAcctRpt>
</Document>';

        // Mit Validierung sollte eine Exception geworfen werden
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('XSD-Validierung fehlgeschlagen');
        CamtParser::parse($invalidXml, validate: true);
    }

    // ============================================================
    // Roundtrip Tests
    // ============================================================

    public function testCamt052Roundtrip(): void {
        $file = $this->samplesPath . '01_EBICS_camt.052_Bareinzahlung_auf_Dot.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt052Document $document1 */
        $document1 = CamtParser::parseFile($file);
        $xml = $document1->toXml();
        $document2 = CamtParser::parseCamt052($xml);

        $this->assertEquals($document1->getId(), $document2->getId());
        $this->assertEquals($document1->getAccountIdentifier(), $document2->getAccountIdentifier());
        $this->assertEquals($document1->countEntries(), $document2->countEntries());
    }

    // ============================================================
    // CAMT.055 Tests (Customer Payment Cancellation Request)
    // ============================================================

    public function testParseCamt055(): void {
        $file = $this->samplesPath . 'sample_camt055.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt055Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55\Document::class, $document);
        $this->assertEquals(CamtType::CAMT055, $document->getCamtType());
        $this->assertEquals('CXLREQ-001', $document->getMessageId());
        $this->assertEquals('Test Unternehmen GmbH', $document->getInitiatingPartyName());
        $this->assertEquals('DE123456789', $document->getInitiatingPartyId());
        $this->assertEquals('CASE-2025-001', $document->getCaseId());
        $this->assertEquals('2', $document->getNumberOfTransactions());
        $this->assertEquals('5000.00', $document->getControlSum());
    }

    public function testParseCamt055UnderlyingTransactions(): void {
        $file = $this->samplesPath . 'sample_camt055.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt055Document $document */
        $document = CamtParser::parseFile($file);

        $underlyingTransactions = $document->getUnderlyingTransactions();
        $this->assertCount(1, $underlyingTransactions);

        $underlying = $underlyingTransactions[0];
        $this->assertEquals('PAIN001-ORIG-001', $underlying->getOriginalGroupInformationMessageId());
        $this->assertEquals('pain.001.001.09', $underlying->getOriginalGroupInformationMessageNameId());
        $this->assertEquals(2, $underlying->getOriginalNumberOfTransactions());
    }

    public function testParseCamt055CancellationRequests(): void {
        $file = $this->samplesPath . 'sample_camt055.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt055Document $document */
        $document = CamtParser::parseFile($file);

        $allRequests = $document->getAllCancellationRequests();
        $this->assertCount(2, $allRequests);

        $firstRequest = $allRequests[0];
        $this->assertEquals('CXL-TX-001', $firstRequest->getCancellationId());
        $this->assertEquals('DUPL', $firstRequest->getCancellationReasonCode());
        $this->assertEquals('Doppelte Zahlung erkannt', $firstRequest->getCancellationReasonAdditionalInfo());
        $this->assertEquals('E2E-001', $firstRequest->getOriginalEndToEndId());
        $this->assertEquals('2500.00', $firstRequest->getOriginalAmount());
        $this->assertEquals('EUR', $firstRequest->getOriginalCurrency()->value);
        $this->assertEquals('Test Unternehmen GmbH', $firstRequest->getDebtorName());
        $this->assertEquals('DE89370400440532013000', $firstRequest->getDebtorIban());
        $this->assertEquals('Lieferant AG', $firstRequest->getCreditorName());
        $this->assertEquals('Rechnung 2025-123', $firstRequest->getRemittanceInformation());

        $secondRequest = $allRequests[1];
        $this->assertEquals('CXL-TX-002', $secondRequest->getCancellationId());
        $this->assertEquals('CUST', $secondRequest->getCancellationReasonCode());
    }

    public function testCamt055ToXml(): void {
        $document = new \CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55\Document(
            messageId: 'TEST-CXL-001',
            creationDateTime: '2025-07-27T10:00:00+02:00',
            numberOfTransactions: '1',
            controlSum: '100.00',
            initiatingPartyName: 'Test AG'
        );

        $underlying = new \CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55\UnderlyingTransaction(
            originalGroupInformationMessageId: 'ORIG-MSG-001'
        );

        $pmtInf = new \CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55\OriginalPaymentInformation(
            originalPaymentInformationId: 'PMTINF-001'
        );

        $txInfo = new \CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55\PaymentCancellationRequest(
            cancellationId: 'CXL-001',
            originalEndToEndId: 'E2E-001',
            originalAmount: '100.00',
            originalCurrency: 'EUR',
            cancellationReasonCode: 'DUPL'
        );

        $pmtInf->addTransactionInformation($txInfo);
        $underlying->addOriginalPaymentInformationAndCancellation($pmtInf);
        $document->addUnderlyingTransaction($underlying);

        $xml = $document->toXml();

        $this->assertStringContainsString('<CstmrPmtCxlReq>', $xml);
        $this->assertStringContainsString('<Id>TEST-CXL-001</Id>', $xml);
        $this->assertStringContainsString('<CxlId>CXL-001</CxlId>', $xml);
        $this->assertStringContainsString('<Cd>DUPL</Cd>', $xml);
    }

    // ============================================================
    // CAMT.056 Tests (FI To FI Payment Cancellation Request)
    // ============================================================

    public function testParseCamt056(): void {
        $file = $this->samplesPath . 'sample_camt056.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt056Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type56\Document::class, $document);
        $this->assertEquals(CamtType::CAMT056, $document->getCamtType());
        $this->assertEquals('FITOFICXL-001', $document->getMessageId());
        $this->assertEquals('COBADEFFXXX', $document->getInstructingAgentBic());
        $this->assertEquals('DEUTDEFFXXX', $document->getInstructedAgentBic());
        $this->assertEquals('CASE-FI-2025-001', $document->getCaseId());
        $this->assertEquals('1', $document->getNumberOfTransactions());
        $this->assertEquals('10000.00', $document->getControlSum());
    }

    public function testParseCamt056CancellationRequests(): void {
        $file = $this->samplesPath . 'sample_camt056.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt056Document $document */
        $document = CamtParser::parseFile($file);

        $allRequests = $document->getAllCancellationRequests();
        $this->assertCount(1, $allRequests);

        $request = $allRequests[0];
        $this->assertEquals('FRAD', $request->getCancellationReasonCode());
        $this->assertEquals('Betrugsverdacht', $request->getCancellationReasonAdditionalInfo());
        $this->assertEquals('E2E-FI-001', $request->getOriginalEndToEndId());
        $this->assertEquals('TX-FI-001', $request->getOriginalTransactionId());
        $this->assertEquals('10000.00', $request->getOriginalInterbankSettlementAmount());
        $this->assertEquals('EUR', $request->getOriginalCurrency()->value);
        $this->assertEquals('Verdächtiger Absender', $request->getDebtorName());
        $this->assertEquals('DE89370400440532013000', $request->getDebtorIban());
        $this->assertEquals('COBADEFFXXX', $request->getDebtorBic());
        $this->assertEquals('Empfänger GmbH', $request->getCreditorName());
    }

    public function testCamt056ToXml(): void {
        $document = new \CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type56\Document(
            messageId: 'TEST-FITOFI-001',
            creationDateTime: '2025-07-27T11:00:00+02:00',
            numberOfTransactions: '1',
            instructingAgentBic: 'COBADEFFXXX',
            instructedAgentBic: 'DEUTDEFFXXX'
        );

        $underlying = new \CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type56\UnderlyingTransaction(
            originalGroupInformationMessageId: 'PACS008-001'
        );

        $txInfo = new \CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type56\PaymentCancellationRequest(
            originalEndToEndId: 'E2E-001',
            originalTransactionId: 'TX-001',
            originalInterbankSettlementAmount: '5000.00',
            originalCurrency: 'EUR',
            cancellationReasonCode: 'FRAD'
        );

        $underlying->addTransactionInformation($txInfo);
        $document->addUnderlyingTransaction($underlying);

        $xml = $document->toXml();

        $this->assertStringContainsString('<FIToFIPmtCxlReq>', $xml);
        $this->assertStringContainsString('<Id>TEST-FITOFI-001</Id>', $xml);
        $this->assertStringContainsString('<BICFI>COBADEFFXXX</BICFI>', $xml);
        $this->assertStringContainsString('<Cd>FRAD</Cd>', $xml);
    }

    // ============================================================
    // CAMT.029 Tests (Resolution of Investigation)
    // ============================================================

    public function testParseCamt029(): void {
        $file = $this->samplesPath . 'sample_camt029.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt029Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\Document::class, $document);
        $this->assertEquals(CamtType::CAMT029, $document->getCamtType());
        $this->assertEquals('RSLTN-001', $document->getAssignmentId());
        $this->assertEquals('DEUTDEFFXXX', $document->getAssignerAgentBic());
        $this->assertEquals('COBADEFFXXX', $document->getAssigneeAgentBic());
        $this->assertEquals('CASE-FI-2025-001', $document->getCaseId());
        $this->assertEquals('CNCL', $document->getInvestigationStatus());
        $this->assertTrue($document->isAccepted());
        $this->assertTrue($document->isResolved());
    }

    public function testParseCamt029CancellationDetails(): void {
        $file = $this->samplesPath . 'sample_camt029.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt029Document $document */
        $document = CamtParser::parseFile($file);

        $cancellationDetails = $document->getCancellationDetails();
        $this->assertCount(1, $cancellationDetails);

        $details = $cancellationDetails[0];
        $grpInfAndSts = $details->getOriginalGroupInformationAndStatus();
        $this->assertNotNull($grpInfAndSts);
        $this->assertEquals('PACS008-ORIG-001', $grpInfAndSts->getOriginalMessageId());
        $this->assertEquals('ACCR', $grpInfAndSts->getGroupCancellationStatus());
        $this->assertTrue($grpInfAndSts->isFullyAccepted());
    }

    public function testParseCamt029TransactionStatus(): void {
        $file = $this->samplesPath . 'sample_camt029.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt029Document $document */
        $document = CamtParser::parseFile($file);

        $allStatus = $document->getAllTransactionStatus();
        $this->assertCount(1, $allStatus);

        $txStatus = $allStatus[0];
        $this->assertEquals('CXLSTS-001', $txStatus->getCancellationStatusId());
        $this->assertEquals('E2E-FI-001', $txStatus->getOriginalEndToEndId());
        $this->assertEquals('TX-FI-001', $txStatus->getOriginalTransactionId());
        $this->assertEquals('CNCL', $txStatus->getTransactionCancellationStatus());
        $this->assertTrue($txStatus->isAccepted());
        $this->assertEquals('10000.00', $txStatus->getOriginalAmount());
        $this->assertEquals('EUR', $txStatus->getOriginalCurrency()->value);
        $this->assertEquals('Verdächtiger Absender', $txStatus->getDebtorName());
        $this->assertEquals('DE89370400440532013000', $txStatus->getDebtorIban());
    }

    public function testParseCamt029CancellationStatusReason(): void {
        $file = $this->samplesPath . 'sample_camt029.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt029Document $document */
        $document = CamtParser::parseFile($file);
        $txStatus = $document->getAllTransactionStatus()[0];

        $statusReasons = $txStatus->getCancellationStatusReasonInformation();
        $this->assertCount(1, $statusReasons);

        $reason = $statusReasons[0];
        $this->assertEquals('FRAD', $reason->getStatusCode());
        $this->assertEquals('Zahlung erfolgreich storniert wegen Betrugsverdacht', $reason->getAdditionalInformation());
    }

    public function testCamt029ToXml(): void {
        $document = new \CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\Document(
            assignmentId: 'TEST-RSLTN-001',
            creationDateTime: '2025-07-27T15:00:00+02:00',
            assignerAgentBic: 'DEUTDEFFXXX',
            assigneeAgentBic: 'COBADEFFXXX',
            investigationStatus: 'CNCL'
        );

        $grpInfAndSts = new \CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\OriginalGroupInformationAndStatus(
            originalMessageId: 'ORIG-MSG-001',
            groupCancellationStatus: 'ACCR'
        );

        $txInfAndSts = new \CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\TransactionInformationAndStatus(
            cancellationStatusId: 'CXLSTS-001',
            originalEndToEndId: 'E2E-001',
            transactionCancellationStatus: 'CNCL'
        );

        $cxlStatus = new \CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\CancellationStatus(
            statusCode: 'FRAD',
            additionalInformation: 'Betrugsverdacht bestätigt'
        );
        $txInfAndSts->addCancellationStatusReasonInformation($cxlStatus);

        $details = new \CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\CancellationDetails($grpInfAndSts);
        $details->addTransactionInformationAndStatus($txInfAndSts);
        $document->addCancellationDetails($details);

        $xml = $document->toXml();

        $this->assertStringContainsString('<RsltnOfInvstgtn>', $xml);
        $this->assertStringContainsString('<Id>TEST-RSLTN-001</Id>', $xml);
        $this->assertStringContainsString('<Conf>CNCL</Conf>', $xml);
        $this->assertStringContainsString('<GrpCxlSts>ACCR</GrpCxlSts>', $xml);
        $this->assertStringContainsString('<TxCxlSts>CNCL</TxCxlSts>', $xml);
        $this->assertStringContainsString('<Cd>FRAD</Cd>', $xml);
    }

    // ============================================================
    // Type Detection Tests for new formats
    // ============================================================

    public function testDetectTypeCamt055(): void {
        $file = $this->samplesPath . 'sample_camt055.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT055, $type);
        $this->assertTrue($type->isCancellationType());
        $this->assertFalse($type->isStatementType());
    }

    public function testDetectTypeCamt056(): void {
        $file = $this->samplesPath . 'sample_camt056.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT056, $type);
        $this->assertTrue($type->isCancellationType());
    }

    public function testDetectTypeCamt029(): void {
        $file = $this->samplesPath . 'sample_camt029.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT029, $type);
        $this->assertTrue($type->isCancellationType());
    }

    // ============================================================
    // CAMT.026 Tests (Unable to Apply)
    // ============================================================

    public function testParseCamt026(): void {
        $file = $this->samplesPath . 'sample_camt026.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt026Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type26\Document::class, $document);
        $this->assertEquals(CamtType::CAMT026, $document->getCamtType());
        $this->assertEquals('ASGN-2025-UTA-001', $document->getAssignmentId());
        $this->assertEquals('COBADEFFXXX', $document->getAssignerAgentBic());
        $this->assertEquals('DEUTDEFFXXX', $document->getAssigneeAgentBic());
        $this->assertEquals('CASE-UTA-2025-001', $document->getCaseId());
        $this->assertEquals('Commerzbank AG', $document->getCaseCreator());
    }

    public function testParseCamt026UnderlyingTransaction(): void {
        $file = $this->samplesPath . 'sample_camt026.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt026Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertEquals('MSG-2025-001', $document->getOriginalMessageId());
        $this->assertEquals('pain.001.001.03', $document->getOriginalMessageNameId());
        $this->assertEquals('E2E-2025-001', $document->getOriginalEndToEndId());
        $this->assertEquals('TX-2025-001', $document->getOriginalTransactionId());
        $this->assertEquals('1500.00', $document->getOriginalInterbankSettlementAmount());
        $this->assertEquals(\CommonToolkit\Enums\CurrencyCode::Euro, $document->getOriginalCurrency());
    }

    public function testParseCamt026UnableToApplyReasons(): void {
        $file = $this->samplesPath . 'sample_camt026.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt026Document $document */
        $document = CamtParser::parseFile($file);
        $reasons = $document->getUnableToApplyReasons();

        $this->assertCount(2, $reasons);

        // First reason - missing information
        $this->assertTrue($reasons[0]->isMissingInformation());
        $this->assertEquals('MSSI', $reasons[0]->getMissingInformationType());

        // Second reason - incorrect information
        $this->assertTrue($reasons[1]->isIncorrectInformation());
        $this->assertEquals('INAM', $reasons[1]->getIncorrectInformationType());
    }

    public function testDetectTypeCamt026(): void {
        $file = $this->samplesPath . 'sample_camt026.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT026, $type);
        $this->assertTrue($type->isInvestigationType());
        $this->assertFalse($type->isCancellationType());
    }

    // ============================================================
    // CAMT.027 Tests (Claim Non Receipt)
    // ============================================================

    public function testParseCamt027(): void {
        $file = $this->samplesPath . 'sample_camt027.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt027Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type27\Document::class, $document);
        $this->assertEquals(CamtType::CAMT027, $document->getCamtType());
        $this->assertEquals('ASGN-2025-CNR-001', $document->getAssignmentId());
        $this->assertEquals('DEUTDEFFXXX', $document->getAssignerAgentBic());
        $this->assertEquals('COBADEFFXXX', $document->getAssigneeAgentBic());
        $this->assertEquals('CASE-CNR-2025-001', $document->getCaseId());
    }

    public function testParseCamt027MissingCoverIndicator(): void {
        $file = $this->samplesPath . 'sample_camt027.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt027Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertTrue($document->isMissingCover());
        $this->assertTrue($document->getMissingCoverIndicator());
        $this->assertNotNull($document->getCoverDate());
        $this->assertEquals('2025-06-25', $document->getCoverDate()->format('Y-m-d'));
    }

    public function testParseCamt027UnderlyingTransaction(): void {
        $file = $this->samplesPath . 'sample_camt027.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt027Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertEquals('MSG-2025-002', $document->getOriginalMessageId());
        $this->assertEquals('TX-2025-002', $document->getOriginalTransactionId());
        $this->assertEquals('2500.00', $document->getOriginalInterbankSettlementAmount());
    }

    public function testDetectTypeCamt027(): void {
        $file = $this->samplesPath . 'sample_camt027.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT027, $type);
        $this->assertTrue($type->isInvestigationType());
    }

    // ============================================================
    // CAMT.028 Tests (Additional Payment Information)
    // ============================================================

    public function testParseCamt028(): void {
        $file = $this->samplesPath . 'sample_camt028.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt028Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type28\Document::class, $document);
        $this->assertEquals(CamtType::CAMT028, $document->getCamtType());
        $this->assertEquals('ASGN-2025-API-001', $document->getAssignmentId());
        $this->assertEquals('COBADEFFXXX', $document->getAssignerAgentBic());
        $this->assertEquals('CASE-API-2025-001', $document->getCaseId());
    }

    public function testParseCamt028AdditionalInformation(): void {
        $file = $this->samplesPath . 'sample_camt028.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt028Document $document */
        $document = CamtParser::parseFile($file);
        $additionalInfo = $document->getAdditionalInformation();

        $this->assertCount(2, $additionalInfo);
        $this->assertStringContainsString(
            'Rechnung Nr. 2025-12345',
            $additionalInfo[0]->getRemittanceInformation()
        );
        $this->assertStringContainsString('Kunde: Max Mustermann', $additionalInfo[1]->getRemittanceInformation());
    }

    public function testParseCamt028UnderlyingTransaction(): void {
        $file = $this->samplesPath . 'sample_camt028.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt028Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertEquals('TX-2025-003', $document->getOriginalTransactionId());
        $this->assertEquals('3500.00', $document->getOriginalInterbankSettlementAmount());
    }

    public function testDetectTypeCamt028(): void {
        $file = $this->samplesPath . 'sample_camt028.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT028, $type);
        $this->assertTrue($type->isInvestigationType());
    }

    // ============================================================
    // CAMT.087 Tests (Request to Modify Payment)
    // ============================================================

    public function testParseCamt087(): void {
        $file = $this->samplesPath . 'sample_camt087.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt087Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type87\Document::class, $document);
        $this->assertEquals(CamtType::CAMT087, $document->getCamtType());
        $this->assertEquals('ASGN-2025-RMP-001', $document->getAssignmentId());
        $this->assertEquals('DEUTDEFFXXX', $document->getAssignerAgentBic());
        $this->assertEquals('CASE-RMP-2025-001', $document->getCaseId());
    }

    public function testParseCamt087ModificationRequest(): void {
        $file = $this->samplesPath . 'sample_camt087.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt087Document $document */
        $document = CamtParser::parseFile($file);
        $modRequests = $document->getModificationRequests();

        $this->assertCount(1, $modRequests);

        $modRequest = $modRequests[0];
        $this->assertTrue($modRequest->hasAmountModification());
        $this->assertEquals('4500.00', $modRequest->getRequestedSettlementAmount());
        $this->assertEquals(\CommonToolkit\Enums\CurrencyCode::Euro, $modRequest->getRequestedCurrency());
        $this->assertTrue($modRequest->hasCreditorModification());
        $this->assertEquals('Musterfirma GmbH (korrigiert)', $modRequest->getCreditorName());
        $this->assertEquals('DE89370400440532013001', $modRequest->getCreditorAccount());
        $this->assertStringContainsString('Rechnung 2025-99999', $modRequest->getRemittanceInformation());
    }

    public function testParseCamt087UnderlyingTransaction(): void {
        $file = $this->samplesPath . 'sample_camt087.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt087Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertEquals('TX-2025-004', $document->getOriginalTransactionId());
        $this->assertEquals('5000.00', $document->getOriginalInterbankSettlementAmount());
        $this->assertEquals(\CommonToolkit\Enums\CurrencyCode::Euro, $document->getOriginalCurrency());
    }

    public function testDetectTypeCamt087(): void {
        $file = $this->samplesPath . 'sample_camt087.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT087, $type);
        $this->assertTrue($type->isInvestigationType());
        $this->assertFalse($type->isCancellationType());
    }

    // ============================================================
    // CAMT.026/027/028/087 XML Generation Tests
    // ============================================================

    public function testCamt026ToXml(): void {
        $file = $this->samplesPath . 'sample_camt026.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt026Document $document */
        $document = CamtParser::parseFile($file);
        $xml = $document->toXml();

        $this->assertStringContainsString('UblToApply', $xml);
        $this->assertStringContainsString('ASGN-2025-UTA-001', $xml);
        $this->assertStringContainsString('COBADEFFXXX', $xml);
        $this->assertStringContainsString('camt.026', $xml);
    }

    public function testCamt027ToXml(): void {
        $file = $this->samplesPath . 'sample_camt027.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt027Document $document */
        $document = CamtParser::parseFile($file);
        $xml = $document->toXml();

        $this->assertStringContainsString('ClmNonRcpt', $xml);
        $this->assertStringContainsString('ASGN-2025-CNR-001', $xml);
        $this->assertStringContainsString('MssngCoverInd', $xml);
        $this->assertStringContainsString('camt.027', $xml);
    }

    public function testCamt028ToXml(): void {
        $file = $this->samplesPath . 'sample_camt028.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt028Document $document */
        $document = CamtParser::parseFile($file);
        $xml = $document->toXml();

        $this->assertStringContainsString('AddtlPmtInf', $xml);
        $this->assertStringContainsString('ASGN-2025-API-001', $xml);
        $this->assertStringContainsString('camt.028', $xml);
    }

    public function testCamt087ToXml(): void {
        $file = $this->samplesPath . 'sample_camt087.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt087Document $document */
        $document = CamtParser::parseFile($file);
        $xml = $document->toXml();

        $this->assertStringContainsString('ReqToModfyPmt', $xml);
        $this->assertStringContainsString('ASGN-2025-RMP-001', $xml);
        $this->assertStringContainsString('4500.00', $xml);
        $this->assertStringContainsString('camt.087', $xml);
    }

    // ============================================================
    // CAMT.030 Tests
    // ============================================================

    public function testParseCamt030NotificationOfCaseAssignment(): void {
        $file = $this->samplesPath . 'sample_camt030.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt030Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type30\Document::class, $document);
        $this->assertEquals(CamtType::CAMT030, $document->getCamtType());
        $this->assertEquals('NTFCTN-2025-001', $document->getHeaderMessageId());
        $this->assertEquals('CASE-2025-ABC123', $document->getCaseId());
        $this->assertEquals('Musterbank AG', $document->getCaseCreator());
        $this->assertEquals('COBADEFFXXX', $document->getAssignerAgentBic());
        $this->assertEquals('DEUTDEFFXXX', $document->getAssigneeAgentBic());
        $this->assertStringContainsString('Weiterleitung zur Klärung', $document->getNotificationJustification());
    }

    public function testDetectTypeCamt030(): void {
        $file = $this->samplesPath . 'sample_camt030.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT030, $type);
        $this->assertTrue($type->isInvestigationType());
        $this->assertFalse($type->isNotificationType());
    }

    public function testCamt030ToXml(): void {
        $file = $this->samplesPath . 'sample_camt030.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt030Document $document */
        $document = CamtParser::parseFile($file);
        $xml = $document->toXml();

        $this->assertStringContainsString('NtfctnOfCaseAssgnmt', $xml);
        $this->assertStringContainsString('NTFCTN-2025-001', $xml);
        $this->assertStringContainsString('CASE-2025-ABC123', $xml);
        $this->assertStringContainsString('camt.030', $xml);
    }

    // ============================================================
    // CAMT.031 Tests
    // ============================================================

    public function testParseCamt031RejectInvestigation(): void {
        $file = $this->samplesPath . 'sample_camt031.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt031Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type31\Document::class, $document);
        $this->assertEquals(CamtType::CAMT031, $document->getCamtType());
        $this->assertEquals('RJCT-2025-001', $document->getAssignmentId());
        $this->assertEquals('CASE-2025-ABC123', $document->getCaseId());
        $this->assertEquals('DEUTDEFFXXX', $document->getAssignerAgentBic());
        $this->assertEquals('COBADEFFXXX', $document->getAssigneeAgentBic());
        $this->assertEquals('NOOR', $document->getRejectionReasonCode());
        $this->assertStringContainsString('Originaltransaktion', $document->getAdditionalInformation());
    }

    public function testDetectTypeCamt031(): void {
        $file = $this->samplesPath . 'sample_camt031.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT031, $type);
        $this->assertTrue($type->isInvestigationType());
    }

    public function testCamt031ToXml(): void {
        $file = $this->samplesPath . 'sample_camt031.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt031Document $document */
        $document = CamtParser::parseFile($file);
        $xml = $document->toXml();

        $this->assertStringContainsString('RjctInvstgtn', $xml);
        $this->assertStringContainsString('RJCT-2025-001', $xml);
        $this->assertStringContainsString('NOOR', $xml);
        $this->assertStringContainsString('camt.031', $xml);
    }

    // ============================================================
    // CAMT.033 Tests
    // ============================================================

    public function testParseCamt033RequestForDuplicate(): void {
        $file = $this->samplesPath . 'sample_camt033.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt033Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type33\Document::class, $document);
        $this->assertEquals(CamtType::CAMT033, $document->getCamtType());
        $this->assertEquals('DPLCT-2025-001', $document->getAssignmentId());
        $this->assertEquals('CASE-2025-XYZ789', $document->getCaseId());
        $this->assertEquals('Commerzbank AG', $document->getCaseCreator());
        $this->assertEquals('PAIN001-2025-001', $document->getOriginalMessageId());
        $this->assertEquals('pain.001.001.09', $document->getOriginalMessageNameId());
        $this->assertEquals('E2E-2025-001', $document->getOriginalEndToEndId());
        $this->assertEquals('TXN-2025-001', $document->getOriginalTransactionId());
        $this->assertEquals('15000.00', $document->getOriginalInterbankSettlementAmount());
        $this->assertEquals(\CommonToolkit\Enums\CurrencyCode::Euro, $document->getOriginalCurrency());
    }

    public function testDetectTypeCamt033(): void {
        $file = $this->samplesPath . 'sample_camt033.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT033, $type);
        $this->assertTrue($type->isInvestigationType());
    }

    public function testCamt033ToXml(): void {
        $file = $this->samplesPath . 'sample_camt033.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt033Document $document */
        $document = CamtParser::parseFile($file);
        $xml = $document->toXml();

        $this->assertStringContainsString('ReqForDplct', $xml);
        $this->assertStringContainsString('DPLCT-2025-001', $xml);
        $this->assertStringContainsString('PAIN001-2025-001', $xml);
        $this->assertStringContainsString('camt.033', $xml);
    }

    // ============================================================
    // CAMT.057 Tests
    // ============================================================

    public function testParseCamt057NotificationToReceive(): void {
        $file = $this->samplesPath . 'sample_camt057.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt057Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type57\Document::class, $document);
        $this->assertEquals(CamtType::CAMT057, $document->getCamtType());
        $this->assertEquals('NTFRCV-2025-001', $document->getGroupHeaderMessageId());
        $this->assertEquals('Hauptkasse GmbH', $document->getInitiatingPartyName());
        $this->assertEquals('COBADEFFXXX', $document->getMessageRecipientBic());

        // Check items
        $items = $document->getItems();
        $this->assertCount(2, $items);

        $item1 = $items[0];
        $this->assertEquals('NTF-001', $item1->getId());
        $this->assertEquals('50000.00', $item1->getAmount());
        $this->assertEquals(\CommonToolkit\Enums\CurrencyCode::Euro, $item1->getCurrency());
        $this->assertEquals('Lieferant ABC GmbH', $item1->getDebtorName());
        $this->assertEquals('DEUTDEFFXXX', $item1->getDebtorAgentBic());
        $this->assertStringContainsString('RE-2025-001', $item1->getRemittanceInformation());

        $item2 = $items[1];
        $this->assertEquals('NTF-002', $item2->getId());
        $this->assertEquals('25000.00', $item2->getAmount());
        $this->assertEquals('Kunde XYZ AG', $item2->getDebtorName());
    }

    public function testDetectTypeCamt057(): void {
        $file = $this->samplesPath . 'sample_camt057.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT057, $type);
        $this->assertTrue($type->isNotificationType());
        $this->assertFalse($type->isInvestigationType());
    }

    public function testCamt057ToXml(): void {
        $file = $this->samplesPath . 'sample_camt057.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt057Document $document */
        $document = CamtParser::parseFile($file);
        $xml = $document->toXml();

        $this->assertStringContainsString('NtfctnToRcv', $xml);
        $this->assertStringContainsString('NTFRCV-2025-001', $xml);
        $this->assertStringContainsString('NTF-001', $xml);
        $this->assertStringContainsString('50000.00', $xml);
        $this->assertStringContainsString('camt.057', $xml);
    }

    // ============================================================
    // CAMT.058 Tests
    // ============================================================

    public function testParseCamt058NotificationToReceiveCancellationAdvice(): void {
        $file = $this->samplesPath . 'sample_camt058.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt058Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type58\Document::class, $document);
        $this->assertEquals(CamtType::CAMT058, $document->getCamtType());
        $this->assertEquals('CXLADVC-2025-001', $document->getGroupHeaderMessageId());
        $this->assertEquals('Hauptkasse GmbH', $document->getInitiatingPartyName());
        $this->assertEquals('NTFRCV-2025-001', $document->getOriginalMessageId());
        $this->assertEquals('camt.057.001.08', $document->getOriginalMessageNameId());

        // Check cancellation items
        $items = $document->getItems();
        $this->assertCount(1, $items);

        $item = $items[0];
        $this->assertEquals('NTF-001', $item->getOriginalItemId());
        $this->assertEquals('CUST', $item->getCancellationReasonCode());
        $this->assertStringContainsString('Kundenwunsch', $item->getCancellationAdditionalInfo());
    }

    public function testDetectTypeCamt058(): void {
        $file = $this->samplesPath . 'sample_camt058.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT058, $type);
        $this->assertTrue($type->isNotificationType());
    }

    public function testCamt058ToXml(): void {
        $file = $this->samplesPath . 'sample_camt058.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt058Document $document */
        $document = CamtParser::parseFile($file);
        $xml = $document->toXml();

        $this->assertStringContainsString('NtfctnToRcvCxlAdvc', $xml);
        $this->assertStringContainsString('CXLADVC-2025-001', $xml);
        $this->assertStringContainsString('NTFRCV-2025-001', $xml);
        $this->assertStringContainsString('CUST', $xml);
        $this->assertStringContainsString('camt.058', $xml);
    }

    // ============================================================
    // CAMT.059 Tests
    // ============================================================

    public function testParseCamt059NotificationToReceiveStatusReport(): void {
        $file = $this->samplesPath . 'sample_camt059.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt059Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type59\Document::class, $document);
        $this->assertEquals(CamtType::CAMT059, $document->getCamtType());
        $this->assertEquals('STSRPT-2025-001', $document->getGroupHeaderMessageId());
        $this->assertEquals('Commerzbank AG', $document->getInitiatingPartyName());
        $this->assertEquals('NTFRCV-2025-001', $document->getOriginalMessageId());
        $this->assertEquals('ACCP', $document->getOriginalGroupStatusCode());

        // Check status items
        $items = $document->getItems();
        $this->assertCount(2, $items);

        $item1 = $items[0];
        $this->assertEquals('NTF-001', $item1->getOriginalItemId());
        $this->assertEquals('ACCP', $item1->getItemStatus());
        $this->assertNull($item1->getReasonCode());

        $item2 = $items[1];
        $this->assertEquals('NTF-002', $item2->getOriginalItemId());
        $this->assertEquals('RJCT', $item2->getItemStatus());
        $this->assertEquals('AC01', $item2->getReasonCode());
        $this->assertStringContainsString('Debitorenkonto', $item2->getAdditionalInformation());
    }

    public function testDetectTypeCamt059(): void {
        $file = $this->samplesPath . 'sample_camt059.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT059, $type);
        $this->assertTrue($type->isNotificationType());
    }

    public function testCamt059ToXml(): void {
        $file = $this->samplesPath . 'sample_camt059.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt059Document $document */
        $document = CamtParser::parseFile($file);
        $xml = $document->toXml();

        $this->assertStringContainsString('NtfctnToRcvStsRpt', $xml);
        $this->assertStringContainsString('STSRPT-2025-001', $xml);
        $this->assertStringContainsString('ACCP', $xml);
        $this->assertStringContainsString('RJCT', $xml);
        $this->assertStringContainsString('AC01', $xml);
        $this->assertStringContainsString('camt.059', $xml);
    }

    // ============================================================
    // CAMT.034 Tests
    // ============================================================

    public function testParseCamt034Duplicate(): void {
        $file = $this->samplesPath . 'sample_camt034.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt034Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type34\Document::class, $document);
        $this->assertEquals(CamtType::CAMT034, $document->getCamtType());
        $this->assertEquals('DPL-2025-001', $document->getAssignmentId());
        $this->assertEquals('COBADEFFXXX', $document->getAssignerAgentBic());
        $this->assertEquals('DEUTDEFFXXX', $document->getAssigneeAgentBic());
        $this->assertEquals('CASE-2025-12345', $document->getCaseId());
        $this->assertEquals('Commerzbank AG', $document->getCaseCreator());
        $this->assertEquals('SWIFT_MT103', $document->getDuplicateContentType());
        $this->assertStringContainsString('TXN-2025-001', $document->getDuplicateContent());
    }

    public function testDetectTypeCamt034(): void {
        $file = $this->samplesPath . 'sample_camt034.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT034, $type);
        $this->assertTrue($type->isInvestigationType());
    }

    public function testCamt034ToXml(): void {
        $file = $this->samplesPath . 'sample_camt034.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt034Document $document */
        $document = CamtParser::parseFile($file);
        $xml = $document->toXml();

        $this->assertStringContainsString('Dplct', $xml);
        $this->assertStringContainsString('DPL-2025-001', $xml);
        $this->assertStringContainsString('COBADEFFXXX', $xml);
        $this->assertStringContainsString('CASE-2025-12345', $xml);
        $this->assertStringContainsString('camt.034', $xml);
    }

    // ============================================================
    // CAMT.035 Tests
    // ============================================================

    public function testParseCamt035ProprietaryFormatInvestigation(): void {
        $file = $this->samplesPath . 'sample_camt035.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt035Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type35\Document::class, $document);
        $this->assertEquals(CamtType::CAMT035, $document->getCamtType());
        $this->assertEquals('PFI-2025-001', $document->getAssignmentId());
        $this->assertEquals('COBADEFFXXX', $document->getAssignerAgentBic());
        $this->assertEquals('DEUTDEFFXXX', $document->getAssigneeAgentBic());
        $this->assertEquals('PRTRY-CASE-2025-001', $document->getCaseId());
        $this->assertEquals('INTERNAL_INVESTIGATION', $document->getProprietaryType());
        $this->assertStringContainsString('duplicate_payment_check', $document->getProprietaryData());
    }

    public function testDetectTypeCamt035(): void {
        $file = $this->samplesPath . 'sample_camt035.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT035, $type);
        $this->assertTrue($type->isInvestigationType());
    }

    public function testCamt035ToXml(): void {
        $file = $this->samplesPath . 'sample_camt035.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt035Document $document */
        $document = CamtParser::parseFile($file);
        $xml = $document->toXml();

        $this->assertStringContainsString('PrtryFrmtInvstgtn', $xml);
        $this->assertStringContainsString('PFI-2025-001', $xml);
        $this->assertStringContainsString('INTERNAL_INVESTIGATION', $xml);
        $this->assertStringContainsString('camt.035', $xml);
    }

    // ============================================================
    // CAMT.036 Tests
    // ============================================================

    public function testParseCamt036DebitAuthorisationResponse(): void {
        $file = $this->samplesPath . 'sample_camt036.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt036Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type36\Document::class, $document);
        $this->assertEquals(CamtType::CAMT036, $document->getCamtType());
        $this->assertEquals('DAR-2025-001', $document->getAssignmentId());
        $this->assertEquals('DEUTDEFFXXX', $document->getAssignerAgentBic());
        $this->assertEquals('COBADEFFXXX', $document->getAssigneeAgentBic());
        $this->assertEquals('DA-CASE-2025-001', $document->getCaseId());
        $this->assertTrue($document->isDebitAuthorised());
        $this->assertEquals('2500.00', $document->getAuthorisedAmount());
        $this->assertEquals('EUR', $document->getAuthorisedCurrency()->value);
        $this->assertEquals('2025-01-20', $document->getValueDate()->format('Y-m-d'));
        $this->assertStringContainsString('genehmigt', $document->getReason());
    }

    public function testDetectTypeCamt036(): void {
        $file = $this->samplesPath . 'sample_camt036.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT036, $type);
        $this->assertTrue($type->isInvestigationType());
    }

    public function testCamt036ToXml(): void {
        $file = $this->samplesPath . 'sample_camt036.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt036Document $document */
        $document = CamtParser::parseFile($file);
        $xml = $document->toXml();

        $this->assertStringContainsString('DbtAuthstnRspn', $xml);
        $this->assertStringContainsString('DAR-2025-001', $xml);
        $this->assertStringContainsString('true', $xml);
        $this->assertStringContainsString('2500.00', $xml);
        $this->assertStringContainsString('camt.036', $xml);
    }

    // ============================================================
    // CAMT.037 Tests
    // ============================================================

    public function testParseCamt037DebitAuthorisationRequest(): void {
        $file = $this->samplesPath . 'sample_camt037.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt037Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type37\Document::class, $document);
        $this->assertEquals(CamtType::CAMT037, $document->getCamtType());
        $this->assertEquals('DAREQ-2025-001', $document->getAssignmentId());
        $this->assertEquals('COBADEFFXXX', $document->getAssignerAgentBic());
        $this->assertEquals('DEUTDEFFXXX', $document->getAssigneeAgentBic());
        $this->assertEquals('DA-CASE-2025-001', $document->getCaseId());
        $this->assertEquals('PAIN008-2025-001', $document->getOriginalMessageId());
        $this->assertEquals('pain.008.001.02', $document->getOriginalMessageNameId());
        $this->assertEquals('E2E-2025-001', $document->getOriginalEndToEndId());
        $this->assertEquals('TXN-2025-DD-001', $document->getOriginalTransactionId());
        $this->assertEquals('2500.00', $document->getOriginalInterbankSettlementAmount());
        $this->assertEquals('EUR', $document->getOriginalCurrency()->value);
        $this->assertEquals('Mustermann GmbH', $document->getDebtorName());
        $this->assertEquals('DE89370400440532013000', $document->getDebtorAccountIban());
    }

    public function testDetectTypeCamt037(): void {
        $file = $this->samplesPath . 'sample_camt037.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT037, $type);
        $this->assertTrue($type->isInvestigationType());
    }

    public function testCamt037ToXml(): void {
        $file = $this->samplesPath . 'sample_camt037.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt037Document $document */
        $document = CamtParser::parseFile($file);
        $xml = $document->toXml();

        $this->assertStringContainsString('DbtAuthstnReq', $xml);
        $this->assertStringContainsString('DAREQ-2025-001', $xml);
        $this->assertStringContainsString('PAIN008-2025-001', $xml);
        $this->assertStringContainsString('Mustermann GmbH', $xml);
        $this->assertStringContainsString('DE89370400440532013000', $xml);
        $this->assertStringContainsString('camt.037', $xml);
    }

    // ============================================================
    // CAMT.038 Tests
    // ============================================================

    public function testParseCamt038CaseStatusReportRequest(): void {
        $file = $this->samplesPath . 'sample_camt038.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt038Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type38\Document::class, $document);
        $this->assertEquals(CamtType::CAMT038, $document->getCamtType());
        $this->assertEquals('CSRR-2025-001', $document->getRequestId());
        $this->assertEquals('COBADEFFXXX', $document->getRequesterAgentBic());
        $this->assertEquals('DEUTDEFFXXX', $document->getResponderAgentBic());
        $this->assertEquals('CASE-2025-PENDING-001', $document->getCaseId());
        $this->assertEquals('Commerzbank AG', $document->getCaseCreator());
    }

    public function testDetectTypeCamt038(): void {
        $file = $this->samplesPath . 'sample_camt038.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT038, $type);
        $this->assertTrue($type->isInvestigationType());
    }

    public function testCamt038ToXml(): void {
        $file = $this->samplesPath . 'sample_camt038.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt038Document $document */
        $document = CamtParser::parseFile($file);
        $xml = $document->toXml();

        $this->assertStringContainsString('CaseStsRptReq', $xml);
        $this->assertStringContainsString('CSRR-2025-001', $xml);
        $this->assertStringContainsString('COBADEFFXXX', $xml);
        $this->assertStringContainsString('CASE-2025-PENDING-001', $xml);
        $this->assertStringContainsString('camt.038', $xml);
    }

    // ============================================================
    // CAMT.039 Tests
    // ============================================================

    public function testParseCamt039CaseStatusReport(): void {
        $file = $this->samplesPath . 'sample_camt039.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt039Document $document */
        $document = CamtParser::parseFile($file);

        $this->assertInstanceOf(\CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type39\Document::class, $document);
        $this->assertEquals(CamtType::CAMT039, $document->getCamtType());
        $this->assertEquals('CSR-2025-001', $document->getReportId());
        $this->assertEquals('DEUTDEFFXXX', $document->getReporterAgentBic());
        $this->assertEquals('COBADEFFXXX', $document->getReceiverAgentBic());
        $this->assertEquals('CASE-2025-PENDING-001', $document->getCaseId());
        $this->assertEquals('PDNG', $document->getStatusCode());
        $this->assertEquals('UNDER_INVESTIGATION', $document->getStatusReason());
        $this->assertStringContainsString('Compliance', $document->getAdditionalInformation());
    }

    public function testDetectTypeCamt039(): void {
        $file = $this->samplesPath . 'sample_camt039.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        $content = file_get_contents($file);
        $type = CamtParser::detectType($content);

        $this->assertEquals(CamtType::CAMT039, $type);
        $this->assertTrue($type->isInvestigationType());
    }

    public function testCamt039ToXml(): void {
        $file = $this->samplesPath . 'sample_camt039.xml';
        if (!file_exists($file)) {
            $this->markTestSkipped('Sample file not found');
        }

        /** @var Camt039Document $document */
        $document = CamtParser::parseFile($file);
        $xml = $document->toXml();

        $this->assertStringContainsString('CaseStsRpt', $xml);
        $this->assertStringContainsString('CSR-2025-001', $xml);
        $this->assertStringContainsString('PDNG', $xml);
        $this->assertStringContainsString('UNDER_INVESTIGATION', $xml);
        $this->assertStringContainsString('camt.039', $xml);
    }
}
