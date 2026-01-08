<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt034GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt034DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt034Generator;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtVersion;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt034GeneratorTest extends BaseTestCase {
    private Camt034Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Camt034Generator();
    }

    #[Test]
    public function testGetCamtType(): void {
        $this->assertSame(CamtType::CAMT034, $this->generator->getCamtType());
    }

    #[Test]
    public function testGenerateBasicDocument(): void {
        $document = Camt034DocumentBuilder::create('ASSIGN-001')
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('Dplct', $xml);
    }

    #[Test]
    public function testGenerateWithDuplicateContent(): void {
        $content = base64_encode('Testinhalt');

        $document = Camt034DocumentBuilder::create('ASSIGN-002')
            ->withDuplicateContent($content)
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Dplct>', $xml);
        $this->assertStringContainsString($content, $xml);
    }

    #[Test]
    public function testGenerateWithCase(): void {
        $document = Camt034DocumentBuilder::create('ASSIGN-003')
            ->forCase('CASE-001', 'Bank AG')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Case>', $xml);
        $this->assertStringContainsString('CASE-001', $xml);
    }

    #[Test]
    public function testGenerateWithVersion06(): void {
        $document = Camt034DocumentBuilder::create('ASSIGN-004')
            ->build();

        $xml = $this->generator->generate($document, CamtVersion::V06);

        $this->assertStringContainsString('camt.034.001.06', $xml);
    }
}
