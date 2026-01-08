<?php
/*
 * Created on   : Thu Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PainParserTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Parsers;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\PainDocumentInterface;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type1\Document as Pain001Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\Document as Pain002Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\Document as Pain007Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\Document as Pain008Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type9\Document as Pain009Document;
use CommonToolkit\FinancialFormats\Enums\Pain\PainType;
use CommonToolkit\FinancialFormats\Parsers\PainParser;
use RuntimeException;
use Tests\Contracts\BaseTestCase;

/**
 * Tests für den generischen PainParser.
 */
class PainParserTest extends BaseTestCase {
    // ============================================================
    // PAIN.001 - Customer Credit Transfer Initiation Tests
    // ============================================================

    public function testParsePain001(): void {
        $xml = $this->getSamplePain001Xml();

        $document = PainParser::parse($xml);

        $this->assertInstanceOf(PainDocumentInterface::class, $document);
        $this->assertInstanceOf(Pain001Document::class, $document);
        $this->assertEquals(PainType::PAIN_001, $document->getType());
    }

    public function testParsePain001Directly(): void {
        $xml = $this->getSamplePain001Xml();

        $document = PainParser::parsePain001($xml);

        $this->assertInstanceOf(Pain001Document::class, $document);
        $this->assertEquals(PainType::PAIN_001, $document->getType());

        $header = $document->getGroupHeader();
        $this->assertEquals('MSG-001', $header->getMessageId());
        $this->assertEquals(1, $header->getNumberOfTransactions());
        $this->assertEquals('Test GmbH', $header->getInitiatingParty()->getName());
    }

    public function testParsePain001WithTransactions(): void {
        $xml = $this->getSamplePain001Xml();

        $document = PainParser::parsePain001($xml);

        $instructions = $document->getPaymentInstructions();
        $this->assertCount(1, $instructions);

        $instruction = $instructions[0];
        $this->assertEquals('PMT-001', $instruction->getPaymentInstructionId());

        $transactions = $instruction->getTransactions();
        $this->assertCount(1, $transactions);

        $tx = $transactions[0];
        $this->assertEquals(1000.0, $tx->getAmount());
        $this->assertEquals('EUR', $tx->getCurrency()->value);
    }

    // ============================================================
    // PAIN.002 - Customer Payment Status Report Tests
    // ============================================================

    public function testParsePain002(): void {
        $xml = $this->getSamplePain002Xml();

        $document = PainParser::parse($xml);

        $this->assertInstanceOf(PainDocumentInterface::class, $document);
        $this->assertInstanceOf(Pain002Document::class, $document);
        $this->assertEquals(PainType::PAIN_002, $document->getType());
    }

    public function testParsePain002Directly(): void {
        $xml = $this->getSamplePain002Xml();

        $document = PainParser::parsePain002($xml);

        $this->assertInstanceOf(Pain002Document::class, $document);
        $header = $document->getGroupHeader();
        $this->assertEquals('STATUS-001', $header->getMessageId());
    }

    // ============================================================
    // PAIN.007 - Customer Payment Reversal Tests
    // ============================================================

    public function testParsePain007(): void {
        $xml = $this->getSamplePain007Xml();

        $document = PainParser::parse($xml);

        $this->assertInstanceOf(PainDocumentInterface::class, $document);
        $this->assertInstanceOf(Pain007Document::class, $document);
        $this->assertEquals(PainType::PAIN_007, $document->getType());
    }

    public function testParsePain007Directly(): void {
        $xml = $this->getSamplePain007Xml();

        $document = PainParser::parsePain007($xml);

        $this->assertInstanceOf(Pain007Document::class, $document);
        $header = $document->getGroupHeader();
        $this->assertEquals('RVSL-001', $header->getMessageId());
    }

    // ============================================================
    // PAIN.008 - Customer Direct Debit Initiation Tests
    // ============================================================

    public function testParsePain008(): void {
        $xml = $this->getSamplePain008Xml();

        $document = PainParser::parse($xml);

        $this->assertInstanceOf(PainDocumentInterface::class, $document);
        $this->assertInstanceOf(Pain008Document::class, $document);
        $this->assertEquals(PainType::PAIN_008, $document->getType());
    }

