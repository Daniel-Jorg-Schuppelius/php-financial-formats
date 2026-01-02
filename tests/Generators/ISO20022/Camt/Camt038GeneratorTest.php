<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt038GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt038DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt038Generator;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt038GeneratorTest extends BaseTestCase {
    private Camt038Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Camt038Generator();
    }

    #[Test]
    public function testGetCamtType(): void {
        $this->assertSame(CamtType::CAMT038, $this->generator->getCamtType());
    }

    #[Test]
    public function testGenerateBasicDocument(): void {
        $document = Camt038DocumentBuilder::create('REQUEST-001')
            ->withRequesterAgent('DEUTDEFFXXX')
            ->withResponderAgent('COBADEFFXXX')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('CaseStsRptReq', $xml);
    }

    #[Test]
    public function testGenerateContainsRequestHeader(): void {
        $document = Camt038DocumentBuilder::create('REQUEST-002')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<ReqHdr>', $xml);
        $this->assertStringContainsString('REQUEST-002', $xml);
    }

    #[Test]
    public function testGenerateWithRequesterAgent(): void {
        $document = Camt038DocumentBuilder::create('REQUEST-003')
            ->withRequesterAgent('DEUTDEFFXXX')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Reqstr>', $xml);
        $this->assertStringContainsString('DEUTDEFFXXX', $xml);
    }

    #[Test]
    public function testGenerateWithResponderAgent(): void {
        $document = Camt038DocumentBuilder::create('REQUEST-004')
            ->withResponderAgent('COBADEFFXXX')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Rspndr>', $xml);
        $this->assertStringContainsString('COBADEFFXXX', $xml);
    }

    #[Test]
    public function testGenerateWithCase(): void {
        $document = Camt038DocumentBuilder::create('REQUEST-005')
            ->forCase('CASE-001', 'Bank AG')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Case>', $xml);
        $this->assertStringContainsString('CASE-001', $xml);
    }

    #[Test]
    public function testGenerateWithVersion06(): void {
        $document = Camt038DocumentBuilder::create('REQUEST-006')
            ->build();

        $xml = $this->generator->generate($document, CamtVersion::V06);

        $this->assertStringContainsString('camt.038.001.06', $xml);
    }
}
