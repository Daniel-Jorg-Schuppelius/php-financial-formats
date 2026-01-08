<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt026GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt026DocumentBuilder;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type26\UnableToApplyReason;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt026Generator;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtVersion;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt026GeneratorTest extends BaseTestCase {
    private Camt026Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Camt026Generator();
    }

    #[Test]
    public function testGetCamtType(): void {
        $this->assertSame(CamtType::CAMT026, $this->generator->getCamtType());
    }

    #[Test]
    public function testGenerateBasicDocument(): void {
        $document = Camt026DocumentBuilder::create('ASSIGN-001')
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('UblToApply', $xml);
    }

    #[Test]
    public function testGenerateContainsAssignmentId(): void {
        $document = Camt026DocumentBuilder::create('ASSIGN-002')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('ASSIGN-002', $xml);
    }

    #[Test]
    public function testGenerateContainsAgentBic(): void {
        $document = Camt026DocumentBuilder::create('ASSIGN-003')
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('DEUTDEFFXXX', $xml);
        $this->assertStringContainsString('COBADEFFXXX', $xml);
    }

    #[Test]
    public function testGenerateWithCase(): void {
        $document = Camt026DocumentBuilder::create('ASSIGN-004')
            ->forCase('CASE-001', 'Bank AG')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Case>', $xml);
        $this->assertStringContainsString('CASE-001', $xml);
        $this->assertStringContainsString('Bank AG', $xml);
    }

    #[Test]
    public function testGenerateWithOriginalTransaction(): void {
        $document = Camt026DocumentBuilder::create('ASSIGN-005')
            ->withOriginalTransaction('MSG-001', 'E2E-001', 'TXN-001')
            ->withOriginalAmount(1500.00, CurrencyCode::Euro)
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('TXN-001', $xml);
        $this->assertStringContainsString('E2E-001', $xml);
        $this->assertStringContainsString('1500.00', $xml);
        $this->assertStringContainsString('EUR', $xml);
    }

    #[Test]
    public function testGenerateWithReasons(): void {
        $reason = new UnableToApplyReason(reasonCode: 'AM09', additionalInformation: 'Falscher Betrag');
        $document = Camt026DocumentBuilder::create('ASSIGN-006')
            ->addUnableToApplyReason($reason)
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Justfn>', $xml);
        $this->assertStringContainsString('AM09', $xml);
    }

    #[Test]
    public function testGenerateWithVersion06(): void {
        $document = Camt026DocumentBuilder::create('ASSIGN-007')
            ->build();

        $xml = $this->generator->generate($document, CamtVersion::V06);

        $this->assertStringContainsString('camt.026.001.06', $xml);
    }
}