    public function testParsePain008Directly(): void {
        $xml = $this->getSamplePain008Xml();

        $document = PainParser::parsePain008($xml);

        $this->assertInstanceOf(Pain008Document::class, $document);
        $header = $document->getGroupHeader();
        $this->assertEquals('DD-001', $header->getMessageId());
    }

    // ============================================================
    // PAIN.009 - Mandate Initiation Request Tests
    // ============================================================

    public function testParsePain009(): void {
        $xml = $this->getSamplePain009Xml();

        $document = PainParser::parse($xml);

        $this->assertInstanceOf(PainDocumentInterface::class, $document);
        $this->assertInstanceOf(Pain009Document::class, $document);
        $this->assertEquals(PainType::PAIN_009, $document->getType());
    }

    public function testParsePain009Directly(): void {
        $xml = $this->getSamplePain009Xml();

        $document = PainParser::parsePain009($xml);

        $this->assertInstanceOf(Pain009Document::class, $document);
        $this->assertEquals('MNDT-001', $document->getMessageId());
    }

    // ============================================================
    // Type Detection & Validation Tests
    // ============================================================

    public function testDetectTypePain001(): void {
        $xml = $this->getSamplePain001Xml();

        $type = PainParser::detectType($xml);

        $this->assertEquals(PainType::PAIN_001, $type);
    }

    public function testDetectTypePain002(): void {
        $xml = $this->getSamplePain002Xml();

        $type = PainParser::detectType($xml);

        $this->assertEquals(PainType::PAIN_002, $type);
    }

    public function testDetectTypePain008(): void {
        $xml = $this->getSamplePain008Xml();

        $type = PainParser::detectType($xml);

        $this->assertEquals(PainType::PAIN_008, $type);
    }

    public function testIsValidWithValidPain001(): void {
        $xml = $this->getSamplePain001Xml();

        $this->assertTrue(PainParser::isValid($xml));
        $this->assertTrue(PainParser::isValid($xml, PainType::PAIN_001));
    }

    public function testIsValidWithWrongExpectedType(): void {
        $xml = $this->getSampleUnknownDocumentXml();

        $this->assertFalse(PainParser::isValid($xml));
    }

    public function testParseThrowsOnUnknownType(): void {
        $xml = $this->getSampleUnknownDocumentXml();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown PAIN document type');

        PainParser::parse($xml);
    }

    // ============================================================
    // PainDocumentInterface Tests
    // ============================================================

    public function testAllDocumentsImplementInterface(): void {
        $testCases = [
            ['xml' => $this->getSamplePain001Xml(), 'expected' => Pain001Document::class],
            ['xml' => $this->getSamplePain002Xml(), 'expected' => Pain002Document::class],
            ['xml' => $this->getSamplePain007Xml(), 'expected' => Pain007Document::class],
            ['xml' => $this->getSamplePain008Xml(), 'expected' => Pain008Document::class],
            ['xml' => $this->getSamplePain009Xml(), 'expected' => Pain009Document::class],
        ];

        foreach ($testCases as $case) {
            $document = PainParser::parse($case['xml']);

            $this->assertInstanceOf(PainDocumentInterface::class, $document);
            $this->assertInstanceOf($case['expected'], $document);
        }
    }

    // ============================================================
    // Sample XML Helpers (loaded from .samples)
    // ============================================================

    private function getSamplePain001Xml(): string {
        return $this->getSampleXml('pain.001.001.12.xml');
    }

    private function getSamplePain002Xml(): string {
        return $this->getSampleXml('pain.002.001.12.xml');
    }

    private function getSamplePain007Xml(): string {
        return $this->getSampleXml('pain.007.001.11.xml');
    }

    private function getSamplePain008Xml(): string {
        return $this->getSampleXml('pain.008.001.11.xml');
    }

    private function getSamplePain009Xml(): string {
        return $this->getSampleXml('pain.009.001.07.xml');
    }

    private function getSampleUnknownDocumentXml(): string {
        return $this->getSampleXml('unknown-document.xml');
    }

    private function getSampleXml(string $fileName): string {
        $path = dirname(__DIR__, 2) . '/.samples/Banking/PAIN/' . $fileName;
        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException("Sample XML could not be read: {$path}");
        }
        return $content;
    }
}
