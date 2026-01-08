<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt053GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Balance;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53\Document;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt053Generator;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtVersion;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class Camt053GeneratorTest extends BaseTestCase {
    private Camt053Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Camt053Generator();
    }

    private function createDocument(): Document {
        return new Document(
            id: 'STMT-001',
            creationDateTime: new DateTimeImmutable('2025-01-15T10:30:00'),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );
    }

    public function testGetCamtType(): void {
        $this->assertSame(CamtType::CAMT053, $this->generator->getCamtType());
    }

    public function testGenerateBasicDocument(): void {
        $document = $this->createDocument();

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('BkToCstmrStmt', $xml);
    }

    public function testGenerateContainsDocumentId(): void {
        $document = $this->createDocument();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('STMT-001', $xml);
    }

    public function testGenerateContainsAccountIdentifier(): void {
        $document = $this->createDocument();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('DE89370400440532013000', $xml);
    }

    public function testGenerateWithVersion02(): void {
        $document = $this->createDocument();

        $xml = $this->generator->generate($document, CamtVersion::V02);

        $this->assertStringContainsString('camt.053.001.02', $xml);
    }

    public function testGenerateWithVersion08(): void {
        $document = $this->createDocument();

        $xml = $this->generator->generate($document, CamtVersion::V08);

        $this->assertStringContainsString('camt.053.001.08', $xml);
    }

    public function testGenerateWithOpeningBalance(): void {
        $balance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2025-01-14'),
            currency: CurrencyCode::Euro,
            amount: 10000.00,
            type: 'OPBD'
        );

        $document = $this->createDocument()->withOpeningBalance($balance);

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Bal>', $xml);
        $this->assertStringContainsString('OPBD', $xml);
    }

    public function testGenerateWithClosingBalance(): void {
        $balance = new Balance(
            creditDebit: CreditDebit::CREDIT,
            date: new DateTimeImmutable('2025-01-15'),
            currency: CurrencyCode::Euro,
            amount: 10500.00,
            type: 'CLBD'
        );

        $document = $this->createDocument()->withClosingBalance($balance);

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Bal>', $xml);
        $this->assertStringContainsString('CLBD', $xml);
    }

    public function testGenerateIsValidXml(): void {
        $document = $this->createDocument();

        $xml = $this->generator->generate($document);

        $dom = new \DOMDocument();
        $result = @$dom->loadXML($xml);

        $this->assertTrue($result, 'Generated XML should be valid');
    }

    public function testGenerateThrowsExceptionForWrongDocumentType(): void {
        $this->expectException(\InvalidArgumentException::class);

        $wrongDocument = $this->createMock(\CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtDocumentAbstract::class);

        $this->generator->generate($wrongDocument);
    }
}
