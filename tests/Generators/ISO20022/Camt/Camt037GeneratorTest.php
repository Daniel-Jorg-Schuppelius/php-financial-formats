<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt037GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt037DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt037Generator;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt037GeneratorTest extends BaseTestCase {
    private Camt037Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Camt037Generator();
    }

    #[Test]
    public function testGetCamtType(): void {
        $this->assertSame(CamtType::CAMT037, $this->generator->getCamtType());
    }

    #[Test]
    public function testGenerateBasicDocument(): void {
        $document = Camt037DocumentBuilder::create('ASSIGN-001')
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('DbtAuthstnReq', $xml);
    }

    #[Test]
    public function testGenerateWithUnderlying(): void {
        $document = Camt037DocumentBuilder::create('ASSIGN-002')
            ->withUnderlyingTransaction('TXN-001', 'E2E-001')
            ->withInstructedAmount(3000.00, CurrencyCode::Euro)
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Undrlyg>', $xml);
        $this->assertStringContainsString('TXN-001', $xml);
        $this->assertStringContainsString('3000.00', $xml);
    }

    #[Test]
    public function testGenerateWithDebtor(): void {
        $document = Camt037DocumentBuilder::create('ASSIGN-003')
            ->withDebtor('Max Mustermann', 'DE89370400440532013000')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('Max Mustermann', $xml);
        $this->assertStringContainsString('DE89370400440532013000', $xml);
    }

    #[Test]
    public function testGenerateWithVersion06(): void {
        $document = Camt037DocumentBuilder::create('ASSIGN-005')
            ->build();

        $xml = $this->generator->generate($document, CamtVersion::V06);

        $this->assertStringContainsString('camt.037.001.06', $xml);
    }
}
