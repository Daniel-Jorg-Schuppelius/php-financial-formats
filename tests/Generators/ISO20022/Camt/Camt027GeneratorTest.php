<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt027GeneratorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Builders\ISO20022\Camt\Camt027DocumentBuilder;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt027Generator;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;
use CommonToolkit\Enums\CurrencyCode;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class Camt027GeneratorTest extends BaseTestCase {
    private Camt027Generator $generator;

    protected function setUp(): void {
        parent::setUp();
        $this->generator = new Camt027Generator();
    }

    #[Test]
    public function testGetCamtType(): void {
        $this->assertSame(CamtType::CAMT027, $this->generator->getCamtType());
    }

    #[Test]
    public function testGenerateBasicDocument(): void {
        $document = Camt027DocumentBuilder::create('ASSIGN-001')
            ->withAssignerAgent('DEUTDEFFXXX')
            ->withAssigneeAgent('COBADEFFXXX')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertNotEmpty($xml);
        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('ClmNonRct', $xml);
    }

    #[Test]
    public function testGenerateWithMissingCoverIndicator(): void {
        $document = Camt027DocumentBuilder::create('ASSIGN-002')
            ->withMissingCoverIndicator(true)
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('<MssngCoverInd>true</MssngCoverInd>', $xml);
    }

    #[Test]
    public function testGenerateWithDebtor(): void {
        $document = Camt027DocumentBuilder::create('ASSIGN-003')
            ->withOriginalTransaction('MSG-001', 'E2E-001')
            ->withDebtorName('Max Mustermann')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('Max Mustermann', $xml);
    }

    #[Test]
    public function testGenerateWithCreditor(): void {
        $document = Camt027DocumentBuilder::create('ASSIGN-004')
            ->withOriginalTransaction('MSG-001', 'E2E-001')
            ->withCreditorName('Firma GmbH')
            ->build();

        $xml = $this->generator->generate($document);

        $this->assertStringContainsString('Firma GmbH', $xml);
    }

    #[Test]
    public function testGenerateWithVersion06(): void {
        $document = Camt027DocumentBuilder::create('ASSIGN-005')
            ->build();

        $xml = $this->generator->generate($document, CamtVersion::V06);

        $this->assertStringContainsString('camt.027.001.06', $xml);
    }
}
