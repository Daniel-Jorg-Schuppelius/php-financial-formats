<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt039GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt039DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt039Generator;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtVersion;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt039GeneratorTest extends BaseTestCase {
    private Camt039Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Camt039Generator();
    }

    #[Test]
    public function testGetCamtType(): void {
        $this->assertSame(CamtType::CAMT039, $this->generator->getCamtType());
    }

    #[Test]
    public function testGenerateBasicDocument(): void {
        $document = Camt039DocumentBuilder::create('REPORT-001')
            ->withReporterAgent('DEUTDEFFXXX')
            ->withReceiverAgent('COBADEFFXXX')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('CaseStsRpt', $xml);
    }

    #[Test]
    public function testGenerateContainsReportHeader(): void {
        $document = Camt039DocumentBuilder::create('REPORT-002')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Hdr>', $xml);
        $this->assertStringContainsString('REPORT-002', $xml);
    }

    #[Test]
    public function testGenerateWithStatus(): void {
        $document = Camt039DocumentBuilder::create('REPORT-003')
            ->withStatus('ACCP', 'Fall akzeptiert')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Sts>', $xml);
        $this->assertStringContainsString('ACCP', $xml);
        $this->assertStringContainsString('Fall akzeptiert', $xml);
    }

    #[Test]
    public function testGenerateWithAdditionalInformation(): void {
        $document = Camt039DocumentBuilder::create('REPORT-004')
            ->withAdditionalInformation('Zusätzliche Details zum Fall')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('Zusätzliche Details zum Fall', $xml);
    }

    #[Test]
    public function testGenerateWithCase(): void {
        $document = Camt039DocumentBuilder::create('REPORT-005')
            ->forCase('CASE-001', 'Bank AG')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Case>', $xml);
        $this->assertStringContainsString('CASE-001', $xml);
    }

    #[Test]
    public function testGenerateWithVersion06(): void {
        $document = Camt039DocumentBuilder::create('REPORT-006')
            ->build();

        $xml = $this->generator->generate($document, CamtVersion::V06);

        $this->assertStringContainsString('camt.039.001.06', $xml);
    }
}
