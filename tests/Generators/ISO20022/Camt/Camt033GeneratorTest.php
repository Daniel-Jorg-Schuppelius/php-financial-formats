<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt033GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt033DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt033Generator;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use CommonToolkit\Enums\CurrencyCode;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt033GeneratorTest extends BaseTestCase {
    private Camt033Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Camt033Generator();
    }

    #[Test]
    public function testGetCamtType(): void {
        $this->assertSame(CamtType::CAMT033, $this->generator->getCamtType());
    }

    #[Test]
    public function testGenerateBasicDocument(): void {
        $document = Camt033DocumentBuilder::create('ASSIGN-001')
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('ReqForDplct', $xml);
    }

    #[Test]
    public function testGenerateWithUnderlying(): void {
        $document = Camt033DocumentBuilder::create('ASSIGN-002')
            ->withOriginalTransaction('TXN-001', 'E2E-001')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Undrlyg>', $xml);
        $this->assertStringContainsString('TXN-001', $xml);
        $this->assertStringContainsString('E2E-001', $xml);
    }

    #[Test]
    public function testGenerateWithAmount(): void {
        $document = Camt033DocumentBuilder::create('ASSIGN-003')
            ->withOriginalTransaction('TXN-001')
            ->withOriginalAmount(7500.00, CurrencyCode::Euro)
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('7500.00', $xml);
        $this->assertStringContainsString('EUR', $xml);
    }

    #[Test]
    public function testGenerateWithCase(): void {
        $document = Camt033DocumentBuilder::create('ASSIGN-004')
            ->forCase('CASE-001', 'Bank AG')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Case>', $xml);
        $this->assertStringContainsString('CASE-001', $xml);
    }

    #[Test]
    public function testGenerateWithVersion06(): void {
        $document = Camt033DocumentBuilder::create('ASSIGN-005')
            ->build();

        $xml = $this->generator->generate($document, CamtVersion::V06);

        $this->assertStringContainsString('camt.033.001.06', $xml);
    }
}
