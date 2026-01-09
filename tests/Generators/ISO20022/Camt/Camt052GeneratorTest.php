<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt052GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type52\Document;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt052Generator;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class Camt052GeneratorTest extends BaseTestCase {
    private Camt052Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Camt052Generator();
    }

    private function createDocument(): Document {
        return new Document(
            id: 'RPT-001',
            creationDateTime: new DateTimeImmutable('2025-01-15T10:30:00'),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );
    }

    public function testGetCamtType(): void {
        $this->assertSame(CamtType::CAMT052, $this->generator->getCamtType());
    }

    public function testGenerateBasicDocument(): void {
        $document = $this->createDocument();

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('BkToCstmrAcctRpt', $xml);
    }

    public function testGenerateContainsDocumentId(): void {
        $document = $this->createDocument();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('RPT-001', $xml);
    }

    public function testGenerateWithVersion02(): void {
        $document = $this->createDocument();

        $xml = $this->generator->generate($document, CamtVersion::V02);

        $this->assertStringContainsString('camt.052.001.02', $xml);
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
