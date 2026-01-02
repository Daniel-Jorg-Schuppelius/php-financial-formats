<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt054GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type54\Document;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt054Generator;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class Camt054GeneratorTest extends BaseTestCase {
    private Camt054Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Camt054Generator();
    }

    private function createDocument(): Document {
        return new Document(
            id: 'NTFCTN-001',
            creationDateTime: new DateTimeImmutable('2025-01-15T10:30:00'),
            accountIdentifier: 'DE89370400440532013000',
            currency: CurrencyCode::Euro
        );
    }

    public function testGetCamtType(): void {
        $this->assertSame(CamtType::CAMT054, $this->generator->getCamtType());
    }

    public function testGenerateBasicDocument(): void {
        $document = $this->createDocument();

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('BkToCstmrDbtCdtNtfctn', $xml);
    }

    public function testGenerateContainsDocumentId(): void {
        $document = $this->createDocument();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('NTFCTN-001', $xml);
    }

    public function testGenerateWithVersion02(): void {
        $document = $this->createDocument();

        $xml = $this->generator->generate($document, CamtVersion::V02);

        $this->assertStringContainsString('camt.054.001.02', $xml);
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
