<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt087GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt087DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt087Generator;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtVersion;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt087GeneratorTest extends BaseTestCase {
    private Camt087Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Camt087Generator();
    }

    #[Test]
    public function testGetCamtType(): void {
        $this->assertSame(CamtType::CAMT087, $this->generator->getCamtType());
    }

    #[Test]
    public function testGenerateBasicDocument(): void {
        $document = Camt087DocumentBuilder::create('ASSIGN-001')
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('ReqToModfyPmt', $xml);
    }

    #[Test]
    public function testGenerateContainsAssignment(): void {
        $document = Camt087DocumentBuilder::create('ASSIGN-002')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Assgnmt>', $xml);
        $this->assertStringContainsString('ASSIGN-002', $xml);
    }

    #[Test]
    public function testGenerateWithCase(): void {
        $document = Camt087DocumentBuilder::create('ASSIGN-003')
            ->forCase('CASE-001', 'Bank AG')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Case>', $xml);
        $this->assertStringContainsString('CASE-001', $xml);
    }

    #[Test]
    public function testGenerateWithUnderlyingTransaction(): void {
        $document = Camt087DocumentBuilder::create('ASSIGN-004')
            ->withOriginalTransaction('TXN-001', 'E2E-001')
            ->withOriginalAmount(5000.00, CurrencyCode::Euro)
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Undrlyg>', $xml);
        $this->assertStringContainsString('TXN-001', $xml);
        $this->assertStringContainsString('E2E-001', $xml);
        $this->assertStringContainsString('5000.00', $xml);
    }

    #[Test]
    public function testGenerateWithOriginalMessage(): void {
        $document = Camt087DocumentBuilder::create('ASSIGN-005')
            ->withOriginalMessage('MSG-001', 'pain.001.001.03')
            ->withOriginalTransaction('TXN-001')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<OrgnlGrpInf>', $xml);
        $this->assertStringContainsString('MSG-001', $xml);
        $this->assertStringContainsString('pain.001.001.03', $xml);
    }

    #[Test]
    public function testGenerateWithAmountModification(): void {
        $document = Camt087DocumentBuilder::create('ASSIGN-006')
            ->withOriginalTransaction('TXN-001')
            ->requestAmountChange(4500.00, CurrencyCode::Euro)
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Mod>', $xml);
        $this->assertStringContainsString('<PmtModDtls>', $xml);
        $this->assertStringContainsString('4500.00', $xml);
    }

    #[Test]
    public function testGenerateWithCreditorModification(): void {
        $document = Camt087DocumentBuilder::create('ASSIGN-007')
            ->withOriginalTransaction('TXN-001')
            ->requestCreditorChange('Neue Firma GmbH', 'DE91100000000123456789')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<CdtrDtls>', $xml);
        $this->assertStringContainsString('Neue Firma GmbH', $xml);
        $this->assertStringContainsString('DE91100000000123456789', $xml);
    }

    #[Test]
    public function testGenerateWithRemittanceModification(): void {
        $document = Camt087DocumentBuilder::create('ASSIGN-008')
            ->withOriginalTransaction('TXN-001')
            ->requestRemittanceChange('Korrigierter Verwendungszweck')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<RmtInf>', $xml);
        $this->assertStringContainsString('Korrigierter Verwendungszweck', $xml);
    }

    #[Test]
    public function testGenerateWithVersion09(): void {
        $document = Camt087DocumentBuilder::create('ASSIGN-009')
            ->build();

        $xml = $this->generator->generate($document, CamtVersion::V09);

        $this->assertStringContainsString('camt.087.001.09', $xml);
    }
}
