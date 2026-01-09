<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt036GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt036DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt036Generator;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt036GeneratorTest extends BaseTestCase {
    private Camt036Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Camt036Generator();
    }

    #[Test]
    public function testGetCamtType(): void {
        $this->assertSame(CamtType::CAMT036, $this->generator->getCamtType());
    }

    #[Test]
    public function testGenerateBasicDocument(): void {
        $document = Camt036DocumentBuilder::create('ASSIGN-001')
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('DbtAuthstnRspn', $xml);
    }

    #[Test]
    public function testGenerateWithAuthorisation(): void {
        $document = Camt036DocumentBuilder::create('ASSIGN-002')
            ->authorised(true)
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<Conf>', $xml);
        $this->assertStringContainsString('<DbtAuthstn>true</DbtAuthstn>', $xml);
    }

    #[Test]
    public function testGenerateWithRejection(): void {
        $document = Camt036DocumentBuilder::create('ASSIGN-003')
            ->authorised(false)
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<DbtAuthstn>false</DbtAuthstn>', $xml);
    }

    #[Test]
    public function testGenerateWithAuthorisedAmount(): void {
        $document = Camt036DocumentBuilder::create('ASSIGN-004')
            ->authorised(true)
            ->withAuthorisedAmount(2500.00, CurrencyCode::Euro, new DateTimeImmutable('2025-01-20'))
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('2500.00', $xml);
        $this->assertStringContainsString('EUR', $xml);
    }

    #[Test]
    public function testGenerateWithReason(): void {
        $document = Camt036DocumentBuilder::create('ASSIGN-005')
            ->authorised(false)
            ->withReason('Nicht berechtigt')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('Nicht berechtigt', $xml);
    }

    #[Test]
    public function testGenerateWithVersion06(): void {
        $document = Camt036DocumentBuilder::create('ASSIGN-006')
            ->build();

        $xml = $this->generator->generate($document, CamtVersion::V06);

        $this->assertStringContainsString('camt.036.001.06', $xml);
    }
}
