<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt035GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt035DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt035Generator;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt035GeneratorTest extends BaseTestCase {
    private Camt035Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Camt035Generator();
    }

    #[Test]
    public function testGetCamtType(): void {
        $this->assertSame(CamtType::CAMT035, $this->generator->getCamtType());
    }

    #[Test]
    public function testGenerateBasicDocument(): void {
        $document = Camt035DocumentBuilder::create('ASSIGN-001')
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('PrtryFrmtInvstgtn', $xml);
    }

    #[Test]
    public function testGenerateWithProprietaryData(): void {
        $document = Camt035DocumentBuilder::create('ASSIGN-002')
            ->withProprietaryData('CustomDataContent', 'CUSTOM_TYPE')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<PrtryData>', $xml);
        $this->assertStringContainsString('CustomDataContent', $xml);
        $this->assertStringContainsString('CUSTOM_TYPE', $xml);
    }

    #[Test]
    public function testGenerateWithCase(): void {
        $document = Camt035DocumentBuilder::create('ASSIGN-003')
            ->forCase('CASE-001', 'Bank AG')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Case>', $xml);
        $this->assertStringContainsString('CASE-001', $xml);
    }

    #[Test]
    public function testGenerateWithVersion06(): void {
        $document = Camt035DocumentBuilder::create('ASSIGN-004')
            ->build();

        $xml = $this->generator->generate($document, CamtVersion::V06);

        $this->assertStringContainsString('camt.035.001.06', $xml);
    }
}
